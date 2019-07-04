<?php

namespace TenFour\Channels;

use Illuminate\Notifications\Notification;

use Log;
use App;

class Voice
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
         $basic  = new \Nexmo\Client\Credentials\Basic(
            config('sms.nexmo.api_key'),
            config('sms.nexmo.api_secret'));
         $keypair = new \Nexmo\Client\Credentials\Keypair(
            file_get_contents(__DIR__ . '/../../storage/nexmo-private.key'),
                config('sms.nexmo.app_id'));
         $client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Container($basic, $keypair));

         try {
            $client->calls()->create($notification->toVoice($contact));
         } catch (\Nexmo\Client\Exception\Request $exception) {
            Log::warning($exception);
            app('sentry')->captureException($exception);
         }
    }

}
