<?php

namespace TenFour\Jobs;

use TenFour\Models\Organization;
use TenFour\Contracts\Repositories\OrganizationRepository;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon\Carbon;

class FixOrgOwners implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrganizationRepository $organizations)
    {
        foreach ($organizations->all() as $org) {
            $org = Organization::findOrFail($org['id']);

            if (!$org->owner()) {
                \Log::warning('Org ' . $org['subdomain'] . ' has no owner');

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
