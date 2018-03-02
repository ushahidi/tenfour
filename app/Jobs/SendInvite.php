<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use TenFour\Models\Organization;
use TenFour\Models\User;
use TenFour\Contracts\Messaging\MessageServiceFactory;
use TenFour\Http\Transformers\UserTransformer;
use TenFour\Services\URLShortenerService;
use TenFour\Mail\Invite as InviteMail;
use Log;

class SendInvite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $member;
    protected $organization;

    /**
     * Create a new job instance.
     *
     * @param Array $check_in
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
    public function handle(MessageServiceFactory $message_service_factory, URLShortenerService $shortener)
    {
        foreach($this->member['contacts'] as $contact)
        {
          if ($contact['type'] == 'email') {
            $email = $contact['contact'];
          }
        }

        if (isset($email)) {
          $message_service = $message_service_factory->make('email');
          $message_service->send($email,
              new InviteMail($this->organization, $email, $this->member),
              [
                  'type' => 'invite',
              ]);
        } else {
          throw new Exception('Cannot invite a member with no email address');
        }

    }
}
