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
    public function __construct($token, $organization)
    {
        $this->token = $token;
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
        //FIXME

        $resetLink = url('https://' .
          (App::environment('staging') ? 'staging.rollcall.io' : $this->organization['domain']) .
          '/login/reset-password/' .
          $this->token);

        $data = [
            'link'              => $resetLink,
            'organization_url'  => $this->organization['subdomain'],
            'organization_name' => $this->organization['name'],
        ];

        return (new MailMessage)
            ->view('emails.resetPassword', $data)
            ->subject('Reset Password');
    }
}
