<?php

namespace RollCall\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Http\Transformers\UserTransformer;

class ImportFailed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization, $exception)
    {
        $this->organization = $organization;
        $this->exception = $exception;
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
        $body = 'The import failed with the message: <br><br>' . $this->exception->getMessage();

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Try to import again',
                'subject'         => 'Import Failed',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('Import Failed');
    }

    private function url()
    {
        return $this->organization->url('/import/menu');
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
            'message' => $this->exception->getMessage(),
            'url' => $this->url(),
        ];
    }
}
