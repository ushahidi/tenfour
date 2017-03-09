<?php

namespace RollCall\Messaging;

use Mail;
use Illuminate\Contracts\Mail\Mailable;

use RollCall\Contracts\Messaging\MessageService;

class MailService implements MessageService
{
    protected $view;

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        if ($msg instanceof Mailable) {
            Mail::to($to)->send($msg);
        } else {
            $params = ['msg' => $msg] + $additional_params;
            $subject = $subject ? $subject : str_limit($msg, $limit = 50, $end = '...');

            Mail::queue($this->view, $params, function($message) use ($to, $subject) {
                $message->to($to);
                $message->subject($subject);
            });
        }
    }

    public function getMessages(Array $options = [])
    {
        //
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}
