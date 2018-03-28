<?php

namespace TenFour\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Models\User;
use TenFour\Models\CheckIn;
use TenFour\Http\Transformers\UserTransformer;

class Complaint extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $person, CheckIn $check_in)
    {
        $this->person = $person;
        $this->check_in = $check_in;
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
            'check_in_message' => isset($this->check_in)?$this->check_in->message:null,
            'check_in_id' => isset($this->check_in)?$this->check_in->id:null
        ];
    }
}
