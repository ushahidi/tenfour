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
            if (! $roll_call_repo->getMessages($this->roll_call['id'], $recipient['id'])) {

                // TODO: Filter by preferred method of sending
                $contacts = $contact_repo->getByUserId($recipient['id']);

                foreach($contacts as $contact)
                {
                    $message_service = $message_service_factory->make($contact['type']);

                    if (config('sms.driver') === 'africastalking') {
                        $message_service->setView('sms.africastalking');
                    }

                    if ($contact['type'] === 'email') {
                        $message_service->send($contact['contact'], new RollCallMail($this->roll_call, $organization, $creator));
                    } else {
                        $message_service->send($contact['contact'], $this->roll_call['message']);
                    }

                    $roll_call_repo->addMessage($this->roll_call['id'], $contact['id']);
                }
            }
        }
    }
}
