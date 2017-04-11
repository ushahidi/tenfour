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

use Log;

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
        $organization = $org_repo->find($this->roll_call['organization_id']);

        // Get complaint count for org
        $complaint_count = $roll_call_repo->getComplaintCountByOrg($this->roll_call['organization_id']);

        // If complaint count is greater than threshold, log and don't send
        if ($complaint_count >= config('rollcall.messaging.complaint_threshold')) {
            Log::info('Cannot send roll call for ' . $organization['name'] . ' because complaints exceed threshold');
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

            $contacts = $contact_repo->getByUserId($recipient['id'], $this->roll_call['send_via']);

            foreach($contacts as $contact)
            {
                if (!$contact['subscribed']) {
                  continue;
                }

                $message_service = $message_service_factory->make($contact['type']);
                $to = $contact['contact'];

                if ($contact['type'] === 'email') {
                    if ($contact['bounce_count'] >= config('rollcall.messaging.bounce_threshold')) {
                        Log::info('Cannot send roll call for ' . $contact['contact'] . ' because bounces exceed threshold');
                        continue;
                    }

                    $message_service->send($to, new RollCallMail($this->roll_call, $organization, $creator, $contact));
                } else if ($contact['type'] === 'phone') {
                    // Send reminder SMS to unresponsive recipient
                    $unreplied_sms_roll_call_id = $roll_call_repo->getLastUnrepliedByContact($contact['id']);

                    if ($unreplied_sms_roll_call_id) {
                        $this->sendReminderSMS($message_service, $to, $org_url .'/rollcalls/'. $unreplied_sms_roll_call_id);
                    }

                    $params = [];
                    $rollcall_url = $params['rollcall_url'] = $org_url .'/rollcalls/'. $this->roll_call['id'];
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
                }

                // Update response status to 'waiting'
                $roll_call_repo->updateRecipientStatus($this->roll_call['id'], $recipient['id'], 'waiting');

                // Log message for recipient
                $roll_call_repo->addMessage($this->roll_call['id'], $contact['id']);
            }
        }
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
}
