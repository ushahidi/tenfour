<?php
namespace TenFour\Messaging;

use TenFour\Messaging\Drivers\AfricasTalking;
use GuzzleHttp\Client;

use SimpleSoftwareIO\SMS\DriverManager as BaseDriverManager;

class DriverManager extends BaseDriverManager
{
     /**
     * Create an instance of AfricasTalking driver
     *
     * @return AfricasTalking
     */
    public function createAfricasTalkingDriver()
    {
        $config = $this->app['config']->get('sms.africastalking', []);

        return new AfricasTalking(
            new Client,
            $config['api_key'],
            $config['username']
        );
    }
}
