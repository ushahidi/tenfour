<?php

namespace RollCall\Messaging;

use GrahamCampbell\Throttle\Facades\Throttle;

class ThrottleAdapter
{
    /**
     * Returns a throttler
     *
     * @param string $from
     * @param string $to
     * @param string $messages_per_second
     *
     * @return \GrahamCampbell\Throttle\Throttlers\ThrottlerInterface
     */
    public static function get($from, $to, $messages_per_second)
    {
        $time = 1/60; // Default cache store expects time to be in minutes

        $throttler = Throttle::get([
            'ip'    => $from,
            'route' => $to,
        ], $messages_per_second, $time);

        return $throttler;
    }
}
