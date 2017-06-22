<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use RollCall\Mail\RollCall as RollCallMail;
use RollCall\Contracts\Messaging\MessageServiceFactory;
use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Models\Organization;
use RollCall\Messaging\SMSService;
use UrlShortener;
use libphonenumber\NumberParseException;

use Log;
use App;

define('SMS_BYTECOUNT', 140);

class SendRollCall implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $roll_call;

    /**
     * Create a new job instance.
     *
     * @param array $roll_call
     *
     * @return void
     */
    public function __construct(array $roll_call)
    {
        $this->roll_call = $roll_call;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory, RollCallRepository $roll_call_repo, ContactRepository $contact_repo, OrganizationRepository $org_repo, PersonRepository $person_repo)
    {
        $this->organization = $org_repo->find($this->roll_call['organization_id']);
        $this->channels = $org_repo->getSetting($this->roll_call['organization_id'], 'channels');

        // Get complaint count for org
        $complaint_count = $roll_call_repo->getComplaintCountByOrg($this->roll_call['organization_id']);

        // If complaint count is greater than threshold, log and don't send
        if ($complaint_count >= config('rollcall.messaging.complaint_threshold')) {
            Log::warning('Cannot send RollCall for ' . $this->organization['name'] . ' because complaints exceed threshold');
            return;
        }

        $creator = $person_repo->find($this->roll_call['organization_id'], $this->roll_call['user_id']);

        $org_url = Organization::findOrFail($this->roll_call['organization_id'])->url();

        // Get creator's contact
        $creator_contacts = $contact_repo->getByUserId($this->roll_call['user_id']);

        // Try to get an email address
        $contact = array_first($creator_contacts, function($contact, $key) {
            return $contact['type'] === 'email';
        }, $creator_contacts[0]);

        $creator['email'] = $contact['contact'];

        foreach($this->roll_call['recipients'] as $recipient)
        {
            // Check if user has a pending reply
            $unreplied_roll_call_id = $roll_call_repo->getLastUnrepliedByUser($recipient['id']);

            // Set state to unresponsive if no reply found
            if ($unreplied_roll_call_id) {
                $roll_call_repo->updateRecipientStatus($unreplied_roll_call_id, $recipient['id'], 'unresponsive');
            }

            // Update response status to 'waiting'
            $roll_call_repo->updateRecipientStatus($this->roll_call['id'], $recipient['id'], 'waiting');

            // Set a reply token
            $recipient['reply_token'] = $roll_call_repo->setReplyToken($this->roll_call['id'], $recipient['id']);

            $contacts = $contact_repo->getByUserId($recipient['id'], $this->roll_call['send_via']);
            $send_via = $this->getSendVia($contacts);

            foreach($contacts as $contact)
            {
                if ($contact['blocked']) {
                    continue;
                }

                $message_service = $message_service_factory->make($contact['type']);
                $to = $contact['contact'];

                if ($contact['type'] === 'email' && isset($send_via['email'])) {

                    if ($contact['bounce_count'] >= config('rollcall.messaging.bounce_threshold')) {
                        Log::info('Cannot send roll call for ' . $contact['contact'] . ' because bounces exceed threshold');
                        continue;
                    }

                    $message_service->send($to, new RollCallMail($this->roll_call, $this->organization, $creator, $contact, $recipient));

                } else if ($contact['type'] === 'phone' && isset($send_via['sms'])) {
                    // Wrap phone number
                    try {
                        $to = App::make('RollCall\Messaging\PhoneNumberAdapter', [$to]);
                    } catch (NumberParseException $exception) {
                        // Can't send a message to an invalid number
                        continue;
                    }

                    // Send reminder SMS to unresponsive recipient
                    $unreplied_sms_roll_call_id = $roll_call_repo->getLastUnrepliedByContact($contact['id']);

                    if ($unreplied_sms_roll_call_id) {
                        $reminder_reply_token = $roll_call_repo->getReplyToken($unreplied_sms_roll_call_id, $recipient['id']);
                        $reminder_sms_url = $this->shortenUrl($org_url .'/r/'. $unreplied_sms_roll_call_id .  '/-/' . $recipient['id'] . '?token=' . urlencode($reminder_reply_token));
                        $this->sendReminderSMS($message_service, $to, $reminder_sms_url);
                    }

                    $params = [];
                    $rollcall_url = $org_url .'/r/'. $this->roll_call['id'] .  '/-/' . $recipient['id'] . '?token=' . urlencode($recipient['reply_token']);
                    $rollcall_url = $params['rollcall_url'] = $this->shortenUrl($rollcall_url );
                    $params['answers'] = $this->roll_call['answers'];
                    $params['keyword'] = $message_service->getKeyword($to);
                    $msg = $this->roll_call['message'];

                    if ($this->isURLOnSMSBoundary('sms.rollcall', ['msg' => $msg] + $params)) {
                        // send sms without rollcall url
                        unset($params['rollcall_url']);
                        $this->sendRollCallSMS($message_service, $to, $msg, ['msg' => $msg] + $params);
                        // send rollcall url
                        $this->sendRollCallURLSMS($message_service, $to, $rollcall_url);
                    } else {
                        // send together
                        $this->sendRollCallSMS($message_service, $to, $msg, ['msg' => $msg] + $params);
                    }

                    App::make('RollCall\Services\CreditService')->addCreditAdjustment($this->roll_call['organization_id'], -1, 'rollcall');

                } else if ($contact['type'] === 'slack' && isset($send_via['slack'])) {
                    // TODO send private message on slack
                    // https://github.com/ushahidi/RollCall/issues/633
                }

                // Log message for recipient
                $roll_call_repo->addMessage($this->roll_call['id'], $contact['id']);
            }
        }
    }

    /*
     * Work out by which channel we should send this RollCall
     */
    protected function getSendVia($contacts) {
        $send_via = [];
        $preferred = [];

        if (!isset($this->roll_call['send_via']) || empty($this->roll_call['send_via'])) {
            return [];
        }

        if (in_array('preferred', $this->roll_call['send_via'])) {
            $preferred = array_map(function ($contact) {
                return $contact['type'];
            }, array_filter($contacts, function ($contact) {
                return $contact['preferred'];
            }));
        }

        if ((in_array('sms', $this->roll_call['send_via']) || in_array('phone', $preferred)) &&
            isset($this->channels->sms) && $this->channels->sms->enabled) {
            $send_via['sms'] = true;
        }

        if ((in_array('email', $this->roll_call['send_via']) || in_array('email', $preferred)) &&
            isset($this->channels->email) && $this->channels->email->enabled) {
            $send_via['email'] = true;
        }

        if ((in_array('slack', $this->roll_call['send_via']) || in_array('slack', $preferred)) &&
            isset($this->channels->slack) && $this->channels->slack->enabled) {
            $send_via['slack'] = true;
        }

        return $send_via;
    }

    public function isURLOnSMSBoundary($view, $data, $url_param = 'rollcall_url') {
        $len_with_url = mb_strlen(view($view, $data));
        $count_with_url = floor($len_with_url / SMS_BYTECOUNT);

        unset($data[$url_param]);
        $len_without_url =  mb_strlen(view($view, $data));
        $count_without_url = floor($len_without_url / SMS_BYTECOUNT);

        return $count_with_url !== $count_without_url;
    }

    private function sendRollCallSMS(SMSService $message_service, $to, $msg, $params) {
        // \Log::info('Sending "RollCallSMS" to=' . $to . ' msg=' . $msg);

        $message_service->setView('sms.rollcall');
        $message_service->send($to, $msg, $params);
    }

    private function sendRollCallURLSMS(SMSService $message_service, $to, $rollcall_url) {
        // \Log::info('Sending "RollCallURLSMS" to=' . $to . ' msg=' . $rollcall_url);

        $message_service->setView('sms.rollcall_url');
        $message_service->send($to, $rollcall_url);
    }

    private function sendReminderSMS(SMSService $message_service, $to, $rollcall_url) {
        // \Log::info('Sending "ReminderSMS" to=' . $to . ' msg=' . $rollcall_url);
        // @TODO include previous rollcall message

        $message_service->setView('sms.unresponsive');
        $message_service->send($to, $rollcall_url);
    }

    private function shortenUrl($url) {
        try {
            $url = UrlShortener::shorten($url);
        } catch (\Waavi\UrlShortener\Exceptions\InvalidResponseException $e) {
            \Log::error($e);
        }

        return $url;
    }

}
