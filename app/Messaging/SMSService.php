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

        // Get driver
        $driver = config('rollcall.messaging.sms_providers.'.$region_code.'.driver');
        $from = config('rollcall.messaging.sms_providers.'.$region_code.'.from');

        if (! $driver) {
            $driver = config('rollcall.messaging.sms_providers.default.driver');
            $from = config('rollcall.messaging.sms_providers.default.from');
        }

        // Set 'from' address if configured
        if ($from) {
            SMS::alwaysFrom($from);
        }

        // Set SMS driver for the region code
        SMS::driver($driver);

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
        // Get configured drivers
        $sms_providers = config('rollcall.messaging.sms_providers');

        $drivers = [];

        foreach($sms_providers as $provider)
        {
            // XXX: Assumes that we are polling for messages for each configured driver
            array_push($drivers, $provider['driver']);
        }

        $drivers = array_unique($drivers);

        $messages = [];

        foreach($drivers as $driver)
        {
            SMS::driver($driver);

            $incoming_messages = SMS::checkMessages($options);

            foreach($incoming_messages as $incoming_message)
            {
                array_push($messages, $this->transform($incoming_message));
            }
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
