<?php

namespace TenFour\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Http\Transformers\UserTransformer;

class LDAPSyncSucceeded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization, $user_count, $group_count)
    {
        $this->organization = $organization;
        $this->user_count = $user_count;
        $this->group_count = $group_count;
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
        $body = 'You successfully synced with your Active Directory (LDAP) server. ';

        if ($this->user_count > 0) {
            $body .= $this->user_count . ' users were added to your organization. ';
        }

        if ($this->group_count > 0) {
            $body .= $this->group_count . ' groups were added to your organization. ';
        }

        $body .= 'This is a one-time notification.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Review my Organization\'s Members',
                'subject'         => 'Active Directory (LDAP) Sync Successful',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('Active Directory (LDAP) Sync Successful');
    }

    private function url()
    {
        return $this->organization->url('/#/people');
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
            'user_count' => $this->user_count,
            'group_count' => $this->group_count,
            'url' => $this->url(),
        ];
    }
}
