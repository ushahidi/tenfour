<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Models\Organization;
use Illuminate\Support\Facades\Password;

class OrgDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'org:delete {subdomain} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an organization, including users and rollcalls.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(OrganizationRepository $organizations)
    {
        $org = $organizations->findBySubdomain($this->argument('subdomain'));

        if ($this->option('force') ||
            $this->confirm("Are you sure you want to delete the organization named '" . $org['name'] . "'?")) {

            // TODO use repository delete here
            Organization::where('id', $org['id'])->delete();

            $this->info("The organization '{$org['name']}' has been deleted.");
        }
    }
}
