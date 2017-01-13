<?php

namespace RollCall\Messaging;

use SimpleSoftwareIO\SMS\SMS as BaseSMS;

class SMS extends BaseSMS
{
    /**
     * Changes the set SMS driver.
     *
     * @param $driver
     */
    public function driver($driver)
    {
        $this->container['sms.sender'] = $this->container->share(function ($app) use ($driver) {
            return (new DriverManager($app))->driver($driver);
        });

        $this->driver = $this->container['sms.sender'];
    }

}
