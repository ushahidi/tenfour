<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Log;
use SMS;
use App;
use Statsd;
use Exception;
use SimpleSoftwareIO\SMS\SMSNotSentException;
use TenFour\Models\SMS as OutgoingSMS;

class SendSMS implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $from;
    protected $driver;
    protected $view;
    protected $additional_params;
    protected $to;
    protected $region_code;

    public function failed(Exception $exception)
    {
        Log::warning($exception);
        Statsd::increment('worker.sendsms.failed');
        app('sentry')->captureException($exception);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($view, $additional_params, $driver, $from, $to, $region_code)
    {
        $this->view = $view;
        $this->additional_params = $additional_params;
        $this->driver = $driver;
        $this->from = $from;
        $this->to = $to;
        $this->region_code = $region_code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->from) {
            SMS::alwaysFrom($this->from);
        }

        // Set SMS driver for the region code
        SMS::driver($this->driver);

        // $messages_per_second = config('sms.'.$this->driver.'.messages_per_second');

        Log::debug('Attempting to send an SMS from="'.$this->from.'" to="'.$this->to.'" with driver="'.$this->driver.'"');

        $to = $this->to;

        try {
            SMS::send($this->view, $this->additional_params, function($sms) {
                $sms->to($this->to);
            });

            $this->logSMS(
                $this->to,
                $this->from,
                $this->driver,
                isset($this->additional_params['check_in_id'])?$this->additional_params['check_in_id']:0,
                isset($this->additional_params['sms_type'])?$this->additional_params['sms_type']:'other',
                view($this->view, $this->additional_params));
        } catch (SMSNotSentException $e) {
            app('sentry')->captureException($e);
            Log::warning($e);
            Statsd::increment('message.sms.retry');
            $this->release(3);
        }
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

        Statsd::increment('message.sms.sent');
        Statsd::increment('message.sms.sent.' . $this->region_code);

    }
}
