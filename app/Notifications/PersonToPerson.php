<?php

namespace TenFour\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Http\Transformers\UserTransformer;

class PersonToPerson extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization, $from, $message)
    {
        $this->organization = $organization;
        $this->from = $from;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $body = 'You have received a new notification from "' . $this->from->name . '"' .
            ': <br><br><em>' . $this->message . '</em>';

        return (new MailMessage)
            ->view('emails.general', [
                'subject'         => 'New Notification',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('New Notification');
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
            'message' => $this->message,
            'person_name' => $this->from->name,
            'person_id' => $this->from->id,
            'profile_picture' => $this->from->profile_picture || null,
            'initials' => UserTransformer::generateInitials($this->from->name),

        ];
    }
}
