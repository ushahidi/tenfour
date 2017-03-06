<?php

namespace RollCall\Messaging;

use SMS;
use RollCall\Contracts\Messaging\MessageService;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use SimpleSoftwareIO\SMS\SMSNotSentException;
use GrahamCampbell\Throttle\Facades\Throttle;

class SMSService implements MessageService
{

    public function setView($view)
    {
        $this->view = $view;
    }

    private function getRegionCode($to)
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

        return $phone_number_util->getRegionCodeForNumber($phone_number_obj);
    }

    public function getKeyword($to)
    {
        $region_code = $this->getRegionCode($to);
        $driver = config('rollcall.messaging.sms_providers.'.$region_code.'.driver');

        if (!$driver) {
            $driver = config('rollcall.messaging.sms_providers.default.driver');
        }

        return config('sms.'.$driver.'.keyword');
    }

    public function send($to, $msg, $additional_params = [], $subject = null)
    {
        $region_code = $this->getRegionCode($to);

        // Get driver
        $driver = config('rollcall.messaging.sms_providers.'.$region_code.'.driver');
        $from = config('rollcall.messaging.sms_providers.'.$region_code.'.from');

        if (! $driver) {
            $driver = config('rollcall.messaging.sms_providers.default.driver');
            $from = config('rollcall.messaging.sms_providers.default.from');
        }

        $additional_params['keyword'] = config('sms.'.$driver.'.keyword');
        $additional_params['from'] = $from;
        $additional_params['msg'] =  $msg;

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
        $time = 1/60; // Pass time in minutes to the cache store

        if ($messages_per_second) {
            $throttler = Throttle::get([
                'ip'    => $from,
                'route' => $to,
            ], $messages_per_second, $time);

            // If we have exceeded the limit return the job back to the queue
            if ($throttler->attempt()) {
                $job->release();
                return;
            }
        }

        try {
            SMS::send($view, $additional_params, function($sms) use ($to) {
                $sms->to($to);
            });
            $job->delete();
        } catch (SMSNotSentException $e) {
            // Retry in 3 seconds
            $job->release(3);
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
