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
use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Models\Organization;

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
    public function handle(MessageServiceFactory $message_service_factory, RollCallRepository $roll_call_repo, ContactRepository $contact_repo, OrganizationRepository $org_repo, UserRepository $user_repo)
    {
        $organization = $org_repo->find($this->roll_call['organization_id']);
        $creator = $user_repo->find($this->roll_call['user_id']);

        // Get creator's contact
        $creator_contacts = $contact_repo->getByUserId($this->roll_call['user_id']);

        // Try to get an email address
        $contact = array_first($creator_contacts, function($contact, $key) {
            return $contact['type'] === 'email';
        }, $creator_contacts[0]);

        $creator['email'] = $contact['contact'];

        foreach($this->roll_call['recipients'] as $recipient)
        {
            // TODO: Filter by preferred method of sending
            $contacts = $contact_repo->getByUserId($recipient['id']);

            foreach($contacts as $contact)
            {
                // Check if contact has a pending reply
                $unreplied_roll_call_id = $roll_call_repo->getLastUnrepliedByContact($contact['id']);

                // Set state to unresponsive if no reply found
                if ($unreplied_roll_call_id) {
                    $roll_call_repo->updateRecipientStatus($unreplied_roll_call_id, $recipient['id'], 'unresponsive');
                }

                $message_service = $message_service_factory->make($contact['type']);

                if ($contact['type'] === 'email') {
                        $message_service->send($contact['contact'], new RollCallMail($this->roll_call, $organization, $creator, $contact));
                } else {

                    // Send reminder SMS to unresponsive recipient
                    if ($unreplied_roll_call_id) {
                        $org_url = Organization::findOrFail($organization['id'])->url();
                        $roll_call_url = $org_url .'/rollcalls/'. $unreplied_roll_call_id;
                        $message_service->setView('sms.unresponsive');
                        $message_service->send($contact['contact'], $roll_call_url);
                    }

                    $message_service->setView('sms.rollcall');
                    $message_service->send($contact['contact'], $this->roll_call['message']);
                }

                // Update response status to 'waiting'
                $roll_call_repo->updateRecipientStatus($this->roll_call['id'], $recipient['id'], 'waiting');

                // Add message if recipient is receiving this for the first time
                if (! $roll_call_repo->getMessages($this->roll_call['id'], $recipient['id'])) {
                    $roll_call_repo->addMessage($this->roll_call['id'], $contact['id']);
                }
            }
        }
    }
}
