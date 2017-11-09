<?php

namespace RollCall\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Log;
use SMS;
use App;
use SimpleSoftwareIO\SMS\SMSNotSentException;
use RollCall\Models\SMS as OutgoingSMS;
use Stiphle;
use Illuminate\Support\Facades\Redis;

class SendSMS implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $from;
    protected $driver;
    protected $view;
    protected $additional_params;
    protected $to;

    private $throttle;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($view, $additional_params, $driver, $from, $to)
    {
        $this->view = $view;
        $this->additional_params = $additional_params;
        $this->driver = $driver;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $throttle = new Stiphle\Throttle\LeakyBucket;
        $throttle->setStorage(new Stiphle\Storage\Redis(Redis::connection()));

        if ($this->from) {
            SMS::alwaysFrom($this->from);
        }

        // Set SMS driver for the region code
        SMS::driver($this->driver);

        $messages_per_second = config('sms.'.$this->driver.'.messages_per_second');

        if ($messages_per_second) {
            $throttled = $throttle->throttle($this->from, $messages_per_second, 1000);
        } else {
            $throttled = 0;
        }

        Log::debug('Attempting to send an SMS from="'.$this->from.'" to="'.$this->to.'" with driver="'.$this->driver.'" (throttle='.$throttled.'ms)');

        $to = $this->to;

        try {
            SMS::send($this->view, $this->additional_params, function($sms) use ($messages_per_second, $throttle) {
                $sms->to($this->to);

                if ($messages_per_second) {
                    $throttled = $throttle->throttle($this->from, $messages_per_second, 1000);
                } else {
                    $throttled = 0;
                }

                Log::debug('Successfully sent an SMS from="'.$this->from.'" to="'.$this->to.'" with driver="'.$this->driver.'" (throttle='.$throttled.'ms)');
            });

            $this->logSMS(
                $this->to,
                $this->from,
                $this->driver,
                isset($this->additional_params['rollcall_id'])?$this->additional_params['rollcall_id']:0,
                isset($this->additional_params['sms_type'])?$this->additional_params['sms_type']:'other',
                view($this->view, $this->additional_params));
        } catch (SMSNotSentException $e) {
            app('sentry')->captureException($e);
            Log::warning($e);
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
}
