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
            $params = ['msg' => $msg] + $additional_params;
            $subject = $subject ? $subject : str_limit($msg, $limit = 50, $end = '...');

            Mail::send($this->view, $params, function($message) use ($to, $subject) {
                $message->to($to);
                $message->subject($subject);
            });
        }
    }

    public function setView($view) {}
    public function getMessages(Array $options = []) {}
}
