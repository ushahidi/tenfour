<?php

use Codeception\Util\Stub;
use RollCall\Messaging\ThrottleAdapter;
use GrahamCampbell\Throttle\Facades\Throttle;

class ThrottleAdapterCest
{
    public function getThrottler()
    {
        // We want to get back a throttler that limits messages sent out per second.
        $messages_per_second = 1;

        // The default cache store expects the time to be in minutes.
        $time = 1/60;

        Throttle::shouldReceive('get')
            ->times(1)
            ->with([
                'ip'    => 'from',
                'route' => 'to',
            ], $messages_per_second, $time);

        ThrottleAdapter::get('from', 'to', $messages_per_second);
    }
}
