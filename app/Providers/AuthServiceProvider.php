<?php

namespace TenFour\Providers;

use Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

use TenFour\Auth\EloquentUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'TenFour\Model' => 'TenFour\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('tenfour', function ($app, array $config) {
            return new EloquentUserProvider($this->app['hash'], $config['model']);
        });

        Passport::tokensCan([
            'user' => 'User level access',
        ]);

        Passport::routes();

    }
}
