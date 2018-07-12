<?php

namespace TenFour\Jobs;

use TenFour\Models\Organization;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\SubscriptionRepository;
use TenFour\Contracts\Services\PaymentService;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use ChargeBee_Subscription;

class SyncSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const MAX_USERS_IN_FLAT_RATE = 100;
    const USERS_IN_USER_BUNDLE = 25;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    protected function getUserBundleQuantity($numUsersInOrganization)
    {
        return ceil(($numUsersInOrganization - self::MAX_USERS_IN_FLAT_RATE) / self::USERS_IN_USER_BUNDLE);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrganizationRepository $organizations, SubscriptionRepository $subscriptions, PaymentService $payments)
    {
        foreach ($organizations->all() as $org) {
            $org = Organization::findOrFail($org['id']);
            $subscription = $org->currentSubscription();

            if (!$subscription) {
                \Log::warn("Organization " . $org->subdomain . " has no subscriptions");
                continue;
            }

            // \Log::info($org->subdomain);

            $hasChanged = false;

            // check if user bundles have changed

            $oldUserBundleQuantity = $subscription->addons()
                ->where('addon_id', $payments->getUserBundleAddonId())
                ->value('quantity');

            $newUserBundleQuantity = count($org->members) > self::MAX_USERS_IN_FLAT_RATE
                ? intval($this->getUserBundleQuantity(count($org->members)))
                : 0;

            if ($newUserBundleQuantity != $oldUserBundleQuantity) {
                $hasChanged = true;
            }

            // check if credit bundles have changed

            $oldCreditBundleQuantity = $subscription->addons()
                ->where('addon_id', $payments->getCreditBundleAddonId())
                ->value('quantity');

            $planAndCreditsSettings = $organizations->getSetting($org->id, 'plan_and_credits');
            $newCreditBundleQuantity = $planAndCreditsSettings && $planAndCreditsSettings->monthlyCreditsExtra
                ? intval($planAndCreditsSettings->monthlyCreditsExtra)
                : 0;

            if ($newCreditBundleQuantity != $oldCreditBundleQuantity) {
                $hasChanged = true;
            }

            if (!$hasChanged) {
                continue;
            }

            $checkout = array(
                "planId" => $subscription->plan_id,
                "replaceAddonList" => true,
                "addons" => []
            );

            if ($newCreditBundleQuantity) {
                array_push($checkout["addons"], [
                    "id" => $payments->getCreditBundleAddonId(),
                    "quantity" => $newCreditBundleQuantity
                ]);
            }

            if ($newUserBundleQuantity) {
                array_push($checkout["addons"], [
                    "id" => $payments->getUserBundleAddonId(),
                    "quantity" => $newUserBundleQuantity
                ]);
            }

            // \Log::info($checkout);

            ChargeBee_Subscription::update($subscription->subscription_id, $checkout);
        }
    }
}
