<?php

namespace TenFour\Notifications;

use TenFour\Http\Transformers\UserTransformer;
use TenFour\Http\Requests\Person\AddPersonRequest;
use TenFour\Services\URLFactory;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApproachingPersonQuotaLimit extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($organization)
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
        $body = 'Your TenFour free plan entitles you to ' . $this->maxPeople() . ' people in your organization. ' .
          'You are now approaching that limit. You might want to consider upgrading your plan to add more people.';

        return (new MailMessage)
            ->view('emails.general', [
                'action_url'      => $this->url(),
                'action_text'     => 'Upgrade your Plan',
                'subject'         => 'Upgrade your Plan',
                'profile_picture' => $this->organization->profile_picture,
                'org_subdomain'   => $this->organization->subdomain,
                'org_name'        => $this->organization->name,
                'initials'        => UserTransformer::generateInitials($this->organization->name),
                'body'            => $body
            ])
            ->subject('Upgrade your Plan');
    }

    private function maxPeople()
    {
        return AddPersonRequest::MAX_PERSONS_IN_FREE_PLAN;
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
            'users' => count($this->organization->members),
            'max' => AddPersonRequest::MAX_PERSONS_IN_FREE_PLAN
        ];
    }
}
