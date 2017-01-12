<?php

namespace RollCall\Messaging;

use SimpleSoftwareIO\SMS\SMSServiceProvider as BaseSMSServiceProvider;

class SMSServiceProvider extends BaseSMSServiceProvider
{
    public function registerSender()
    {
        $this->app['sms.sender'] = $this->app->share(function ($app) {
            return (new DriverManager($app))->driver();
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('sms', function ($app) {

            $this->registerSender();
            $sms = new SMS($app['sms.sender']);
            $this->setSMSDependencies($sms, $app);

            //Set the from setting
            if ($app['config']->has('sms.from')) {
                $sms->alwaysFrom($app['config']['sms']['from']);
            }

            return $sms;
        });
    }

    /**
     * Set a few dependencies on the sms instance.
     *
     * @param SMS $sms
     * @param  $app
     */
    private function setSMSDependencies($sms, $app)
    {
        $sms->setContainer($app);
        $sms->setQueue($app['queue']);
    }
}
