<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\SubscriptionRepository;
use TenFour\Models\Organization;
use TenFour\Contracts\Services\PaymentService;

class MigrateSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate subscriptions to freemium';

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
    public function handle(OrganizationRepository $organizations, SubscriptionRepository $subscriptions, PaymentService $payments)
    {
        foreach ($organizations->all() as $org) {
            $org = Organization::findOrFail($org['id']);

            if (count($org->subscriptions) === 0) {
                // create a freemium subscription on chargebee for this orgs

                $subscription = $payments->createSubscription($org);
                $subscriptions->create($org->id, $subscription);

                $this->info('Created free-plan subscription for' . $org['subdomain']);

            } else if (count($org->subscriptions) === 1) {
                $sub = $org->subscriptions[0];

                if ($sub->plan_id === 'standard-plan') {
                    if ($sub->status === 'cancelled' || $sub->status === 'in_trial') {
                        $payments->changeToFreePlan($sub->subscription_id);
                        $this->info('Switched ' . $org['subdomain'] . ' to free plan');
                    } else if ($sub->status === 'active') {
                        $payments->changeToProPlan($sub->subscription_id);
                        $this->info('Switched ' . $org['subdomain'] . ' to pro plan');
                    }
                }

            } else {
                $this->error($org->subdomain . ' has more than one subscription');
            }
        }
    }
}
