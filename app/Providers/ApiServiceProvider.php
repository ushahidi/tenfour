<?php

namespace RollCall\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use RollCall\Http\Requests\Organization\GetOrganizationRequest;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('org_contact', 'RollCall\Validators\OrgMemberValidator@validateContact');

        $this->app->resolving(function ($object, $app) {
            if (is_object($object) && in_array('Rollcall\Traits\UserAccess', $this->getTraits(get_class($object)))) {
                $object->setAuth($app['Dingo\Api\Auth\Auth']);
                $object->setUsers($app['RollCall\Contracts\Repositories\UserRepository']);
                $object->setPeople($app['RollCall\Contracts\Repositories\PersonRepository']);
            }
        });

        $exception = app('api.exception');


        $exception->register(function(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        });

        $exception->register(function(\League\OAuth2\Server\Exception\InvalidCredentialsException $e) {
            throw new UnauthorizedHttpException('Bearer', $e->getMessage(), $e);
        });
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

        $this->app->bind('RollCall\Contracts\Repositories\PersonRepository',
                         'RollCall\Repositories\EloquentPersonRepository');

        $this->app->bind('RollCall\Contracts\Messaging\MessageServiceFactory',
                         'RollCall\Messaging\MessageServiceFactory');

        $this->app->when('RollCall\Messaging\Validators\NexmoMessageValidator')
            ->needs('$secret')
            ->give(config('rollcall.messaging.nexmo_security_secret'));

    }

    /** Recursively list all traits defined on final class */
    private function getTraits($className)
    {
        $stack = [];

        $reflection = new \ReflectionClass($className);
        if ($traits = $reflection->getTraitNames()) {
            $stack = $traits;
        }

        $parent = get_parent_class($className);
        if ($parent !== false) {
            $stack = array_merge($stack, $this->getTraits($parent));
        }

        return $stack;
    }
}
