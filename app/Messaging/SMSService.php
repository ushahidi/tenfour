<?php

namespace RollCall\Messaging;

use SMS;
use RollCall\Contracts\Messaging\MessageService;

class SMSService implements MessageService
{
    public function setView($view)
    {
        $this->view = $view;
    }

    public function send($to, $msg, $additional_params = [])
    {
        if (isset($this->view)) {
            SMS::send($this->view, ['msg' => $msg], function($sms) use ($to) {
                $sms->to($to);
            });
        } else {
            SMS::send($msg, [], function($sms) use ($to) {
                $sms->to($to);
            });
        }
    }

    public function getMessages(Array $options = [])
    {
        $incoming_messages = SMS::checkMessages($options);

        $messages = [];

        foreach($incoming_messages as $incoming_message)
        {
            array_push($messages, $this->transform($incoming_message));
        }

        return $messages;
    }

    protected function transform($incoming_message)
    {
        $message = [];

        $message['from'] = $incoming_message->from();
        $message['message'] = $incoming_message->message();

        // Get message id from provider
        if (isset($incoming_message->raw()['id'])) {
            $message['id'] = $incoming_message->raw()['id'];
        } else {
            $message['id'] = null;
        }

        return $message;
    }
}
