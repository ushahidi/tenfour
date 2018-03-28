<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Contracts\Repositories\CheckInRepository;
use TenFour\Jobs\SendCheckIn;

class ResendCheckIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkin:resend {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend a check-in';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(CheckInRepository $check_ins)
    {
        $check_in = $check_ins->find($this->argument('id'));

      dispatch((new SendCheckIn($check_in))/*->onQueue('checkins')*/);
    }
}
