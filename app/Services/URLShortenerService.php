<?php

namespace RollCall\Services;

use UrlShortener;

class URLShortenerService
{
    public function __construct()
    {
    }

    public function shorten($url)
    {
        if (!config('urlshortener.driver')) {
            return $url;
        }

        try {
            $url = UrlShortener::shorten($url);
        } catch (\Waavi\UrlShortener\Exceptions\InvalidResponseException $e) {
            \Log::error($e);
        }

        return $url;
    }
}
