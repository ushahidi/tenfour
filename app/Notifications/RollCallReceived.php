<?php

namespace RollCall\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\RollCall;

class RollCallReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Rollcall $rollcall)
    {
        $this->rollcall = $rollcall;
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
            'rollcall_message' => $this->rollcall->message,
            'rollcall_id' => $this->rollcall->id,
            'avatar' => 'http://www.gravatar.com/avatar/647a5458daa00a474a611a5b0d603b9b?d=identicon&s=40' // @todo replace with real avatar
        ];
    }
}
