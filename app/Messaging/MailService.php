<?php

namespace RollCall\Messaging;

use Mail;

use RollCall\Contracts\Messaging\MessageService;

class MailService implements MessageService
{
    public function send($to, $msg, $additional_params = [])
    {
        Mail::to($to)->send($msg);
    }

    public function getMessages(Array $options = [])
    {
        //
    }

    public function setView($view)
    {
        //
    }
}
