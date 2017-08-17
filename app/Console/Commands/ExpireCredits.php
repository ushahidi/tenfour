<?php

namespace RollCall\Console\Commands;

use RollCall\Contracts\Repositories\OrganizationRepository;
use Illuminate\Console\Command;
use App;

class ExpireCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset credits at end of month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->creditService = App::make('RollCall\Services\CreditService');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(OrganizationRepository $organizations)
    {
        $this->creditService->expireCreditsOnUnpaid();
    }
}
