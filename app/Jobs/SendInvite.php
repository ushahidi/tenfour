<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use RollCall\Contracts\Messaging\MessageServiceFactory;

class SendInvite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $member;
    protected $organization;

    /**
     * Create a new job instance.
     *
     * @param Array $roll_call
     * @param Array $contact
     *
     * @return void
     */
    public function __construct(Array $member, Array $organization)
    {
        $this->member = $member;
        $this->organization = $organization;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceFactory $message_service_factory)
    {
        $client_url = env('CLIENT_URL', null);
        $url = secure_url(
          $client_url . 'login/invite/'
          .'?email=' . urlencode($this->member['email'])
          .'&userId=' . $this->member['id']
          .'&orgId=' . $this->organization['id']
          .'&token=' . $this->member['invite_token']
        );
        $msg = 'You have been invited to join '.$this->organization['name'].'\'s Rollcall, please click the link below to complete registration';
        $subject = $this->organization['name'] . ' invited you to join Rollcall';

        $message_service = $message_service_factory->make('email');
        $message_service->setView('emails.invite');
        $message_service->send($this->member['email'], $msg, ['url' => $url], $subject);
    }
}
