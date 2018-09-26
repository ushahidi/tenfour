<?php

namespace TenFour\Notifications;

use TenFour\Http\Transformers\UserTransformer;
use TenFour\Channels\FCM as FCMChannel;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CheckInChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $check_in)
    {
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
            'type'    => 'checkin:changed',
            'subject' => 'TenFour Check-In Updated',
            'checkin_id'  => $this->check_in['id'],
        ];
    }
}
