<?php

namespace TenFour\Channels;

use TenFour\Messaging\MailService;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Log;

class CheckInMail
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($contact, Notification $notification)
    {
        $message_service = new MailService;

        $checkInMail = $notification->toMail($contact);

        $message_service->send(
            $contact->contact,
            $checkInMail,
            [
                'check_in_id' => $notification->check_in['id'],
                'type' => 'check_in'
            ]
        );
    }
}
