<?php

namespace TenFour\Notifications;

use TenFour\Http\Transformers\UserTransformer;
use TenFour\Channels\FCM as FCMChannel;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreditsChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($credits)
    {
        $this->credits = $credits;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FCMChannel::class];
    }

    /**
     * Get the fcm representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFCM($notifiable)
    {
        return [
            'type'    => 'credits:changed',
            'subject' => 'Credits Changed',
            'msg'     => null,
            'credits' => $this->credits
        ];
    }
}
