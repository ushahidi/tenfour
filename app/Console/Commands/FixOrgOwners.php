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
        foreach ($organizations->all() as $org) {
            $org = Organization::findOrFail($org['id']);

            if (!$org->owner()) {
                $this->info('Org ' . $org['subdomain'] . ' has no owner');

                $admin = $org->members->where('role', 'admin')->first();

                if (!$admin) {
                    $admin = $org->members->first();
                }

                $admin->role = 'owner';

                $admin->save();
            }
        }
    }
}
