<?php

namespace TenFour\Jobs;

use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Jobs\LDAPSyncOrg as LDAPSyncOrgJob;

use DB;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LDAPSyncAll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle()
    {
        $ldap_organizations = DB::table('organizations')
            ->leftJoin('settings', 'organizations.id', '=', 'settings.organization_id')
            ->where('settings.key', '=', 'ldap')
            ->get()
            ->toArray();

        foreach ($ldap_organizations as $organization) {
            // TODO stagger syncing orgs to avoid swamping resources

            dispatch((new LDAPSyncOrgJob($organization->organization_id)));
        }
    }
}
