<?php
namespace TenFour\Messaging;

use TenFour\Messaging\Drivers\AfricasTalking;
use TenFour\Messaging\Drivers\BulkSMS;

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

     /**
     * Create an instance of BulkSMS driver
     *
     * @return BulkSMS
     */
    public function createBulkSMSDriver()
    {
        $config = $this->app['config']->get('sms.bulksms', []);

        return new BulkSMS(
            new Client,
            $config['token_id'],
            $config['token_secret']
        );
    }
}
