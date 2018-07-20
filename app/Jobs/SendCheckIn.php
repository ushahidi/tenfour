<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use TenFour\Mail\CheckIn as CheckInMail;
use TenFour\Contracts\Messaging\MessageServiceFactory;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Models\Organization;
use TenFour\Models\User;
use TenFour\Models\DeviceToken;
use TenFour\Messaging\SMSService;
use TenFour\Services\URLShortenerService;
use TenFour\Services\AnalyticsService;

use libphonenumber\NumberParseException;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Log;
use App;
use Exception;
use Statsd;

define('SMS_BYTECOUNT', 140);

class SendCheckIn implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $check_in;

    /**
     * Create a new job instance.
     *
     * @param array $check_in
     *
     * @return void
     */
    public function __construct(array $check_in)
    {
        $this->check_in = $check_in;
    }

    public function failed(Exception $exception)
    {
        Log::warning($exception);
        Statsd::increment('worker.sendcheckin.failed');
        app('sentry')->captureException($exception);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory, CheckInRepository $check_in_repo, ContactRepository $contact_repo, OrganizationRepository $org_repo, PersonRepository $person_repo, URLShortenerService $shortener)
    {
        $this->shortener = $shortener;

        $this->organization = $org_repo->find($this->check_in['organization_id']);
        $this->channels = $org_repo->getSetting($this->check_in['organization_id'], 'channels');

        // Get complaint count for org
        $complaint_count = $check_in_repo->getComplaintCountByOrg($this->check_in['organization_id']);

        // If complaint count is greater than threshold, log and don't send
        if ($complaint_count >= config('tenfour.messaging.complaint_threshold')) {
            Log::warning('Cannot send check-in for ' . $this->organization['name'] . ' because complaints exceed threshold');
            return;
        }

        $creator = $person_repo->find($this->check_in['organization_id'], $this->check_in['user_id']);

        $organization = Organization::findOrFail($this->check_in['organization_id']);
        $org_name = $organization->name;
        $org_url = $organization->url();
        $sender_name = User::findOrFail($this->check_in['user_id'])->name;

        // Get creator's contact
        $creator_contacts = $contact_repo->getByUserId($this->check_in['user_id']);

        // Try to get an email address
        $contact = array_first($creator_contacts, function($contact, $key) {
            return $contact['type'] === 'email';
        }, $creator_contacts[0]);

        $creator['email'] = $contact['contact'];
        $creditAdjustmentMeta = [
            'credits' => 0,
            'recipients' => 0,
            'contacts' => 0,
            'check_in_id' => $this->check_in['id'],
        ];

        $this->dispatchCheckInViaFCM($message_service_factory->make('push'), $organization, $sender_name);

        foreach($this->check_in['recipients'] as $recipient)
        {
            // Check if user has a pending reply
            $unreplied_check_in_id = $check_in_repo->getLastUnrepliedByUser($recipient['id']);

            // Set state to unresponsive if no reply found
            if ($unreplied_check_in_id) {
                $check_in_repo->updateRecipientStatus($unreplied_check_in_id, $recipient['id'], 'unresponsive');
            }

            // Update response status to 'waiting'
            $check_in_repo->updateRecipientStatus($this->check_in['id'], $recipient['id'], 'waiting');

            // Set a reply token
            $recipient['reply_token'] = $check_in_repo->setReplyToken($this->check_in['id'], $recipient['id']);

            $contacts = $contact_repo->getByUserId($recipient['id'], $this->check_in['send_via']);
            $send_via = $this->getSendVia($contacts);

            foreach($contacts as $contact)
            {
                if ($contact['blocked']) {
                    continue;
                }

                $message_service = $message_service_factory->make($contact['type']);
                $from = null;

                if ($contact['type'] === 'email' && isset($send_via['email'])) {
                    $to = $contact['contact'];

                    if ((new EmailValidator())->isValid($to, new RFCValidation())) {
                        $this->dispatchCheckInViaEmail($message_service, $contact, $to, $creator, $recipient);
                    } else {
                        Log::warning("Can't send a check-in to an invalid email address: '" . $to . "'");
                        continue;
                    }

                } else if ($contact['type'] === 'phone' && isset($send_via['sms'])) {

                    try {
                        $to = App::make('TenFour\Messaging\PhoneNumberAdapter');
                        $to->setRawNumber($contact['contact']);
                    } catch (NumberParseException $exception) {
                        Log::warning("Can't send a check-in to an invalid phone number: " . $exception);
                        continue;
                    }

                    $from = $this->getFromNumber($check_in_repo, $contact, $to);

                    if ($check_in_repo->isOutgoingNumberActive($contact['id'], $from)) {
                        // only send reminder if we are reusing a number on which there is an active check-in
                        $this->dispatchCheckInReminderViaSMS($check_in_repo, $message_service, $contact, $recipient, $to, $org_url, $from);
                    }

                    $this->dispatchCheckInViaSMS($message_service, $from, $to, $recipient, $org_url, $sender_name, $org_name);

                    $creditAdjustmentMeta['credits']++;
                    $creditAdjustmentMeta['contacts']++;

                } else if ($contact['type'] === 'slack' && isset($send_via['slack'])) {

                    $this->dispatchCheckInViaSlack();
                }

                $check_in_repo->addMessage($this->check_in['id'], $contact['id'], $from);
                $creditAdjustmentMeta['recipients']++;
            }
        }

        if ($creditAdjustmentMeta['credits'] > 0) {
            App::make('TenFour\Services\CreditService')->addCreditAdjustment($this->check_in['organization_id'], 0-$creditAdjustmentMeta['credits'], 'check-in', $creditAdjustmentMeta);
        }

        (new AnalyticsService())->track('CheckIn Sent', [
            'org_id'            => $this->check_in['organization_id'],
            'check_in_id'        => $this->check_in['id'],
            'total_recipients'  => count($this->check_in['recipients']),
        ]);

        Statsd::increment('checkin.sent');
    }

    protected function getFromNumber($check_in_repo, $contact, $to) {
        // if there is already a from number used for this contact/check-in then use that
        $previously_sent_from = $check_in_repo->getOutgoingNumberForCheckInToContact($this->check_in['id'], $contact['id']);

        if ($previously_sent_from) {
            return $previously_sent_from;
        }

        $region_code = $to->getRegionCode();

        $from = config('tenfour.messaging.sms_providers.'.$region_code.'.from');

        if (! $from) {
            $from = config('tenfour.messaging.sms_providers.default.from');
        }

        if (is_array($from)) {
            if (!config('tenfour.messaging.skip_number_shuffle')) {
                shuffle($from);
            }

            // if possible, get an outgoing number with no unreplied check-ins for this user
            foreach ($from as $from_number) {
                if (!$check_in_repo->isOutgoingNumberActive($contact['id'], $from_number)) {
                    return $from_number;
                }
            }

            return $from[0];
        } else {
            return $from;
        }
    }

    protected function dispatchCheckInViaFCM($message_service, $organization, $sender_name) {

        $recipient_ids = array_map(function ($value) {
            return $value['id'];
        }, $this->check_in['recipients']);

        $to = DeviceToken::whereIn('user_id', $recipient_ids)->pluck('token')->all();

        $params['type'] = 'checkin:created';
        $params['org_name'] = $organization->name;
        $params['org_id'] = $organization->id;
        $params['checkin_id'] = $this->check_in['id'];
        $params['sender_name'] = $sender_name;
        $params['sender_id'] = $this->check_in['user_id'];

        $message_service->setView('fcm.checkin');
        $message_service->send($to, $this->check_in['message'], $params, null, null);
    }

    protected function dispatchCheckInViaEmail($message_service, $contact, $to, $creator, $recipient) {
        if ($contact['bounce_count'] >= config('tenfour.messaging.bounce_threshold')) {
            Log::info('Cannot send check-in for ' . $contact['contact'] . ' because bounces exceed threshold');
            return;
        }

        $message_service->send(
            $to,
            new CheckInMail($this->check_in, $this->organization, $creator, $contact, $recipient),
            [
                'check_in_id' => $this->check_in['id'],
                'type' => 'check_in'
            ]
        );
    }

    protected function dispatchCheckInViaSMS($message_service, $from, $to, $recipient, $org_url, $sender_name, $org_name) {

        $params = [];

        $check_in_url = $org_url .
            '/#/r/' .
            $this->check_in['id'] . '/' .
            '-/' .
            $recipient['id'] . '/' .
            urlencode($recipient['reply_token']);

        $check_in_url = $params['check_in_url'] = $this->shortener->shorten($check_in_url);
        $params['answers'] = $this->check_in['answers'];
        $params['keyword'] = $message_service->getKeyword($to);
        $params['sender_name'] = $sender_name;
        $params['org_name'] = $org_name;
        $msg = $this->check_in['message'];

        if ($this->isURLOnSMSBoundary('sms.checkin', ['msg' => $msg] + $params)) {
            // send sms without check-in url
            unset($params['check_in_url']);
            $this->sendCheckInSMS($message_service, $from, $to, $msg, ['msg' => $msg] + $params);
            // send check-in url
            $this->sendCheckInURLSMS($message_service, $from, $to, $check_in_url);
        } else {
            // send together
            $this->sendCheckInSMS($message_service, $from, $to, $msg, ['msg' => $msg] + $params);
        }
    }

    protected function dispatchCheckInViaSlack() {
        // TODO send private message on slack
        // https://github.com/ushahidi/CheckIn/issues/633
    }

    protected function dispatchCheckInReminderViaSMS($check_in_repo, $message_service, $contact, $recipient, $to, $org_url, $from) {
        $unreplied_sms_check_in = $check_in_repo->getLastUnrepliedByContact($contact['id'], $from);

        if ($unreplied_sms_check_in['id'] === $this->check_in['id']) {
            return;
        }

        if ($unreplied_sms_check_in && $unreplied_sms_check_in['id'] && $unreplied_sms_check_in['from']) {
            $reminder_reply_token = $check_in_repo->getReplyToken($unreplied_sms_check_in['id'], $recipient['id']);

            $reminder_sms_url = $org_url .
                '/#/r/' .
                $unreplied_sms_check_in['id'] . '/' .
                '-/' .
                $recipient['id'] . '/' .
                urlencode($reminder_reply_token);

            $reminder_sms_url = $this->shortener->shorten($reminder_sms_url);

            $this->sendReminderSMS($message_service, $to, $reminder_sms_url, $from, $unreplied_sms_check_in['id']);
        }
    }

    /*
     * Work out by which channel we should send this check-in
     */
    protected function getSendVia($contacts) {
        $send_via = ['app'];
        $preferred = [];
        $subscription = $this->organization['current_subscription'];

        if (!isset($this->check_in['send_via']) || empty($this->check_in['send_via'])) {
            return $send_via;
        }

        if (!$subscription || $subscription['plan_id'] === config("chargebee.plans.free")) {
            return $send_via;
        }

        if (in_array('preferred', $this->check_in['send_via'])) {
            $preferred = array_map(function ($contact) {
                return $contact['type'];
            }, array_filter($contacts, function ($contact) {
                return $contact['preferred'];
            }));
        }

        if ((in_array('sms', $this->check_in['send_via']) || in_array('phone', $preferred)) &&
            isset($this->channels->sms) && $this->channels->sms->enabled) {
            $send_via['sms'] = true;
        }

        if ((in_array('email', $this->check_in['send_via']) || in_array('email', $preferred)) &&
            isset($this->channels->email) && $this->channels->email->enabled) {
            $send_via['email'] = true;
        }

        if ((in_array('slack', $this->check_in['send_via']) || in_array('slack', $preferred)) &&
            isset($this->channels->slack) && $this->channels->slack->enabled) {
            $send_via['slack'] = true;
        }

        return $send_via;
    }

    public function isURLOnSMSBoundary($view, $data, $url_param = 'check_in_url') {
        $len_with_url = mb_strlen(view($view, $data));
        $count_with_url = floor($len_with_url / SMS_BYTECOUNT);

        unset($data[$url_param]);
        $len_without_url =  mb_strlen(view($view, $data));
        $count_without_url = floor($len_without_url / SMS_BYTECOUNT);

        return $count_with_url !== $count_without_url;
    }

    private function sendCheckInSMS(SMSService $message_service, $from, $to, $msg, $params) {
        $params['sms_type'] = 'check_in';
        $params['check_in_id'] = $this->check_in['id'];

        $message_service->setView('sms.checkin');
        $message_service->send($to, $msg, $params, null, $from);
    }

    private function sendCheckInURLSMS(SMSService $message_service, $from, $to, $check_in_url) {
        $params = [
            'sms_type' => 'check_in_url',
            'check_in_id' => $this->check_in['id']
        ];

        $message_service->setView('sms.checkin_url');
        $message_service->send($to, $check_in_url, $params, null, $from);
    }

    private function sendReminderSMS(SMSService $message_service, $to, $check_in_url, $from, $check_in_id) {
        $params = [
            'sms_type' => 'reminder',
            'check_in_id' => $check_in_id
        ];

        $message_service->setView('sms.unresponsive');
        $message_service->send($to, $check_in_url, $params, null, $from);
    }

}
