<?php

namespace RollCall\Messaging;

use Log;
use SMS;
use App;
use RollCall\Contracts\Messaging\MessageService;
use SimpleSoftwareIO\SMS\SMSNotSentException;
use RollCall\Models\SMS as OutgoingSMS;
use Stiphle\Throttle\LeakyBucket;
use Stiphle;
use Illuminate\Support\Facades\Redis;

class SMSService implements MessageService
{

    private $view;
    private $iterator;
    private $throttle;

    public function __construct()
    {
        $this->throttle = new Stiphle\Throttle\LeakyBucket;
        $storage = new Stiphle\Storage\Redis(Redis::connection());
        $this->throttle->setStorage($storage);
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

        $queue = resolve('\Illuminate\Queue\QueueManager');

        $queue->push('RollCall\Messaging\SMSService@handleQueuedMessage', compact('view', 'additional_params', 'driver', 'from', 'to'));
    }

    /**
     * Handles a queue message.
     *
     * @param \Illuminate\Queue\Jobs\Job $job
     * @param array                      $data
     */
    public function handleQueuedMessage($job, $data)
    {
        extract($data);

        // Set 'from' address if configured
        if ($from) {
            SMS::alwaysFrom($from);
        }

        // Set SMS driver for the region code
        SMS::driver($driver);

        $messages_per_second = config('sms.'.$driver.'.messages_per_second');

        if ($messages_per_second) {
            $throttled = $this->throttle->throttle($from, $messages_per_second, 1000);
        }

        Log::debug('Attempting to send an SMS from="'.$from.'" to="'.$to.'" with driver="'.$driver.'"');

        try {
            SMS::send($view, $additional_params, function($sms) use ($to) {
                $sms->to($to);
            });

            $this->logSMS(
                $to,
                $from,
                $driver,
                isset($additional_params['rollcall_id'])?$additional_params['rollcall_id']:0,
                isset($additional_params['sms_type'])?$additional_params['sms_type']:'other',
                view($view, $additional_params));

            $job->delete();
        } catch (SMSNotSentException $e) {
            Log::warning($e);
            // Retry in 3 seconds
            $job->release(3);
        }

    }

    protected function logSMS($to, $from, $driver, $rollcall_id = 0, $type = 'other', $message = '')
    {
        $sms = new OutgoingSMS;
        $sms->to = $to;
        $sms->from = $from;
        $sms->driver = $driver;
        $sms->rollcall_id = $rollcall_id;
        $sms->type = $type;
        $sms->message = $message;
        $sms->save();
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
