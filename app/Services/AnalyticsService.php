<?php

namespace TenFour\Services;

use Segment;
use Exception;

class AnalyticsService
{
    public function __construct()
    {
        if (config('segment.key')) {
            Segment::init(config('segment.key'));

            try {
                $this->user = app('Dingo\Api\Auth\Auth')->user();
            } catch (\Throwable $e) {
                \Log::warning($e);
            }

            if (isset($this->user)) {
                $this->identify($this->user);
            }
        }
    }

    public function identify($user)
    {
        if (config('segment.key')) {
            Segment::identify(array(
                "userId" => $user->id,
                "traits" => array(
                  "name" => $user->name,
                  "email" => (method_exists($user,'email')?$user->email():'')
                )
            ));
        }
    }

    public function track($event, $properties)
    {
        if (config('segment.key')) {
            try {
                Segment::track(array(
                    'userId'      => (is_object($this->user)?$this->user->id:'anonymous'.session_id()),
                    'event'       => $event,
                    'properties'  => $properties
                ));
            } catch (Exception $e) {
                \Log::warning($e);
            }
        }
    }
}
