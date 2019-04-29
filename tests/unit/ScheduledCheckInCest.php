<?php

namespace Tests\Unit;

use TestCase;
use TenFour\Jobs\CreateScheduledCheckins;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory;
class ScheduledCheckinCest
{
    /**
     * @return void
     */
    public function testCreateScheduledCheckinsHourly()
    {
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create();
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['message' => 'Scheduled template', 'scheduled_checkin_id'=> $sci1]);
        // default hourly 1 day
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(24);
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsDaily()
    {

        
        // 1 per day, 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            ['frequency' => 'daily']
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(1);
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }
    /**
     * @return void
     */
    public function testCreateScheduledCheckinsWeekly()
    {

        
        // 1 per week, 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            ['frequency' => 'weekly']
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(1);
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsBiWeeklySingleRun()
    {
        // 1 per week, 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            ['frequency' => 'biweekly']
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(1);
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsBiWeeklyFullMonthExpiration()
    {
        $faker = Factory::create();

        
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            [
                'frequency' => 'biweekly',
                'expires_at' => date_format($faker->dateTimeBetween('+1 month', '+1 month'), "Y-m-d H:i:s"),
            ]
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(3); // today, 2 weeks from today, 4 weeks from today
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsMonthlyFullMonthExpiration()
    {
        $faker = Factory::create();
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            [
                'frequency' => 'monthly',
                'expires_at' => date_format($faker->dateTimeBetween('+1 month', '+1 month'), "Y-m-d H:i:s"),
            ]
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(2); // today, 1 month from today
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }
     /**
      * This works as expected as long as the day of the month is <=28. .
      * need to define what monthly even means
     * @return void
     */
    public function testCreateScheduledCheckinsMonthlyCheckLastDaysWork()
    {
        $faker = Factory::create();
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            [
                'frequency' => 'monthly',
                'starts_at' => '2190-01-31 21:19:00',
                'expires_at' => '2190-04-03 21:19:00',
            ]
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(3); // today, 1 month from today
        $scheduled = new CreateScheduledCheckins();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    public function testMonthlyReturnsCorrectDates(\UnitTester $t)
    {
        $sci1 = factory(\TenFour\Models\ScheduledCheckin::class)->create(
            [
                'frequency' => 'monthly',
                'starts_at' => '2190-01-31 21:19:00',
                'expires_at' => '2190-05-01 21:19:00',
            ]
        );
        $checkIn = factory(\TenFour\Models\CheckIn::class)->create(['scheduled_checkin_id'=> $sci1]);
        $scheduled = new CreateScheduledCheckins();
        $return = $scheduled->getMonthlyDates('2019-02-28 21:19:00', $sci1);
        $t->assertEquals($return, '2019-03-31 21:19:00');
        $return = $scheduled->getMonthlyDates('2019-01-31 21:19:00', $sci1);
        $t->assertEquals($return, '2019-02-28 21:19:00');
        $return = $scheduled->getMonthlyDates('2019-03-31 21:19:00', $sci1);
        $t->assertEquals($return, '2019-04-30 21:19:00');
    }
}
