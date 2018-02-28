<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Exception;
use Log;
use Mail;
use Illuminate\Contracts\Mail\Mailable;
use TenFour\Models\Mail as OutgoingMail;
use Statsd;

class SendMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $mailable;
    protected $view;
    protected $additional_params;
    protected $subject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $mailable, $view, $additional_params = [])
    {
        $this->to = $to;
        $this->mailable = $mailable;
        $this->view = $view;
        $this->additional_params = $additional_params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->mailable instanceof Mailable) {
            Mail::to($this->to)->send($this->mailable);

            $from = ($this->mailable->from && count($this->mailable->from))
                ? $this->mailable->from[0]['address']
                : config('mail.from')['address'];

            $this->logMail(
                $this->to,
                $from,
                $this->mailable->subject,
                isset($this->additional_params['type'])?$this->additional_params['type']:'other',
                isset($this->additional_params['check_in_id'])?$this->additional_params['check_in_id']:0
            );
        } else {
            throw new Exception('Refusing to queue a non-mailable mail');
        }
    }

    public function failed(Exception $exception)
    {
        Log::warning($exception);
        Statsd::increment('worker.sendmail.failed');
        app('sentry')->captureException($exception);
    }

    protected function logMail($to, $from, $subject, $type, $check_in_id)
    {
        $mail = new OutgoingMail;
        $mail->to = $to;
        $mail->from = $from;
        $mail->subject = $subject;
        $mail->check_in_id = $check_in_id;
        $mail->type = $type;
        $mail->save();

        Statsd::increment('message.mail.sent');
    }
}
