<?php

namespace RollCall\Messaging;

use Mail;
use RollCall\Contracts\Messaging\MessageService;

class MailService implements MessageService
{
    protected $view = 'emails.rollcall';

    public function setView($view)
    {
        $this->view = $view;
    }

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        $params = ['msg' => $msg] + $additional_params;
        $subject = $subject ? $subject : str_limit($msg, $limit = 50, $end = '...');

        Mail::send($this->view, $params, function($message) use ($to, $subject) {
            $message->to($to);
            $message->subject($subject);
        });
    }

    public function getMessages(Array $options = [])
    {
        //
    }
}
