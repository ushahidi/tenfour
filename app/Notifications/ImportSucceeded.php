<?php

namespace TenFour\Notifications;

use TenFour\Http\Transformers\UserTransformer;
use TenFour\Services\URLFactory;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ImportSucceeded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization, $count, $dupe_count)
    {
        $this->organization = $organization;
        $this->count = $count;
        $this->dupe_count = $dupe_count;
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
        $body = '';

        if ($this->dupe_count > 0) {
            $body .= 'You successfully imported ' . $this->count . ' members into your organization. ';
        } else {
            $body .= 'Nobody was imported into your organization. ';
        }

        if ($this->dupe_count > 0) {
            $body .= $this->dupe_count . ' duplicates were found. ';
        }

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
        return URLFactory::makePeopleURL($this->organization);
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
            'dupe_count' => $this->dupe_count,
            'url' => $this->url(),
        ];
    }
}
