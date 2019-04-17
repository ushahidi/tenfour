<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Models\ScheduledCheckIn;
use Cron\CronExpression;
use Illuminate\Support\Facades\Log;
use TenFour\Models\CheckIn;
use Illuminate\Support\Facades\Schema;

class CreateScheduledCheckIns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CheckInRepository $check_in_repo)
    {
        if (Schema::hasTable('scheduled_check_in')) {
            // Get all scheduled_check_in entries from the database that are not already processed
            $scheduledCheckIns = ScheduledCheckIn::active();
            foreach ($scheduledCheckIns as $scheduledCheckIn) {
                $scheduledCheckIn->scheduled = true;
                 //stop other jobs from getting this scheduled check-in and processing it
                $scheduledCheckIn->save();
                // get template of a checkin to create others
                $checkInTemplate = $scheduledCheckIn->check_ins->toArray();
                $nextRunDate = $scheduledCheckIn->starts_at;
                while (new \DateTime($scheduledCheckIn->expires_at) >= new \DateTime($nextRunDate)) {
                    $checkInTemplate = $scheduledCheckIn->check_ins->toArray();
                    $cron = CronExpression::factory("@$scheduledCheckIn->frequency");
                    $checkInTemplate['send_at'] = $nextRunDate;
                    unset($checkInTemplate['id']);
                    unset($checkInTemplate['created_at']);
                    unset($checkInTemplate['updated_at']);
                    $checkInTemplate['recipients'] = [];
                    unset($checkInTemplate['deleted_at']);
                    $check_in_repo->create($checkInTemplate);
                    $nextRunDate = $cron->getNextRunDate($nextRunDate)->format('Y-m-d H:i:s');
                }
            }
        }
    }
}
