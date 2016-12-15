<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use RollCall\Mail\RollCall;

use RollCall\Contracts\Messaging\MessageServiceFactory;

class SendRollCall implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $roll_call;

    protected $contact;

    protected $organization;

    protected $creator;

    /**
     * Create a new job instance.
     *
     * @param array $roll_call
     * @param array $contact
     * @param array $organization
     * @param array $creator
     *
     * @return void
     */
    public function __construct(array $roll_call, array $contact, array $organization, array $creator)
    {
        $this->roll_call = $roll_call;
        $this->contact = $contact;
        $this->organization = $organization;
        $this->creator = $creator;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory)
    {
        $message = null;

        $message_service = $message_service_factory->make($this->contact['type']);

        if (config('sms.driver') == 'africastalking') {
            $message_service->setView('sms.africastalking');
        }

        if ($this->contact['type'] === 'email') {
            $message = new RollCall($this->roll_call, $this->organization, $this->creator);
        } else {
            $message = $this->roll_call['message'];
        }

        $message_service->send($this->contact['contact'], $message);
    }
}
