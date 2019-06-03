<?php

namespace TenFour\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use TenFour\Http\Requests\Organization\GetOrganizationRequest;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

class ApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('org_contact', 'TenFour\Validators\OrgMemberValidator@validateContact');
        Validator::extend('input_image', 'TenFour\Validators\ImageValidator@validateProfilePictureUpload');
        Validator::extend('phone_number', 'TenFour\Validators\PhoneNumberValidator@validatePhoneNumber');
        Validator::extend('reserved_word', 'TenFour\Validators\ReservedWordValidator@validateName');

        $this->app->resolving(function ($object, $app) {
            if (is_object($object) && in_array('TenFour\Traits\UserAccess', $this->getTraits(get_class($object)))) {
                $object->setAuth($app['Dingo\Api\Auth\Auth']);
            }
        });

        $exception = app('api.exception');

        $exception->register(function(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        });

        $exception->register(function(\Illuminate\Validation\ValidationException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        });

        Queue::failing(function (JobFailed $event) {
            app('sentry')->captureMessage('A job failed', $event);
        });
    }

    public function register()
    {
        $this->app->bind('TenFour\Contracts\Repositories\OrganizationRepository',
                         'TenFour\Repositories\EloquentOrganizationRepository');

        $this->app->bind('TenFour\Contracts\Repositories\ContactRepository',
                         'TenFour\Repositories\EloquentContactRepository');

        $this->app->bind('TenFour\Contracts\Repositories\CheckInRepository',
                         'TenFour\Repositories\EloquentCheckInRepository');

        $this->app->bind('TenFour\Contracts\Repositories\ScheduledCheckinRepository',
                         'TenFour\Repositories\EloquentScheduledCheckinRepository');

        $this->app->bind('TenFour\Contracts\Repositories\ReplyRepository',
                         'TenFour\Repositories\EloquentReplyRepository');

        $this->app->bind('TenFour\Contracts\Repositories\PersonRepository',
                         'TenFour\Repositories\EloquentPersonRepository');

        $this->app->bind('TenFour\Contracts\Repositories\ContactFilesRepository',
                         'TenFour\Repositories\EloquentContactFilesRepository');

        $this->app->bind('TenFour\Contracts\Repositories\UnverifiedAddressRepository',
                         'TenFour\Repositories\EloquentUnverifiedAddressRepository');

        $this->app->bind('TenFour\Contracts\Repositories\GroupRepository',
                         'TenFour\Repositories\EloquentGroupRepository');

        $this->app->bind('TenFour\Contracts\Messaging\MessageServiceFactory',
                         'TenFour\Messaging\MessageServiceFactory');

        $this->app->when('TenFour\Messaging\Validators\NexmoMessageValidator')
            ->needs('$secret')
            ->give(config('tenfour.messaging.nexmo_security_secret'));

        $this->app->bind('TenFour\Contracts\Contacts\CsvImporter',
                         'TenFour\Contacts\CsvImporter');

        $this->app->bind('TenFour\Contracts\Contacts\CsvReader',
                         'TenFour\Contacts\CsvReader');

        $this->app->bind('TenFour\Contracts\Contacts\CsvTransformer',
                         'TenFour\Contacts\CsvTransformer');

        $this->app->bind('TenFour\Contracts\Repositories\SubscriptionRepository',
                         'TenFour\Repositories\EloquentSubscriptionRepository');

        $this->app->bind('TenFour\Contracts\Repositories\AlertSourceRepository',
                         'TenFour\Repositories\EloquentAlertSourceRepository');

        $this->app->bind('TenFour\Contracts\Services\PaymentService',
                         'TenFour\Services\Payments\ChargeBeePaymentService');

        $this->app->bind('TenFour\Contracts\Repositories\NotificationRepository',
                         'TenFour\Repositories\EloquentNotificationRepository');

        $this->app->when('TenFour\Messaging\PhoneNumberAdapter')
            ->needs('libphonenumber\PhoneNumberUtil')
            ->give(function () {
                return PhoneNumberUtil::getInstance();
            });

        $this->app->when('TenFour\Messaging\PhoneNumberAdapter')
            ->needs('libphonenumber\PhoneNumberToCarrierMapper')
            ->give(function () {
                return PhoneNumberToCarrierMapper::getInstance();
            });
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
