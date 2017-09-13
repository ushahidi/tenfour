<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use RollCall\Contracts\Messaging\MessageServiceFactory;

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
        $url = 'https://app.' . config('rollcall.domain') . '/organization/email/confirmation/?email='.urlencode($this->address['address']).'&token=' . urlencode($this->address['verification_token']);
        $subject = 'Verify your RollCall email address';

        $message_service = $message_service_factory->make('email');
        $message_service->setView('emails.general');
        $message_service->send($this->address['address'], '', [
            'type' => 'verification',
            'action_url' => $url,
            'action_text' => $subject,
            'subject' => $subject,
            'profile_picture' => config('mail.empty_account_img'),
            'org_subdomain' => 'www',
            'org_name' => 'RollCall',
            'body' => 'Ushahidi uses RollCall to reach people like you on any device and get quick answers to urgent questions.<br><br>Verifying your email address will allow you to create an account and an organization on RollCall.'
        ], $subject);
    }
}
