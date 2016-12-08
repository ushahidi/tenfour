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
}
