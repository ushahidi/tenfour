<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use RollCall\Models\Organization;
use RollCall\Models\User;
use RollCall\Contracts\Messaging\MessageServiceFactory;
use Log;

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
        $org = Organization::findOrFail($this->organization['id']);
        $client_url = $org->url();

        foreach($this->member['contacts'] as $contact)
        {
          if ($contact['type'] == 'email') {
            $email = $contact['contact'];
          }
        }

        if (isset($email)) {
          $url = secure_url(
            $client_url . '/login/invite/'
            .'?email=' . urlencode($email)
            .'&personId=' . $this->member['id']
            .'&orgId=' . $this->organization['id']
            .'&token=' . $this->member['invite_token']
          );
          $msg = 'You have been invited to join '.$this->organization['name'].'\'s Rollcall, please click the link below to complete registration';
          $subject = $this->organization['name'] . ' invited you to join Rollcall';

          $message_service = $message_service_factory->make('email');
          $message_service->setView('emails.invite');
          $message_service->send($email, $msg, ['url' => $url], $subject);
        } else {
          Log::info('Cannot invite a member with no email address');
        }

    }
}
