<?php

namespace TenFour\Notifications;

use TenFour\Http\Transformers\UserTransformer;
use TenFour\Services\URLFactory;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreditsLow extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization, $credits)
    {
        $this->organization = $organization;
        $this->credits = $credits;
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
        $body = 'It is important that you can reach your entire team in an emergency. ' .
            'Get more text credits now, so you\'re ready for anything.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Get More Credits',
                'subject'         => 'Your account is running low on credits',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('Your TenFour account is running low on credits');
    }

    private function url()
    {
        return URLFactory::makePaymentsURL($this->organization);
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
            'credits' => $this->credits
        ];
    }
}
