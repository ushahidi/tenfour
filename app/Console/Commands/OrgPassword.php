<?php

namespace RollCall\Console\Commands;

use Illuminate\Console\Command;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Models\User;
use Illuminate\Support\Facades\Hash;

class OrgPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'org:password {subdomain} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user password all users in the org';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PersonRepository $people, OrganizationRepository $organizations)
    {
        $org = $organizations->findBySubdomain($this->argument('subdomain'));

        if ($this->confirm("Are you sure you want to set the password for all users in {$org['name']}")) {
            // @todo move this to the repo
            User::where('organization_id', $org['id'])
                ->update(['password' => Hash::make($this->argument('password'))]);

            $this->info('Set password for all users');
        }
    }
}
