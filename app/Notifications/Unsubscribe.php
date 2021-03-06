<?php

namespace TenFour\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Models\User;
use TenFour\Models\Contact;
use TenFour\Http\Transformers\UserTransformer;

class Unsubscribe extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $person, Contact $contact)
    {
        $this->person = $person;
        $this->contact = $contact;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'person_name' => $this->person->name,
            'person_id' => $this->person->id,
            'profile_picture' => $this->person->profile_picture || null,
            'initials' => UserTransformer::generateInitials($this->person->name),
            'contact' => $this->contact->contact,
            'contact_type' => $this->contact->type
        ];
    }
}
