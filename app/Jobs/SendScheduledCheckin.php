<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TenFour\Models\CheckIn;
use TenFour\Models\ScheduledCheckIn;
use TenFour\Contracts\Repositories\CheckInRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendScheduledCheckin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }
    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle()
    {DB::connection()->enableQueryLog();

        $check_ins = CheckIn::where([['send_at', '<', DB::raw('NOW()')], ['sent', '=', 0]])->get();
        array_map(function($check_in) {
            dispatch((new SendCheckIn($check_in)));
        }, $check_ins->toArray());
    }
}
