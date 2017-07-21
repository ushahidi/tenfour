<?php

namespace RollCall\Http\Controllers;

use RollCall\Models\Subscription;
use RollCall\Models\Addon;
use RollCall\Http\Controllers\Controller;
use RollCall\Contracts\Repositories\OrganizationRepository;
use RollCall\Services\CreditService;
use Illuminate\Http\Request;
use ChargeBee_Environment;
use ChargeBee_Subscription;
use RollCall\Notifications\PaymentFailed;
use RollCall\Notifications\PaymentSucceeded;
use RollCall\Notifications\TrialEnding;

use Log;

class ChargeBeeWebhookController extends Controller
{
    public function __construct(OrganizationRepository $organizations, CreditService $creditService)
    {
        $this->organizations = $organizations;
        $this->creditService = $creditService;

        ChargeBee_Environment::configure(config("chargebee.site"),config("chargebee.key"));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request)
    {
        $webhookEvent = studly_case($request->event_type);

        if (method_exists($this, 'handle' . $webhookEvent)) {
            $payload = json_decode(json_encode($request->input('content')));

            return $this->{'handle' . $webhookEvent}($payload);
        } else {
            Log::warning('[ChargeBee] No event handler for ' . $webhookEvent);
            return response(" No event handler for " . $webhookEvent, 200);
        }
    }

    protected function getSubscription($payload)
    {
        $subscription = Subscription::where('subscription_id', $payload->subscription->id)
            // ->with('addons')
            ->first();

        if (!$subscription) {
            Log::warning('[ChargeBee] No subscription found for id ' . $payload->subscription->id);
            return abort(404, 'No subscription found');
        }

        return $subscription;
    }

    public function handleSubscriptionTrialEndReminder($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => isset($payload->subscription->trial_end)?$payload->subscription->trial_end:null,
            'status'            => $payload->subscription->status,
        ]);

        $subscription->organization->owner()->notify(new TrialEnding($subscription));

        Log::info('[ChargeBee] Processed SubscriptionTrialEndReminder for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionRenewalReminder($payload)
    {
        $subscription = $this->getSubscription($payload);

        // use this opportunity to update the subscription based on number of users and extra credit settings

        $numUsers = count($subscription->organization->members);
        $planAndCreditsSettings = $this->organizations->getSetting($subscription->organization->id, 'plan_and_credits');

        $checkout = array(
            "planId" => config("chargebee.plan"),
            "planQuantity" => $numUsers,
            "replaceAddonList" => true,
        );

        if ($planAndCreditsSettings && $planAndCreditsSettings->monthlyCreditsExtra) {
            $checkout["addons"] = array(array(
                "id" => config("chargebee.addon"),
                "quantity" => $planAndCreditsSettings->monthlyCreditsExtra
            ));
        }

        ChargeBee_Subscription::update($subscription->subscription_id, $checkout);

        Log::info('[ChargeBee] Processed SubscriptionRenewalReminder for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handlePaymentSucceeded($payload)
    {
        $subscription = $this->getSubscription($payload);

        // 5 text message (SMS) credits per plan quantity
        // plus addon quantity

        $credits = CreditService::CREDITS_PER_USER_PER_MONTH * $payload->subscription->plan_quantity;

        if (isset($subscription->addons)) {
            foreach ($subscription->addons as $key => $addon) {
                if ($addon->addon_id === config("chargebee.addon")) {
                    $credits += $addon->quantity;
                }
            }
        }

        $meta = $payload;

        $creditAdjustment = $this->creditService->addCreditAdjustment($subscription->organization->id, $credits, 'topup', $meta);

        $subscription->update([
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => isset($payload->subscription->trial_end)?$payload->subscription->trial_end:null,
            'status'            => $payload->subscription->status,
        ]);

        $subscription->organization->owner()->notify(new PaymentSucceeded($subscription, $creditAdjustment));

        Log::info('[ChargeBee] Processed PaymentSucceeded for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handlePaymentFailed($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->organization->owner()->notify(new PaymentFailed($subscription));

        $subscription->update([
            'status'            => $payload->subscription->status,
        ]);

        Log::info('[ChargeBee] Processed PaymentFailed for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionCancelled($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'status'            => $payload->subscription->status,
        ]);

        Log::info('[ChargeBee] Processed SubscriptionCancelled for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionReactivated($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'status'            => $payload->subscription->status,
        ]);

        Log::info('[ChargeBee] Processed SubscriptionReactivated for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionCreated($payload)
    {
        $subscription = Subscription::where('subscription_id', $payload->subscription->id)->first();

        if ($subscription) {
            Log::info('[ChargeBee] Skipping existing subscription ' . $payload->subscription->id);
            return response('OK', 200);
        }

        // The client didn't handle the create subscription callback correctly

        Log::error('[ChargeBee] Received SubscriptionCreated for non-existant subscription ' . $payload->subscription->id);

        return response('OK', 200);

    }

    public function handleSubscriptionRenewed($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => $payload->subscription->trial_end,
            'status'            => $payload->subscription->status,
        ]);

        Log::info('[ChargeBee] Processed SubscriptionRenewed for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionDeleted($payload)
    {
        $subscription = $this->getSubscription($payload);

        Addon::where('subscription_id', $subscription->id)->delete();
        Subscription::where('id', $subscription->id)->delete();

        Log::info('[ChargeBee] Processed SubscriptionDeleted for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionChanged($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'status'            => $payload->subscription->status,
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => isset($payload->subscription->trial_end)?$payload->subscription->trial_end:null,
            'quantity'          => $payload->subscription->plan_quantity,
        ]);

        if ($payload->card) {
            $subscription->update([
                'last_four'         => $payload->card->last4,
                'card_type'         => ucfirst($payload->card->card_type),
                'expiry_month'      => $payload->card->expiry_month,
                'expiry_year'       => $payload->card->expiry_year,
            ]);
        }

        if (isset($payload->subscription->addons)) {
            Addon::where('subscription_id', $subscription->id)->delete();

            foreach ($payload->subscription->addons as $addon) {
                $subscription->addons()->create([
                    'quantity' => $addon->quantity,
                    'addon_id' => $addon->id,
                ]);
            }
        }

        Log::info('[ChargeBee] Processed SubscriptionChanged for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

}
