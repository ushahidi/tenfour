<?php

namespace TenFour\Messaging;

use TenFour\Jobs\SendMail;
use TenFour\Contracts\Messaging\MessageService;

class MailService implements MessageService
{
    protected $view;

    public function send($to, $mailable, $additional_params = [], $subject = null)
    {
        dispatch((new SendMail($to, $mailable, $this->view, $additional_params))/*->onQueue('mails')*/);
    }

    protected function logMail($to, $from, $subject, $type, $check_in_id)
    {
        $mail = new OutgoingMail;
        $mail->to = $to;
        $mail->from = $from;
        $mail->subject = $subject;
        $mail->check_in_id = $check_in_id;
        $mail->type = $type;
        $mail->save();
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
