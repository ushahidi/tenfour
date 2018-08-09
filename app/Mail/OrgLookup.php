<?php

namespace TenFour\Mail;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;

class OrgLookup extends Mailable
{
    use Queueable, SerializesModels;

    protected $organizations;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $organizations)
    {
        $this->organizations = $organizations;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $org_url = Organization::findOrFail($this->organizations[0]['id'])->url();

        $subject = 'Your TenFour domain';
        $msg = 'You\'re receiving this email because we received a request to send the domain for your TenFour account.<br>';

        foreach ($this->organizations as $organization) {
            $org_url = Organization::findOrFail($organization['id'])->url();
            $msg .= '<br>Your domain is <a href="' . $org_url . '">' . $org_url . '</a><br>';
        }

        return $this->view('emails.general')
            ->with([
                'action_url' => $org_url,
                'action_text' => 'Sign in now',
                'subject' => $subject,
                'org_name' => $this->organizations[0]['name'],
                'org_subdomain' => $this->organizations[0]['subdomain'],
                'profile_picture' => $this->organizations[0]['profile_picture'],
                'initials' => UserTransformer::generateInitials($this->organizations[0]['name']),
                'type' => 'orglookup',
                'body' => $msg
            ])
            ->subject($subject);
    }
}
