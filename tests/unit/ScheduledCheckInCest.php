<?php

namespace Tests\Unit;

use TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TenFour\Jobs\CreateScheduledCheckIns;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TenFour\Models\CheckIn;
use TenFour\Repositories\EloquentCheckInRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use TenFour\Models\ScheduledCheckIn;
use Illuminate\Database\Eloquent\Collection;
use Faker\Factory;
class ScheduledCheckInCest extends TestCase
{
    use DatabaseTransactions;
    public function setUp()
    {
         parent::setUp();
    }
    
    /**
     * @return void
     */
    public function testCreateScheduledCheckinsHourly()
    {

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        // default hourly 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(['check_ins_id' => $checkIn->id]);
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        // 
        $check_in_repo->shouldReceive('create')->times(24);
        $scheduled = new CreateScheduledCheckIns();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsDaily()
    {

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        // 1 per day, 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(
            ['check_ins_id' => $checkIn->id, 'frequency' => 'daily']
        );
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(1);
        $scheduled = new CreateScheduledCheckIns();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }
    /**
     * @return void
     */
    public function testCreateScheduledCheckinsWeekly()
    {

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        // 1 per week, 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(
            ['check_ins_id' => $checkIn->id, 'frequency' => 'weekly']
        );
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(1);
        $scheduled = new CreateScheduledCheckIns();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsBiWeeklySingleRun()
    {

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        // 1 per week, 1 day
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(
            ['check_ins_id' => $checkIn->id, 'frequency' => 'biweekly']
        );
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(1);
        $scheduled = new CreateScheduledCheckIns();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsBiWeeklyFullMonthExpiration()
    {
        $faker = Factory::create();

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(
            [
                'check_ins_id' => $checkIn->id,
                'frequency' => 'biweekly',
                'expires_at' => date_format($faker->dateTimeBetween('+1 month', '+1 month'), "Y-m-d H:i:s"),
            ]
        );
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(3); // today, 2 weeks from today, 4 weeks from today
        $scheduled = new CreateScheduledCheckIns();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }

    /**
     * @return void
     */
    public function testCreateScheduledCheckinsMonthlyFullMonthExpiration()
    {
        $faker = Factory::create();

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(
            [
                'check_ins_id' => $checkIn->id,
                'frequency' => 'monthly',
                'expires_at' => date_format($faker->dateTimeBetween('+1 month', '+1 month'), "Y-m-d H:i:s"),
            ]
        );
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(2); // today, 1 month from today
        $scheduled = new CreateScheduledCheckIns();
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

        $checkIn = factory(\TenFour\Models\CheckIn::class)->create();
        $sci1 = factory(\TenFour\Models\ScheduledCheckIn::class)->create(
            [
                'check_ins_id' => $checkIn->id,
                'frequency' => 'monthly',
                'starts_at' => '2190-01-31 21:19:00',
                'expires_at' => '2190-04-03 21:19:00',
            ]
        );
        $scheduledCheckIns =[
            $sci1
        ];
        $check_in_repo =  \Mockery::spy('TenFour\Contracts\Repositories\CheckInRepository');
        $check_in_repo->shouldReceive('create')->times(3); // today, 1 month from today
        $scheduled = new CreateScheduledCheckIns();
        $scheduled->handle($check_in_repo, $scheduledCheckIns);
    }
}
