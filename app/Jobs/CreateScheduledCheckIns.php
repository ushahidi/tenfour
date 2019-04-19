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
    public function handle($check_in_repo, $scheduledCheckIns = [])
    {
        foreach ($scheduledCheckIns as $scheduledCheckIn) {
            $scheduledCheckIn->scheduled = true;
            //stop other jobs from getting this scheduled check-in and processing it
            $scheduledCheckIn->save();
            // QUESTION: if starts_at is lower than NOW for whatever reason, shouuld we do NOW instead?
            $cron = CronExpression::factory("@$scheduledCheckIn->frequency");
            $nextRunDate = $cron->getNextRunDate($scheduledCheckIn->starts_at)->format('Y-m-d H:i:s');
            while (new \DateTime($scheduledCheckIn->expires_at) >= new \DateTime($nextRunDate)) {
                $nextRunDate = $this->createCheckIns($scheduledCheckIn, $nextRunDate, $check_in_repo, $cron);
            }
        }
    }
    public function createCheckIns($scheduledCheckIn, $nextRunDate, $check_in_repo, $cron) {
        $checkInTemplate = $this->fromCheckInTemplate($scheduledCheckIn->check_ins->toArray(), $nextRunDate);
        $check_in_repo->create($checkInTemplate);
        $nextRunDate = $cron->getNextRunDate($nextRunDate)->format('Y-m-d H:i:s');
        return $nextRunDate;
    }
    public function fromCheckInTemplate($checkInTemplate, $nextRunDate){
        $checkInTemplate['send_at'] = $nextRunDate;
        unset($checkInTemplate['id']);
        unset($checkInTemplate['created_at']);
        unset($checkInTemplate['updated_at']);
        unset($checkInTemplate['deleted_at']);
        $checkInTemplate['recipients'] = [];
        return $checkInTemplate;
    }
}
