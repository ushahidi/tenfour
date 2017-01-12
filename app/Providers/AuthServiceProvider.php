<?php

namespace RollCall\Providers;

use Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use RollCall\Auth\EloquentUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'RollCall\Model' => 'RollCall\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('rollcall', function ($app, array $config) {
            return new EloquentUserProvider($this->app['hash'], $config['model']);
        });
    }
}
