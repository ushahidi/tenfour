<?php

namespace TenFour\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TenFour\Models\CheckIn;
use TenFour\Models\ScheduledCheckin;
use TenFour\Contracts\Repositories\CheckInRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendScheduledCheckin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $check_in_repo;
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
    public function handle(CheckInRepository $check_in_repo)
    {
        $this->check_in_repo = $check_in_repo;
        $check_ins = CheckIn::where([['send_at', '<', DB::raw('NOW()')], ['sent', '=', 0]])->get();
        array_map(function($check_in) {
            $scheduled_checkin = ScheduledCheckin::where(
                [['id', '=', $check_in['scheduled_checkin_id']]]
            )->first();
            $original = CheckIn::where(
                [['scheduled_checkin_id', '=', $scheduled_checkin->id]]
            )->whereNull('send_at')->first();
            $check_in['recipients'] = $original->recipients;
            $recipients = array_map(function($recipient) {
                return ['id' => $recipient['id']];
            }, $original->recipients->toArray());
            $this->check_in_repo->update(['recipients' => $recipients], $check_in['id']);
            dispatch((new SendCheckIn($check_in)));
        }, $check_ins->toArray());
    }
}
