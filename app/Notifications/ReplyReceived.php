<?php

namespace TenFour\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use TenFour\Models\Reply;
use TenFour\Http\Transformers\UserTransformer;


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
            'check_in_id' => $this->reply->check_in_id,
            'profile_picture' => $this->reply->user->profile_picture,
            'initials' => UserTransformer::generateInitials($this->reply->user->name),
        ];
    }
}
