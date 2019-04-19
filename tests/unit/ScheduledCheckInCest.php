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
}
