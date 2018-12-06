<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Models\Organization;

class FixOrgOwners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'org:fix-owners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For organizations without owners, set the owner to be the first admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(OrganizationRepository $organizations)
    {
        $job = new \TenFour\Jobs\FixOrgOwners();

        $job->handle($organizations);
    }
}
