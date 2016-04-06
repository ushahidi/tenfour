<?php

namespace RollCall\Providers;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('RollCall\Contracts\Repositories\UserRepository',
                         'RollCall\Repositories\EloquentUserRepository');
    }
}
