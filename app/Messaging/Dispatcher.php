<?php
namespace RollCall\Messaging;

use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Jobs\SendRollCall;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Dispatcher
{
    use DispatchesJobs;

    public function __construct(RollCallRepository $roll_calls, ContactRepository $contacts)
    {
        $this->roll_calls = $roll_calls;
        $this->contacts = $contacts;
    }

     /**
     * Queue roll call for recipients
     *
     * @param int $roll_call_id
     * @param Array $recipient
     *
     * @return void
     */
    public function queue($roll_call_id, $recipient)
    {
        $roll_call = $this->roll_calls->find($roll_call_id);

        // TODO: Filter by preferred method of sending
        if (!$this->roll_calls->getMessages($roll_call_id, $recipient['id'])) {
            $contacts = $this->contacts->getByUserId($recipient['id']);

            foreach($contacts as $contact)
            {
                $this->dispatch(new SendRollCall($roll_call, $contact));

                $this->roll_calls->addMessage($roll_call['id'], $contact['id']);
            }
        }
    }
}
