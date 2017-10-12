<?php

namespace RollCall\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \RollCall\Console\Commands\Inspire::class,
        \RollCall\Console\Commands\ReceiveSMS::class,
        \RollCall\Console\Commands\ImportContacts::class,
        \RollCall\Console\Commands\ResendRollCall::class,
        \RollCall\Console\Commands\OrgReset::class,
        \RollCall\Console\Commands\OrgPassword::class,
        \RollCall\Console\Commands\OrgDelete::class,
        \RollCall\Console\Commands\ExpireCredits::class,
        \RollCall\Console\Commands\NotifyFreePromoEnding::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Disable pulling SMS, this doesn't work well with multiple providers
        // $schedule->command(\RollCall\Console\Commands\ReceiveSMS::class)
        //          ->everyMinute();

        $schedule->command(\RollCall\Console\Commands\ExpireCredits::class)->daily();
        $schedule->command(\RollCall\Console\Commands\NotifyFreePromoEnding::class)->daily();
    }
}
