<?php

namespace TenFour\Channels;

use TenFour\Messaging\FCMService;

use Illuminate\Notifications\Notification;

class FCM
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $params = $notification->toFCM($notifiable);

        $to = $notifiable->deviceTokens()->pluck('token')->toArray();

        (new FCMService())->send($to, $params['msg'], $params, $params['subject']);
    }
}
