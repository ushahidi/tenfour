<?php

namespace TenFour\Jobs;

use TenFour\Models\Organization;
use TenFour\Notifications\CreditsLow;
use TenFour\Notifications\CreditsZero;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Services\CreditService;
use TenFour\Contracts\Services\PaymentService;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon\Carbon;

class CheckLowCredits implements ShouldQueue
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
    public function handle(OrganizationRepository $organizations, PaymentService $payments,CreditService $creditService)
    {
        foreach ($organizations->all() as $org) {

            $org = Organization::findOrFail($org['id']);
            $subscription = $org->currentSubscription();

            if (!$subscription) {
                \Log::warning("Organization " . $org->subdomain . " has no subscriptions");
                continue;
            }

            $created_at = Carbon::createFromTimestamp(strtotime($org->created_at));

            if ($created_at->diffInDays(Carbon::now()) < 1) {
                continue;
            };

            if ($subscription->plan_id === $payments->getFreePlanId()) {
                continue;
            }

            $creditsLowNotification = $org->owner()->notifications()
                ->where('type', '=', 'TenFour\Notifications\CreditsLow');
            $creditsZeroNotification = $org->owner()->notifications()
                ->where('type', '=', 'TenFour\Notifications\CreditsZero');

            $numUsers = count($org->members);
            $credits = $creditService->getBalance($org['id']);

            // Low credits

            if ($credits < $numUsers && $credits > 0) {
                if (!$creditsLowNotification->count()) {
                    $org->owner()->notify(new CreditsLow($org, $credits));
                }
            } else {
                if ($creditsLowNotification->count()) {
                    $creditsLowNotification->delete();
                }
            }

            // Zero credits

            if ($credits <= 0) {
                if (!$creditsZeroNotification->count()) {
                    $org->owner()->notify(new CreditsZero($org));
                }

                if ($creditsLowNotification->count()) {
                    $creditsLowNotification->delete();
                }
            } else {
                if ($creditsZeroNotification->count()) {
                    $creditsZeroNotification->delete();
                }
            }
        }
    }
}
