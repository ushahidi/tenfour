<?php

namespace TenFour\Jobs;

use TenFour\Models\Organization;
use TenFour\Notifications\ApproachingPersonQuotaLimit;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Contracts\Repositories\SubscriptionRepository;
use TenFour\Contracts\Services\PaymentService;
use TenFour\Http\Requests\Person\AddPersonRequest;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckQuotas implements ShouldQueue
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
    public function handle(OrganizationRepository $organizations, PaymentService $payments)
    {
        foreach ($organizations->all() as $org) {

            $org = Organization::findOrFail($org['id']);
            $subscription = $org->currentSubscription();

            if (!$subscription) {
                \Log::warning("Organization " . $org->subdomain . " has no subscriptions");
                continue;
            }

            if ($subscription->plan_id === $payments->getFreePlanId()) {
                $quotaNotifications = $org->owner()->notifications()
                    ->where('type', '=', 'TenFour\Notifications\ApproachingPersonQuotaLimit');

                if (count($org->members) > AddPersonRequest::MAX_PERSONS_IN_FREE_PLAN - 10) {
                    if ($quotaNotifications->count() === 0) {
                        $org->owner()->notify(new ApproachingPersonQuotaLimit($org));
                    }
                } else {
                    if ($quotaNotifications->count() > 0) {
                        $quotaNotifications->delete();
                    }
                }
            }
        }
    }
}
