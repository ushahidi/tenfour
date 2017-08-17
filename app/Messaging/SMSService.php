<?php

namespace RollCall\Messaging;

use Log;
use SMS;
use App;
use RollCall\Contracts\Messaging\MessageService;
use SimpleSoftwareIO\SMS\SMSNotSentException;
use GrahamCampbell\Throttle\Facades\Throttle;

class SMSService implements MessageService
{
    public function setView($view)
    {
        $this->view = $view;
    }

    public function getKeyword($to)
    {
        $region_code = $to->getRegionCode();
        $driver = config('rollcall.messaging.sms_providers.'.$region_code.'.driver');

        if (!$driver) {
            $driver = config('rollcall.messaging.sms_providers.default.driver');
        }

        return config('sms.'.$driver.'.keyword');
    }

    public function send($to, $msg = '', $additional_params = [], $subject = null)
    {
        $region_code = $to->getRegionCode();

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

            $throttler = ThrottleAdapter::get($from, $to, $messages_per_second);

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

    public function sendResponseReceivedSMS($to) {
        Log::info('Sending "response received" sms to: ' . $to->getNormalizedNumber());

        $this->setView('sms.response_received');
        $this->send($to);
    }
}
