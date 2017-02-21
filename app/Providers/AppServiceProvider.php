<?php

namespace RollCall\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Clockwork\Support\Laravel\ClockworkMiddleware;
use Clockwork\Support\Laravel\ClockworkServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Kernel $kernel)
    {
        $clockwork = $this->app['config']->get('app.clockwork', false);
        if ($clockwork) {
            $kernel->prependMiddleware(ClockworkMiddleware::class);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $clockwork = $this->app['config']->get('app.clockwork', false);
        \Log::info('here');
        \Log::info($clockwork);
        if ($clockwork) {
             $this->app->register(ClockworkServiceProvider::class);
        }
    }
}
