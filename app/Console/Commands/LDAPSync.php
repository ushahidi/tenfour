<?php

namespace TenFour\Console\Commands;

use TenFour\Jobs\LDAPSyncOrg as LDAPSyncOrgJob;
use TenFour\Jobs\LDAPSyncAll as LDAPSyncAllJob;
use TenFour\Contracts\Repositories\OrganizationRepository;

use Illuminate\Console\Command;

use DB;

class LDAPSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:ldap {subdomain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync organizations with LDAP configured';

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
        if ($this->argument('subdomain')) {
            $organization = $organizations->findBySubdomain($this->argument('subdomain'));

            if ($organization) {
                dispatch((new LDAPSyncOrgJob($organization['id'])));
            }
        } else {
            dispatch((new LDAPSyncAllJob()));
        }
    }
}
