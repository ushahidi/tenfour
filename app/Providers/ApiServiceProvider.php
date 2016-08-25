<?php

namespace RollCall\Providers;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('RollCall\Contracts\Repositories\UserRepository',
                         'RollCall\Repositories\EloquentUserRepository');
        $this->app->bind('RollCall\Contracts\Repositories\OrganizationRepository',
                         'RollCall\Repositories\EloquentOrganizationRepository');
        $this->app->bind('RollCall\Contracts\Repositories\ContactRepository',
                         'RollCall\Repositories\EloquentContactRepository');
        $this->app->bind('RollCall\Contracts\Repositories\RollcallRepository',
                         'RollCall\Repositories\EloquentRollcallRepository');
    }
}
