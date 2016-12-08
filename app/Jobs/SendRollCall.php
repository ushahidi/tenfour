<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use RollCall\Contracts\Messaging\MessageServiceFactory;

class SendRollCall implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $roll_call;

    protected $contact;

    /**
     * Create a new job instance.
     *
     * @param Array $roll_call
     * @param Array $contact
     *
     * @return void
     */
    public function __construct(Array $roll_call, Array $contact)
    {
        $this->roll_call = $roll_call;
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory)
    {
        $url = url('rollcalls/' . $this->roll_call['id']);

        $message_service = $message_service_factory->make($this->contact['type']);
        $message_service->send($this->contact['contact'], $this->roll_call['message'], ['url' => $url]);
    }
}
