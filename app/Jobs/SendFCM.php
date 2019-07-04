<?php

namespace TenFour\Jobs;

use TenFour\Models\DeviceToken;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

use DB;
use Log;
use FCM;
use Exception;

class SendFCM implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $view;
    protected $params;
    protected $tokens;

    public function failed(Exception $exception)
    {
        Log::warning($exception);
        // Statsd::increment('worker.sendsms.failed');
        app('sentry')->captureException($exception);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($view, $params, $tokens)
    {
        $this->view = $view;
        $this->params = $params;
        $this->tokens = $tokens;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('[SendFCM] Sending to ' . count($this->tokens) . ' tokens.');

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder(isset($this->params['title'])?$this->params['title']:null);

        if (isset($this->view)) {
            $notificationBuilder->setBody(view($this->view, $this->params))->setSound('default');
        } else if (isset($this->params['msg'])) {
            $notificationBuilder->setBody($this->params['msg'])->setSound('default');                    
        }

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($this->params);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $downstreamResponse = FCM::sendTo($this->tokens, $option, $notification, $data);

        Log::info('[SendFCM] Success count='.$downstreamResponse->numberSuccess());
        Log::info('[SendFCM] Failure count='.$downstreamResponse->numberFailure());
        Log::info('[SendFCM] Mod count='.$downstreamResponse->numberModification());

        $this->deleteTokens($downstreamResponse->tokensToDelete());
        $this->modifyTokens($downstreamResponse->tokensToModify());

        if (config('app.env') !== 'local') {
            $this->deleteTokens(array_keys($downstreamResponse->tokensWithError()));
        }
    }

    private function deleteTokens($tokens) {
        Log::info('Deleting tokens: ' . json_encode($tokens));
        DeviceToken::whereIn('token', $tokens)->delete();
    }

    private function modifyTokens($tokens) {
        Log::info('Modifying tokens: ' . json_encode($tokens));
        foreach ($tokens as $key => $value) {
            DeviceToken::where('token', $key)->update(['token' => $value]);
        }
    }
}
