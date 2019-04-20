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
     * Receives frequency and start date and time
     * Special scenario for biweekly: will return a weekly entry and needs to be skipped manually
     * through CronFactory's nextRunDate nth option
     * @param [type] $frequency
     * @param \DateTimeInterface $startDate
     * @return string
     */
    public function cronFormat($frequency, \DateTimeInterface $startDate){
        switch ($frequency) {
            case 'hourly': 
                /**
                 * At minute 2.
                 * 02 * * * *
                 */
                return $startDate->format('i') . ' */1 * * *';
            case 'daily': 
                /**
                 * 02 23 * * *
                 * At 23:02
                 */
                return $startDate->format('i') . ' ' . $startDate->format('H') . ' * * *';
            case 'weekly': 
                /**
                 * At 23:02 on Wednesday.
                 * 02 23 * * 3
                 */
                return $startDate->format('i') . ' ' . $startDate->format('H') . ' * * ' . $startDate->format('N');
            // Special case: this returns "Weekly" and has to be handled with getNextRunDate to skip 1 week/run
            case 'biweekly': 
                /**
                 * At 23:02 on Wednesday.
                 * 02 23 * * 3
                 */
                return $startDate->format('i') . ' ' . $startDate->format('H') . ' * * ' . $startDate->format('N');
            case 'monthly': 
                /**
                 * At 21:20 on day-of-month 1.
                 * 20 21 1 * *
                 */
                return $startDate->format('i') . ' ' . $startDate->format('H') . ' ' .$startDate->format('d'). ' * *';
        }
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CheckInRepository $checkInRepo, $scheduledCheckIns = null)
    {
        if (!$scheduledCheckIns) {
            // Get all scheduled_check_in entries from the database that are not already processed
            $scheduledCheckInClass = new ScheduledCheckIn();
            $scheduledCheckIns = $scheduledCheckInClass->findActive();
        }
        foreach ($scheduledCheckIns as $scheduledCheckIn) {
            $scheduledCheckIn->scheduled = true;
            //stop other jobs from getting this scheduled check-in and processing it
            $scheduledCheckIn->save();
            $cronExpression = $this->cronFormat($scheduledCheckIn->frequency, new \DateTime($scheduledCheckIn->starts_at));
            $cron = CronExpression::factory($cronExpression);
            if ($scheduledCheckIn->frequency === 'biweekly') {
                $nextRunDate = $cron->getNextRunDate($scheduledCheckIn->starts_at, 0, true)->format('Y-m-d H:i:s');
            } else {
                $nextRunDate = $cron->getNextRunDate($scheduledCheckIn->starts_at, 0, true)->format('Y-m-d H:i:s');
            }
            while (new \DateTime($scheduledCheckIn->expires_at) >= new \DateTime($nextRunDate)) {
                $nextRunDate = $this->createCheckIns($scheduledCheckIn, $nextRunDate, $checkInRepo, $cron);
            }
        }
    }
    public function createCheckIns($scheduledCheckIn, $nextRunDate, $checkInRepo, $cron) {
        $checkInTemplate = $this->fromCheckInTemplate($scheduledCheckIn->check_ins->toArray(), $nextRunDate);
        $checkInRepo->create($checkInTemplate);

        if ($scheduledCheckIn->frequency === 'monthly') {
            $nextRunDate = $this->getMonthlyDates($nextRunDate, $cron, $scheduledCheckIn);
        } else if ($scheduledCheckIn->frequency === 'biweekly') {
            $nextRunDate = $cron->getNextRunDate($nextRunDate, 1)->format('Y-m-d H:i:s');
        } else {
            $nextRunDate = $cron->getNextRunDate($nextRunDate)->format('Y-m-d H:i:s');
        }
        
        return $nextRunDate;
    }
    /**
     * Fixes dates using months for months when the run date is higher than the last day of the next month. 
     * Example: running on the 31st of every month won't work in Febraury and needs a special fix
     * @return void
     */
    private function getMonthlyDates($nextRunDate, $cron, $scheduledCheckIn) {
        //example $nextRunDate 2190-01-31 21:19:00 
        $previousMonthDate = new \DateTime($nextRunDate);
        // will return  2190-03-31 21:19:00 
        $calculatedNextRunDate =  $cron->getNextRunDate($nextRunDate);
        if ( $calculatedNextRunDate->format('n') - $previousMonthDate->format('n') > 1) {
            $previousMonthDate->modify("last day of next month");
            $cronExpression = $this->cronFormat($scheduledCheckIn->frequency, $previousMonthDate);
            $cron = CronExpression::factory($cronExpression);
            return $cron->getNextRunDate($previousMonthDate->format('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
        } else {
            return $cron->getNextRunDate($nextRunDate)->format('Y-m-d H:i:s');
        }
    }
    public function fromCheckInTemplate($checkInTemplate, $nextRunDate){
        $checkInTemplate['send_at'] = $nextRunDate;
        unset($checkInTemplate['id']);
        unset($checkInTemplate['created_at']);
        unset($checkInTemplate['updated_at']);
        unset($checkInTemplate['deleted_at']);
        unset($checkInTemplate['template']);
        $checkInTemplate['recipients'] = [];
        return $checkInTemplate;
    }
}
