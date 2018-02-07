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
          $msg = 'You have been invited to join '.$this->organization['name'].'\'s TenFour, please click the link below to complete registration';
          $subject = $this->organization['name'] . ' invited you to join TenFour';

          $message_service = $message_service_factory->make('email');
          $message_service->setView('emails.general');
          $message_service->send($email, $msg, [
            'action_url' => $shortener->shorten($url),
            'action_text' => 'Join ' . $org['name'] . '\'s TenFour',
            'subject' => $subject,
            'org_name' => $org['name'],
            'org_subdomain' => $org['subdomain'],
            'profile_picture' => $org['profile_picture'],
            'initials' => UserTransformer::generateInitials($org['name']),
            'type' => 'invite',
            'body' => $org['name'] . ' uses TenFour to reach people like you on any device and get quick answers to urgent questions. By joining ' . $org['name'] . ' on TenFour, you\'ll be able to easily see and respond to questions, and configure notifications.'
          ], $subject);
        } else {
          Log::info('Cannot invite a member with no email address');
        }

    }
}
