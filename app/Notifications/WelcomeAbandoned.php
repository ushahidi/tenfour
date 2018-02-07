<?php

namespace TenFour\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Models\Organization;
use TenFour\Http\Transformers\UserTransformer;

// Send followup email who abandon at payments screen and let them know you have a free trial
// https://github.com/ushahidi/RollCall/issues/847

class WelcomeAbandoned extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $body = "Thank you for checking out TenFour. TenFour helps you reach your team during an emergency." .
            "<br><br>" .
            "Finish signing up at " .
            "<a href='" . $this->url() . "'>" . $this->url() . "</a> " .
            " to make sure your team is prepared.";

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Log in to TenFour',
                'subject'         => 'Welcome to TenFour',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('Welcome to TenFour');
    }

    private function url()
    {
        return $this->organization->url();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'url' => $this->url(),
        ];
    }
}
