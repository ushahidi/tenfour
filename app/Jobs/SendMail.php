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
    protected $msg;
    protected $view;
    protected $additional_params;
    protected $subject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $msg, $view, $additional_params = [], $subject = null)
    {
        $this->to = $to;
        $this->msg = $msg;
        $this->view = $view;
        $this->additional_params = $additional_params;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->msg instanceof Mailable) {
            Mail::to($this->to)->send($this->msg);

            $this->logMail(
                $this->to,
                $this->msg->from[0]['address'],
                $this->msg->subject,
                isset($this->additional_params['type'])?$this->additional_params['type']:'other',
                isset($this->additional_params['check_in_id'])?$this->additional_params['check_in_id']:0
            );
        } else {
            $params = ['msg' => $this->msg] + $this->additional_params;
            $subject = $this->subject ? $this->subject : str_limit($this->msg, $limit = 50, $end = '...');
            $to = $this->to;
            $subject = $this->subject;

            Mail::queue($this->view, $params, function($message) use ($to, $subject) {
                $message->to($to);
                $message->subject($subject);
            });

            $this->logMail(
                $this->to,
                isset($this->additional_params['from'])?$this->additional_params['from']:config('mail.from.address'),
                $this->subject,
                isset($this->additional_params['type'])?$this->additional_params['type']:'other',
                isset($this->additional_params['check_in_id'])?$this->additional_params['check_in_id']:0
            );
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
