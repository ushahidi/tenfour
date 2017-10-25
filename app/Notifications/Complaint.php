<?php

namespace RollCall\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\User;
use RollCall\Models\RollCall;
use RollCall\Http\Transformers\UserTransformer;

class Complaint extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $person, RollCall $roll_call)
    {
        $this->person = $person;
        $this->roll_call = $roll_call;
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
            'rollcall_message' => isset($this->roll_call)?$this->roll_call->message:null,
            'rollcall_id' => isset($this->roll_call)?$this->roll_call->id:null
        ];
    }
}
