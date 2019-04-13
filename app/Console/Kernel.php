<?php

namespace TenFour\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use TenFour\Models\ScheduledCheckIn;
use Cron\CronExpression;
use Illuminate\Support\Facades\Log;
use TenFour\Jobs\SendCheckIn;

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
        // Get all scheduled_check_in entries from the database that are not already scheduled
        $scheduledCheckIns = ScheduledCheckIn::active();
        Log::emergency('The system is up!');
        // Go through each task to dynamically set them up.
        // hourly, daily, weekly, biweekly, monthly
        foreach ($scheduledCheckIns as $scheduledCheckIn) {
            $frequency = $scheduledCheckIn->frequency;
            // Use the scheduler to add the task at its desired frequency
            $schedule->call(function() use ($scheduledCheckIn){
                // dispatch a check-in now
                $scheduledCheckIn->setAsScheduled();
                SendCheckIn::dispatch($scheduledCheckIn->check_ins->toArray()); // ? nope , need to call the job itself and send it
            })
            ->between($scheduledCheckIn->starts_at, $scheduledCheckIn->expires_at)
            ->cron(CronExpression::factory("@$frequency")->getExpression());
        }
    }
}
