<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use TenFour\Contracts\Messaging\MessageServiceFactory;
use TenFour\Mail\Verification as VerificationMail;

class SendVerificationEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $address;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $address)
    {
        $this->address = $address;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory)
    {
        $message_service = $message_service_factory->make('email');
        $message_service->send($this->address['address'],
            new VerificationMail($this->address),
            [
                'type' => 'verification',
            ]);
    }
}
