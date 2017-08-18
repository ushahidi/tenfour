<?php

namespace RollCall\Notifications;

use App;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token, $email, $organization)
    {
        $this->token = $token;
        $this->email = $email;
        $this->organization = $organization;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetLink = url($this->organization->url() .
            '/login/reset-password/' .
            $this->token .
            '?email=' . urlencode($this->email) .
            '&subdomain=' . urlencode($this->organization['subdomain']));

        $data = [
            'link'                    => $resetLink,
            'organization_subdomain'  => $this->organization['subdomain'],
            'organization_name'       => $this->organization['name'],
        ];

        return (new MailMessage)
            ->view('emails.reset_password', $data)
            ->subject('Reset Password');
    }
}
