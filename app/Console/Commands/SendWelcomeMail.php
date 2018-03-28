<?php

namespace TenFour\Console\Commands;

use TenFour\Notifications\Welcome;
use TenFour\Notifications\WelcomeAbandoned;
use TenFour\Services\AnalyticsService;
use TenFour\Models\Organization;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;

class SendWelcomeMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:welcome';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send welcome mail';

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
    public function handle()
    {
        // - get orgs that have joined in the last hour
        // - TODO edge case if someone signs up at the top of the hour they might erronously get the abandoned mail 

        $lastHour = Carbon::now()->subHour();

        $organizations = DB::table('organizations')
            ->where('created_at', '>=', $lastHour)
            ->get()
            ->toArray();

        foreach ($organizations as $organization) {
            $organizationModel = Organization::findOrFail($organization->id);

            if (count($organizationModel->subscriptions) > 0) {
                // - if org has completed onboarding then send Welcome mail
                $organizationModel->owner()->notify(new Welcome($organizationModel));
            } else {
                // - if org has not completed subscription then send followup
                $organizationModel->owner()->notify(new WelcomeAbandoned($organizationModel));

                (new AnalyticsService())->track('Organization Abandoned Onboarding', [
                    'org_id'          => $organization->id,
                    'subdomain'       => $organization->subdomain,
                ]);
            }
        }
    }
}
