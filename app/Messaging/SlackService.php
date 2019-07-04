<?php

namespace TenFour\Messaging;

use Illuminate\Notifications\Messages\SlackMessage;
use TenFour\Slack\CheckIn as CheckInSlack;
use TenFour\Contracts\Messaging\MessageService;

class SlackService implements MessageService
{

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        if ($msg instanceof CheckInSlack) {
            Mail::to($to)->send($msg);
        } else {
            \Log::error('Refusing to queue a non-mailable mail');
        }
    }

    public function setView($view) {}
    public function getMessages(Array $options = []) {}
}
