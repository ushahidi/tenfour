<?php

namespace TenFour\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        'Barryvdh\Cors\HandleCors',
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \TenFour\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ],
        'api' => [
            'throttle:60,1',
        ],
    ];
        /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                          => \TenFour\Http\Middleware\Authenticate::class,
        'auth.basic'                    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.basic.chargebee-webhook'  => \TenFour\Http\Middleware\ChargeBeeWebhookAuthentication::class,
        'client_credentials'            => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
        'csrf'                          => \TenFour\Http\Middleware\VerifyCsrfToken::class,
        'guest'                         => \TenFour\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'                      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
