<?php

namespace TenFour\Messaging;

use Log;
use SMS;
use App;
use TenFour\Contracts\Messaging\MessageService;
use TenFour\Jobs\SendFCM;

class FCMService implements MessageService
{
    private $view;

    public function __construct()
    {
    }

    public function setView($view)
    {
        $this->view = $view;
    }


    public function send($to, $msg = '', $additional_params = [], $subject = null, $from = null)
    {
        if (count($to) == 0) {
            return;
        }

        if (!config('fcm.http.server_key') || !config('fcm.http.sender_id')) {
            Log::warning('FCM not configured - skipping sending message "' . $subject . '"');
            return;
        }

        $additional_params['msg'] = $msg;
        $additional_params['title'] = $subject;

        dispatch((new SendFCM($this->view, $additional_params, $to))/*->onQueue('sms')*/);
    }


    public function getMessages(Array $options = [])
    {
    }

}
