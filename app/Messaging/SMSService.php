<?php

namespace RollCall\Messaging;

use Log;
use SMS;
use App;
use RollCall\Contracts\Messaging\MessageService;
use RollCall\Jobs\SendSMS;

class SMSService implements MessageService
{

    private $view;
    private $iterator;
    private $throttle;

    public function __construct()
    {
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function setIterator($iterator)
    {
        $this->iterator = $iterator;
    }

    public function getDriver($region_code)
    {
        if (config('sms.driver') == 'log') {
            return 'log';
        }

        $driver = config('rollcall.messaging.sms_providers.'.$region_code.'.driver');

        if (!$driver) {
            $driver = config('rollcall.messaging.sms_providers.default.driver');
        }

        return $driver;
    }

    public function getKeyword($to)
    {
        $region_code = $to->getRegionCode();

        $driver = $this->getDriver($region_code);

        return config('sms.'.$driver.'.keyword');
    }

    public function send($to, $msg = '', $additional_params = [], $subject = null, $from = null)
    {
        $region_code = $to->getRegionCode();

        if ($from == null) {
            // Get 'from' address from iterator if set
            if ($this->iterator) {
                $from = $this->iterator->current();
            } else {
                $from = config('rollcall.messaging.sms_providers.'.$region_code.'.from');

                if (!$from) {
                    $from = config('rollcall.messaging.sms_providers.default.from');
                }

                if (is_array($from)) {
                    $from = $from[0];
                }
            }
        }

        $driver = $this->getDriver($region_code);

        $additional_params['keyword'] = config('sms.'.$driver.'.keyword');
        $additional_params['from'] = $from;
        $additional_params['msg'] =  $msg;

        $to = $to->getNormalizedNumber();

        $view = isset($this->view) ? $this->view : $msg;

        dispatch((new SendSMS($view, $additional_params, $driver, $from, $to))/*->onQueue('sms')*/);
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

    public function sendResponseReceivedSMS($to, $from = null, $rollcall_id = 0) {
        Log::info('Sending "response received" sms to: ' . $to->getNormalizedNumber());

        $params = [
            'sms_type' => 'response_received',
            'rollcall_id' => $rollcall_id
        ];

        $this->setView('sms.response_received');
        $this->send($to, null, $params, null, $from);
    }
}
