<?php

namespace RollCall\Messaging;

use SMS;
use RollCall\Contracts\Messaging\MessageService;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class SMSService implements MessageService
{
    public function setView($view)
    {
        $this->view = $view;
    }

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        // Get region code. The assumption is that all phone numbers are passed as
        // international numbers
        if (! starts_with($to, '+')) {
            $phone_number = '+'.$to;
        } else {
            $phone_number = $to;
        }

        $phone_number_util = PhoneNumberUtil::getInstance();

        try {
            $phone_number_obj = $phone_number_util->parse($phone_number, null);
        }

        catch (NumberParseException $exception) {
            // Can't send a message to an invalid number
            return;
        }

        $region_code = $phone_number_util->getRegionCodeForNumber($phone_number_obj);

        // Set SMS driver for the region code
        SMS::driver(config('rollcall.messaging.sms_drivers.'.$region_code));

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
