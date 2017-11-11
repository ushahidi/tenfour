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

    protected function logMail($to, $from, $subject, $type, $rollcall_id)
    {
        $mail = new OutgoingMail;
        $mail->to = $to;
        $mail->from = $from;
        $mail->subject = $subject;
        $mail->rollcall_id = $rollcall_id;
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
