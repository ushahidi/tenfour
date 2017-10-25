<?php

namespace RollCall\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Http\Transformers\UserTransformer;

class ImportSucceeded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization, $count)
    {
        $this->organization = $organization;
        $this->count = $count;
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
        $body = 'You successfully imported ' . $this->count . ' members into your organization.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Review my Organization\'s Members',
                'subject'         => 'Import Successful',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('Import Successful');
    }

    private function url()
    {
        return $this->organization->url('/people');
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
            'count' => $this->count,
            'url' => $this->url(),
        ];
    }
}
