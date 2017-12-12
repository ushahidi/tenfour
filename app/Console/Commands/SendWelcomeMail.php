<?php

namespace RollCall\Console\Commands;

use RollCall\Notifications\Welcome;
use RollCall\Notifications\WelcomeAbandoned;
use RollCall\Services\AnalyticsService;
use RollCall\Models\Organization;

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
        // - get orgs that have joined in the last 24 hours

        $yesterday = Carbon::now()->subDay();

        $organizations = DB::table('organizations')
            ->where('created_at', '>=', $yesterday)
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
