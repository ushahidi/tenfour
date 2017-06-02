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
        Validator::extend('input_image', 'RollCall\Validators\ImageValidator@validateProfilePictureUpload');
        Validator::extend('phone_number', 'RollCall\Validators\PhoneNumberValidator@validatePhoneNumber');
        Validator::extend('reserved_word', 'RollCall\Validators\ReservedWordValidator@validateName');

        $this->app->resolving(function ($object, $app) {
            if (is_object($object) && in_array('Rollcall\Traits\UserAccess', $this->getTraits(get_class($object)))) {
                $object->setAuth($app['Dingo\Api\Auth\Auth']);
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

        $this->app->bind('RollCall\Contracts\Repositories\ContactFilesRepository',
                         'RollCall\Repositories\EloquentContactFilesRepository');

        $this->app->bind('RollCall\Contracts\Repositories\UnverifiedAddressRepository',
                         'RollCall\Repositories\EloquentUnverifiedAddressRepository');

        $this->app->bind('RollCall\Contracts\Messaging\MessageServiceFactory',
                         'RollCall\Messaging\MessageServiceFactory');

        $this->app->when('RollCall\Messaging\Validators\NexmoMessageValidator')
            ->needs('$secret')
            ->give(config('rollcall.messaging.nexmo_security_secret'));

        $this->app->bind('RollCall\Contracts\Contacts\CsvImporter',
                         'RollCall\Contacts\CsvImporter');

        $this->app->bind('RollCall\Contracts\Contacts\CsvReader',
                         'RollCall\Contacts\CsvReader');

        $this->app->bind('RollCall\Contracts\Contacts\CsvTransformer',
                         'RollCall\Contacts\CsvTransformer');
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
