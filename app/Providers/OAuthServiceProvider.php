<?php

namespace TenFour\Providers;

use Dingo\Api\Auth\Auth;
use Dingo\Api\Auth\Provider\OAuth2;
use Illuminate\Support\ServiceProvider;
use TenFour\Contracts\Repositories\PersonRepository;
use DB;

class OAuthServiceProvider extends ServiceProvider
{
    public function boot(PersonRepository $people)
    {
        $this->app[Auth::class]->extend('oauth', function ($app) use ($people) {
            $provider = new OAuth2($app['oauth2-server.authorizer']->getChecker());

            $provider->setUserResolver(function ($id) use ($people) {
                return $people->findObject($id);
            });

            $provider->setClientResolver(function ($id) {
                $client = DB::select('select * from oauth_clients where id = ?', [$id])[0];
                return $client;
            });

            return $provider;
        });
    }

    public function register()
    {
        //
    }
}
