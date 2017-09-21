<?php

namespace RollCall\Messaging;

use Mail;
use Illuminate\Contracts\Mail\Mailable;
use RollCall\Models\Mail as OutgoingMail;

use RollCall\Contracts\Messaging\MessageService;

class MailService implements MessageService
{
    protected $view;

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        \Log::debug('in to message_service->send()');

        if ($msg instanceof Mailable) {
            \Log::debug('is mailable');

            Mail::to($to)->send($msg);

            $this->logMail(
                $to,
                $msg->from[0]['address'],
                $msg->subject,
                isset($additional_params['type'])?$additional_params['type']:'other',
                isset($additional_params['rollcall_id'])?$additional_params['rollcall_id']:0
            );
        } else {
            $params = ['msg' => $msg] + $additional_params;
            $subject = $subject ? $subject : str_limit($msg, $limit = 50, $end = '...');

            \Log::debug('is queued');

            Mail::queue($this->view, $params, function($message) use ($to, $subject) {
                $message->to($to);
                $message->subject($subject);
            });

            $this->logMail(
                $to,
                isset($additional_params['from'])?$additional_params['from']:config('mail.from.address'),
                $subject,
                isset($additional_params['type'])?$additional_params['type']:'other',
                isset($additional_params['rollcall_id'])?$additional_params['rollcall_id']:0
            );
        }
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
