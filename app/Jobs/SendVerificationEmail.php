<?php

namespace TenFour\Jobs;

use Illuminate\Queue\SerializesModels;
use TenFour\Contracts\Messaging\MessageServiceFactory;
use TenFour\Mail\Verification as VerificationMail;

class SendVerificationEmail
{
    use SerializesModels;

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
