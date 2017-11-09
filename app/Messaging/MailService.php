<?php

namespace RollCall\Messaging;

use RollCall\Jobs\SendMail;
use RollCall\Contracts\Messaging\MessageService;

class MailService implements MessageService
{
    protected $view;

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        dispatch((new SendMail($to, $msg, $this->view, $additional_params, $subject))/*->onQueue('mails')*/);
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
