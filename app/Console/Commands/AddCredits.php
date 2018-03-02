<?php

namespace TenFour\Console\Commands;

use TenFour\Contracts\Repositories\OrganizationRepository;
use Illuminate\Console\Command;
use App;

class AddCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:add {subdomain} {credits}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add credits to an organization';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->creditService = App::make('TenFour\Services\CreditService');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(OrganizationRepository $organizations)
    {
        $org = $organizations->findBySubdomain($this->argument('subdomain'));

        $creditAdjustment = $this->creditService->addCreditAdjustment(
            $org['id'],
            $this->argument('credits'),
            'topup'
        );

        $this->info($org['name'] . ' now has ' . $creditAdjustment['balance'] . ' credits.');
    }
}
