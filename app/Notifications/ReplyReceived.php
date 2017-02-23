<?php

namespace RollCall\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RollCall\Models\Reply;
use RollCall\Http\Transformers\UserTransformer;


class ReplyReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
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
            'reply_from' => $this->reply->user->name,
            'rollcall_id' => $this->reply->roll_call_id,
            'gravatar' => ! empty($this->reply->user->email) ? md5(strtolower(trim($this->reply->user->email))) : '00000000000000000000000000000000',
            'profile_picture' => $this->reply->user->profile_picture,
            'initials' => UserTransformer::generateInitials($this->reply->user->name),
        ];
    }
}
