<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Models\ScheduledCheckin;
use Cron\CronExpression;
use Illuminate\Support\Facades\Log;
use TenFour\Models\CheckIn;
use Illuminate\Support\Facades\Schema;
use Nexmo\Verify\Check;

class CreateScheduledCheckins implements ShouldQueue
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
            // Get all scheduled_checkin entries from the database that are not already processed
            $scheduledCheckInClass = new ScheduledCheckin();
            $scheduledCheckIns = $scheduledCheckInClass->findActive();
        }
        foreach ($scheduledCheckIns as $scheduledCheckIn) {
            $checkInTemplate = CheckIn::where([
                ['scheduled_checkin_id', '=', $scheduledCheckIn->id],
                ['template', '=', 1]
            ]
            )->get()->toArray();
            $checkInTemplate = array_pop($checkInTemplate);
            $scheduledCheckIn->scheduled = true;
            //stop other jobs from getting this scheduled check-in and processing it
            $scheduledCheckIn->save();
            $cronExpression = $this->cronFormat($scheduledCheckIn->frequency, new \DateTime($scheduledCheckIn->starts_at));
            $cron = CronExpression::factory($cronExpression);
            $nextRunDate = $cron->getNextRunDate($scheduledCheckIn->starts_at, 0, true)->format('Y-m-d H:i:s');
            while (new \DateTime($scheduledCheckIn->expires_at) >= new \DateTime($nextRunDate)) {
                $nextRunDate = $this->createCheckIns($scheduledCheckIn, $nextRunDate, $checkInRepo, $checkInTemplate, $cron);
            }
        }
    }

    public function createCheckIns($scheduledCheckIn, $prevRunDate, $checkInRepo, $checkInTemplate, $cron) {
        $checkIn = $this->fromCheckInTemplate($checkInTemplate, $prevRunDate);
        $checkInRepo->create($checkIn);
        $nextRunDate = null;
        if ($scheduledCheckIn->frequency === 'monthly') {
            $nextRunDate = $this->getMonthlyDates($prevRunDate, $scheduledCheckIn);
        } else if ($scheduledCheckIn->frequency === 'biweekly') {
            $nextRunDate = $cron->getNextRunDate($prevRunDate, 1)->format('Y-m-d H:i:s');
        } else {
            $nextRunDate = $cron->getNextRunDate($prevRunDate)->format('Y-m-d H:i:s');
        }
        return $nextRunDate;
    }
    /**
     * Fixes dates using months for months when the run date is higher than the last day of the next month. 
     * Example: running on the 31st of every month won't work in Febraury and needs a special fix
     * 30 days has September,
     * April, June, and November.
     * When short February's done
     * All the rest have 31...
     * @return void
     */
    public function getMonthlyDates($prevRunDate, $scheduledCheckIn) {
        // the date in the scheduled check in that the user selecteed
        $originalStartDate = new \DateTime($scheduledCheckIn->starts_at);
        // the last date we calculated for a check in to run at, will be used to calculate the next one.
        $prevRunDateTime = new \DateTime($prevRunDate);
        // the  last run date that will be modified to pre-calculate the next one, 
        // used for formatting and internal logic only.
        $nextRunDate = new \DateTime($prevRunDate);
        $nextRunDate->modify('+1 month');
        $dayWasChanged = $originalStartDate->format('d') !== $prevRunDateTime->format('d');
        $nextDateSkippedMonth = ($nextRunDate->format('n') - $prevRunDateTime->format('n')) > 1;
        if ($nextDateSkippedMonth){
            $return = $prevRunDateTime->modify('last day of next month')->format('Y-m-d H:i:s');
        } else if ($dayWasChanged){
            $day = $originalStartDate->format('d');
            $nextMonth = $prevRunDateTime->modify('last day of next month');
            $return = $nextMonth->setDate(
                $nextMonth->format('Y'),
                $nextMonth->format('n'),
                $day
            )->format('Y-m-d H:i:s');
        } else {
            $return = $prevRunDateTime->modify('+1 month')->format('Y-m-d H:i:s');
        }
        return $return;
    }

    public function fromCheckInTemplate($checkInTemplate, $nextRunDate){
        $checkInTemplate['send_at'] = $nextRunDate;
        unset($checkInTemplate['id']);
        unset($checkInTemplate['created_at']);
        unset($checkInTemplate['updated_at']);
        unset($checkInTemplate['deleted_at']);
        unset($checkInTemplate['template']);
        unset($checkInTemplate['users']);
        $checkInTemplate['recipients'] = [];
        return $checkInTemplate;
    }
}
