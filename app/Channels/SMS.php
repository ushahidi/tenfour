<?php

namespace TenFour\Channels;

use TenFour\Messaging\SMSService;

use Illuminate\Notifications\Notification;

use Log;
use App;

class SMS
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
        $message_service = new SMSService();

        $sms = $notification->toSMS($contact);

        $to = App::make('TenFour\Messaging\PhoneNumberAdapter');
        $to->setRawNumber($contact->contact);

        $message_service->setView($sms['view']);
        $message_service->send($to, $sms['msg'], $sms, null, $sms['from']);
    }

}
