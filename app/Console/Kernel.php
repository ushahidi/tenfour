<?php

namespace TenFour\Console;

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
        \TenFour\Console\Commands\Inspire::class,
        \TenFour\Console\Commands\ReceiveSMS::class,
        \TenFour\Console\Commands\ImportContacts::class,
        \TenFour\Console\Commands\ResendCheckIn::class,
        \TenFour\Console\Commands\OrgReset::class,
        \TenFour\Console\Commands\OrgPassword::class,
        \TenFour\Console\Commands\OrgDelete::class,
        \TenFour\Console\Commands\ExpireCredits::class,
        \TenFour\Console\Commands\NotifyFreePromoEnding::class,
        \TenFour\Console\Commands\OrgAdmin::class,
        \TenFour\Console\Commands\SendWelcomeMail::class,
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
        // $schedule->command(\TenFour\Console\Commands\ReceiveSMS::class)
        //          ->everyMinute();

        $schedule->command(\TenFour\Console\Commands\ExpireCredits::class)->daily();
        $schedule->command(\TenFour\Console\Commands\NotifyFreePromoEnding::class)->daily();
        $schedule->command(\TenFour\Console\Commands\SendWelcomeMail::class)->daily();
    }
}
