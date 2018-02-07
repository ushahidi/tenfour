<?php

namespace TenFour\Messaging;

use Log;
use SMS;
use App;
use TenFour\Contracts\Messaging\MessageService;
use TenFour\Jobs\SendSMS;

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

        $driver = config('tenfour.messaging.sms_providers.'.$region_code.'.driver');

        if (!$driver) {
            $driver = config('tenfour.messaging.sms_providers.default.driver');
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
                $from = config('tenfour.messaging.sms_providers.'.$region_code.'.from');

                if (!$from) {
                    $from = config('tenfour.messaging.sms_providers.default.from');
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

        dispatch((new SendSMS($view, $additional_params, $driver, $from, $to, $region_code))/*->onQueue('sms')*/);
    }

    protected function logSMS($to, $from, $driver, $check_in_id = 0, $type = 'other', $message = '')
    {
        $sms = new OutgoingSMS;
        $sms->to = $to;
        $sms->from = $from;
        $sms->driver = $driver;
        $sms->check_in_id = $check_in_id;
        $sms->type = $type;
        $sms->message = $message;
        $sms->save();
    }

    public function getMessages(Array $options = [])
    {
        // Get configured drivers
        $sms_providers = config('tenfour.messaging.sms_providers');

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

    public function sendResponseReceivedSMS($to, $from = null, $check_in_id = 0) {
        Log::info('Sending "response received" sms to: ' . $to->getNormalizedNumber());

        $params = [
            'sms_type' => 'response_received',
            'check_in_id' => $check_in_id
        ];

        $this->setView('sms.response_received');
        $this->send($to, null, $params, null, $from);
    }
}
