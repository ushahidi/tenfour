<?php

namespace RollCall\Notifications;

use App;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Http\Transformers\UserTransformer;

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

        $subject = 'Reset Password';

        $data = [
            'action_url'      => $resetLink,
            'action_text'     => $subject,
            'subject'         => 'Need to reset your password?',
            'org_subdomain'   => $this->organization['subdomain'],
            'org_name'        => $this->organization['name'],
            'profile_picture' => $this->organization['profile_picture'],
            'initials'        => UserTransformer::generateInitials($this->organization['name']),
            'body'            => 'You\'re receiving this email because we received a request to reset the password for your ' . $this->organization['name'] . ' RollCall account.'
        ];

        return (new MailMessage)
            ->view('emails.general', $data)
            ->subject($subject);
    }
}
