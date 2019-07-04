<?php

namespace TenFour\Mail;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use TenFour\Services\URLFactory;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;

class Invite extends Mailable
{
    use Queueable, SerializesModels;

    protected $organization;
    protected $email;
    protected $member;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $organization, string $email, array $member)
    {
        $this->organization = $organization;
        $this->email = $email;
        $this->member = $member;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        foreach($this->member['contacts'] as $contact)
        {
          if ($contact['type'] == 'email') {
            $email = $contact['contact'];
          }
        }

        $url = URLFactory::makeInviteURL(
            Organization::findOrFail($this->organization['id']),
            $this->member['id'],
            $this->email,
            $this->member['invite_token']
          );

        $msg = 'You have been invited to join '.$this->organization['name'].'\'s TenFour, please click the link below to complete registration';
        $subject = $this->organization['name'] . ' invited you to join TenFour';

        return $this->view('emails.general')
            ->with([
                'action_url' => URLFactory::shorten($url),
                'action_text' => 'Join ' . $this->organization['name'] . '\'s TenFour',
                'subject' => $subject,
                'org_name' => $this->organization['name'],
                'org_subdomain' => $this->organization['subdomain'],
                'profile_picture' => $this->organization['profile_picture'],
                'initials' => UserTransformer::generateInitials($this->organization['name']),
                'type' => 'invite',
                'body' => $this->organization['name'] . ' uses TenFour to reach people like you on any device and get quick answers to urgent questions. By joining ' .
                    $this->organization['name'] . ' on TenFour, you\'ll be able to easily see and respond to questions, and configure notifications.'
            ])
            ->subject($subject);
    }
}
