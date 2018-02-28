<?php

namespace TenFour\Messaging;

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
        $this->container['sms.sender'] = (new DriverManager($this->container))->driver($driver);

        $this->driver = $this->container['sms.sender'];
    }

}
