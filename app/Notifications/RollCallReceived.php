<?php

namespace RollCall\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\RollCall;
use RollCall\Http\Transformers\UserTransformer;

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
            'gravatar' => ! empty($this->rollcall->user->email) ? md5(strtolower(trim($this->rollcall->user->email))) : '00000000000000000000000000000000',
            'profile_picture' => $this->rollcall->user->profile_picture || null,
            'initials' => UserTransformer::generateInitials($this->rollcall->user->name),
        ];
    }
}
