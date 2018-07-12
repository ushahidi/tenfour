<?php

namespace TenFour\Http\Controllers;

use TenFour\Models\Subscription;
use TenFour\Models\Addon;
use TenFour\Http\Controllers\Controller;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Services\CreditService;
use Illuminate\Http\Request;
use ChargeBee_Environment;
use ChargeBee_Subscription;
use ChargeBee_Coupon;
use ChargeBee_InvalidRequestException;
use TenFour\Notifications\PaymentFailed;
use TenFour\Notifications\PaymentSucceeded;
use TenFour\Notifications\TrialEnding;
use TenFour\Contracts\Services\PaymentService;

use Log;

class ChargeBeeWebhookController extends Controller
{
    const MAX_USERS_IN_FLAT_RATE = 100;
    const USERS_IN_USER_BUNDLE = 25;

    public function __construct(OrganizationRepository $organizations, CreditService $creditService, PaymentService $payments)
    {
        $this->organizations = $organizations;
        $this->creditService = $creditService;
        $this->payments = $payments;

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

        // $subscription = Subscription::where('subscription_id', '2smoc9EGQxUoH9uRnj')->first();

        if (!$subscription) {
            Log::warning('[ChargeBee] No subscription found for id ' . $payload->subscription->id);
            return abort(200, 'No subscription found');
        }

        return $subscription;
    }

    protected function getSubscriptionByCustomerId($payload)
    {
        $subscription = Subscription::where('customer_id', $payload->customer->id)
            // ->with('addons')
            ->first();

        if (!$subscription) {
            Log::warning('[ChargeBee] No subscription found for customer id ' . $payload->customer->id);
            return abort(200, 'No subscription found');
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

        // use this opportunity to update the subscription based on number of users and addon settings
        // TODO move this to a daily job

        $numUsers = count($subscription->organization->members);
        $planAndCreditsSettings = $this->organizations->getSetting($subscription->organization->id, 'plan_and_credits');

        $checkout = array(
            "planId" => $subscription->plan_id,
            "replaceAddonList" => true,
            "addons" => []
        );

        if ($planAndCreditsSettings && $planAndCreditsSettings->monthlyCreditsExtra) {
            array_push($checkout["addons"], [
                "id" => $this->payments->getCreditBundleAddonId(),
                "quantity" => $planAndCreditsSettings->monthlyCreditsExtra
            ]);
        }

        if ($numUsers > self::MAX_USERS_IN_FLAT_RATE) {
            array_push($checkout["addons"], [
                "id" => $this->payments->getUserBundleAddonId(),
                "quantity" => $this->getUserBundleQuantity($numUsers)
            ]);
        }

        ChargeBee_Subscription::update($subscription->subscription_id, $checkout);

        Log::info('[ChargeBee] Processed SubscriptionRenewalReminder for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    protected function getUserBundleQuantity($numUsersInOrganization)
    {
        return ceil(($numUsersInOrganization - self::MAX_USERS_IN_FLAT_RATE) / self::USERS_IN_USER_BUNDLE);
    }

    protected function topup($organization_id, $credits, $meta)
    {
        return $this->creditService->addCreditAdjustment($organization_id, $credits, 'topup', $meta);
    }

    public function handlePaymentSucceeded($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => isset($payload->subscription->trial_end)?$payload->subscription->trial_end:null,
            'status'            => $payload->subscription->status,
        ]);

        if (isset($payload->card)) {
            $subscription->update([
                'last_four'         => $payload->card->last4,
                'card_type'         => ucfirst($payload->card->card_type),
                'expiry_month'      => $payload->card->expiry_month,
                'expiry_year'       => $payload->card->expiry_year,
            ]);
        }

        $credits = 0;

        if (isset($payload->invoice) && isset($payload->invoice->line_items)) {
            foreach ($payload->invoice->line_items as $line_item) {
                if ($line_item->entity_id === $this->payments->getProPlanId()) {
                    $credits += CreditService::BASE_CREDITS_PER_MONTH;
                }
                else if ($line_item->entity_id === $this->payments->getUserBundleAddonId()) {
                    $credits += CreditService::CREDITS_PER_USER_BUNDLE_PER_MONTH * $line_item->quantity;
                }
                else if ($line_item->entity_id === $this->payments->getCreditBundleAddonId()) {
                    $credits += $line_item->quantity;
                }
                else if ($line_item->entity_id === $this->payments->getCreditTopupAddonId()) {
                    $credits += $line_item->quantity;
                }
            }
        }

        if ($credits) {
            $creditAdjustment = $this->topup($subscription->organization->id, $credits, $payload);

            $subscription->organization->owner()->notify(new PaymentSucceeded($subscription, (object) [
                "adjustment" => $credits,
                "balance" => $creditAdjustment->balance
            ]));
        }

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

        $this->payments->changeToFreePlan($subscription->subscription_id);

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

        // nothing to do here - subscriptions are handled during organization onboarding

        Log::info('[ChargeBee] Processed SubscriptionCreated for subscription ' . $payload->subscription->id);

        return response('OK', 200);

    }

    public function handleSubscriptionRenewed($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => isset($payload->subscription->trial_end)?$payload->subscription->trial_end:null,
            'status'            => $payload->subscription->status,
        ]);

        if ($subscription['promo_code']) {
            // https://github.com/ushahidi/RollCall/issues/735
            try {
                $coupon_result = ChargeBee_Coupon::retrieve($subscription['promo_code']);
                if ($coupon_result->coupon()->discountPercentage == 100) {
                    $meta = $payload;
                    $meta->rc_freepromo = true;
                    $creditAdjustment = $this->topup($subscription->organization->id, config('credits.freepromo'), $meta);
                }
            }
            catch (ChargeBee_InvalidRequestException $e) {}
            catch (Exception $e) {}
        }


        Log::info('[ChargeBee] Processed SubscriptionRenewed for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionDeleted($payload)
    {
        $subscription = $this->getSubscription($payload);

        $this->payments->changeToFreePlan($subscription->subscription_id);

        Log::info('[ChargeBee] Processed SubscriptionDeleted for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }

    public function handleSubscriptionChanged($payload)
    {
        $subscription = $this->getSubscription($payload);

        $subscription->update([
            'status'            => $payload->subscription->status,
            'plan_id'           => $payload->subscription->plan_id,
            'next_billing_at'   => $payload->subscription->next_billing_at,
            'trial_ends_at'     => isset($payload->subscription->trial_end)?$payload->subscription->trial_end:null,
            'quantity'          => $payload->subscription->plan_quantity,
            'promo_code'        => isset($payload->subscription->coupons) && count($payload->subscription->coupons) ? $payload->subscription->coupons[0]->coupon_id : null,
            'promo_ends_at'     => isset($payload->subscription->coupons) && count($payload->subscription->coupons) && isset($payload->subscription->coupons[0]->apply_till) ? $payload->subscription->coupons[0]->apply_till : null,

        ]);

        if (isset($payload->card)) {
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

    public function handleCardUpdated($payload)
    {
        $subscription = $this->getSubscriptionByCustomerId($payload);

        if (isset($payload->card)) {
            $subscription->update([
                'last_four'         => $payload->card->last4,
                'card_type'         => ucfirst($payload->card->card_type),
                'expiry_month'      => $payload->card->expiry_month,
                'expiry_year'       => $payload->card->expiry_year,
            ]);
        }

        Log::info('[ChargeBee] Processed CardUpdated for subscription ' . $subscription->subscription_id);

        return response('OK', 200);
    }
}
