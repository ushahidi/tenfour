<?php

namespace TenFour\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Disable pulling SMS, this doesn't work well with multiple providers
        // $schedule->command(\TenFour\Console\Commands\ReceiveSMS::class)
        //          ->everyMinute();
        $schedule->job(new \TenFour\Jobs\FixOrgOwners)->hourly();
        $schedule->job(new \TenFour\Jobs\NotifyFreePromoEnding)->daily();
        $schedule->job(new \TenFour\Jobs\SyncSubscriptions)->hourly();
        $schedule->job(new \TenFour\Jobs\CheckQuotas)->hourly();
        $schedule->job(new \TenFour\Jobs\CheckLowCredits)->hourly();
        $schedule->job(new \TenFour\Jobs\LDAPSyncAll)->daily();
        $schedule->job(new \TenFour\Jobs\ExpireUnverifiedAddresses)->daily();
        $schedule->job(new \TenFour\Jobs\SendScheduledCheckin)->everyMinute();
        $schedule->job(new \TenFour\Jobs\CreateScheduledCheckIns)->everyMinute();
    }
}
