<?php

namespace RollCall\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('org_contact', 'RollCall\Validators\OrgMemberValidator@validateContact');
    }

    public function register()
    {
        $this->app->bind('RollCall\Contracts\Repositories\UserRepository',
                         'RollCall\Repositories\EloquentUserRepository');
        $this->app->bind('RollCall\Contracts\Repositories\OrganizationRepository',
                         'RollCall\Repositories\EloquentOrganizationRepository');
        $this->app->bind('RollCall\Contracts\Repositories\ContactRepository',
                         'RollCall\Repositories\EloquentContactRepository');
        $this->app->bind('RollCall\Contracts\Repositories\RollCallRepository',
                         'RollCall\Repositories\EloquentRollCallRepository');
        $this->app->bind('RollCall\Contracts\Repositories\ReplyRepository',
                         'RollCall\Repositories\EloquentReplyRepository');
    }
}
