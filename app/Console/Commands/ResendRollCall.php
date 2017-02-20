<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;
use RollCall\Contracts\Repositories\RollCallRepository;
use RollCall\Jobs\SendRollCall;

class ResendRollCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollcall:resend {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend a rollcall';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(RollCallRepository $rollcalls)
    {
        $roll_call = $rollcalls->find($this->argument('id'));

        dispatch(new SendRollCall($roll_call));
    }
}
