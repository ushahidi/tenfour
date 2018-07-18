<?php

namespace TenFour\Services\Payments;
use TenFour\Contracts\Services\PaymentService;

use ChargeBee_Environment;
use ChargeBee_HostedPage;
use ChargeBee_Subscription;
use ChargeBee_Coupon;
use ChargeBee_InvalidRequestException;
use ChargeBee_Invoice;
use Carbon\Carbon;

class ChargeBeePaymentService implements PaymentService
{
    const TRIAL_PERIOD_DAYS = 30;
    const PRO_PLAN_FLAT_RATE_COST = 39;
    const CREDIT_BUNDLE_COST = .1;
    const USER_BUNDLE_COST = 5;

    public function __construct()
    {
        ChargeBee_Environment::configure(config("chargebee.site"),config("chargebee.key"));
    }

    protected function toArray($result)
    {
        $subscription = $result->subscription();
        $addons = $subscription->addons;
        $card = $result->card();
        $customer = $result->customer();

        return [
            'subscription' => $subscription->getValues(),
            'card' => $card ? $card->getValues() : null,
            'customer' => $customer->getValues(),
        ];
    }

    public function getFreePlanId()
    {
        return config("chargebee.plans.free");
    }

    public function getProPlanId()
    {
        return config("chargebee.plans.pro");
    }

    private function getTrialEnd()
    {
        return Carbon::now()->addDays(self::TRIAL_PERIOD_DAYS)->timestamp;
    }

    public function getCreditBundleAddonId()
    {
        return config("chargebee.addons.credits");
    }

    public function getUserBundleAddonId()
    {
        return config("chargebee.addons.users");
    }

    public function getCreditTopupAddonId()
    {
        return config("chargebee.addons.topup");
    }

    public function getProUpgradeHostedPageUrl($organization, $redirectUrl)
    {
        $checkout = array(
            "subscription" => array(
                "id" => $organization->currentSubscription()->subscription_id,
                "planId" => $this->getProPlanId(),
                "planQuantity" => 1,
                "autoCollection" => "on",
                "trialEnd" => 0,
            ),
            "embed" => true,
            "redirectUrl" => $redirectUrl,
            "cancelledUrl" => $redirectUrl,
            "passThruContent" => json_encode(["organization_id" => $organization->id]),
        );

        $hostedPage = ChargeBee_HostedPage::checkoutExisting($checkout)->hostedPage();

        return $hostedPage->url;
    }

    public function getUpdatePaymentInfoHostedPageUrl($organization, $redirectUrl)
    {
        $checkout = array(
            "customer" => array(
                "id" => $organization->currentSubscription()->customer_id
            ),
            "embed" => true,
            "redirectUrl" => $redirectUrl,
            "cancelledUrl" => $redirectUrl,
            "passThruContent" => json_encode(["organization_id" => $organization->id]),
        );

        $hostedPage = ChargeBee_HostedPage::updatePaymentMethod($checkout)->hostedPage();

        return $hostedPage->url;
    }

    public function createSubscription($organization)
    {
        // create a freemium subscription for new organizations

        $result = ChargeBee_Subscription::create([
            "planId"          => $this->getFreePlanId(),
            "autoCollection"  => "off",
            "customer"        => array(
                "email"       => $organization->owner()->email(),
                "company"     => $organization->name,
                "phone"       => $organization->owner()->phone()
            ),
        ]);

        return $this->toArray($result);
    }

    public function retrieveSubscription($subscription_id)
    {
        $result = ChargeBee_Subscription::retrieve($subscription_id);

        return $this->toArray($result);
    }

    public function cancelSubscription($subscription_id)
    {
        $result = ChargeBee_Subscription::cancel($subscription_id);

        return $this->toArray($result);
    }

    public function reactivateSubscription($subscription_id)
    {
        $result = ChargeBee_Subscription::reactivate($subscription_id);

        return $this->toArray($result);
    }

    public function retrieveCoupon($promo_code)
    {
        try {
            $result = ChargeBee_Coupon::retrieve($promo_code);
            $coupon = $result->coupon();
        } catch (ChargeBee_InvalidRequestException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }

        return $coupon->getValues();
    }

    public function changeToFreePlan($subscription_id)
    {
        $result = ChargeBee_Subscription::update($subscription_id, [
            "planId"            => $this->getFreePlanId(),
            "status"            => "active",
            "replaceAddonList"  => true,
            "addons"            => []
        ]);

        return $this->toArray($result);
    }

    public function changeToProPlan($subscription_id)
    {
        $result = ChargeBee_Subscription::update($subscription_id, [
            "planId"            => $this->getProPlanId(),
            "status"            => "active",
            "replaceAddonList"  => true,
            "addons"            => []
        ]);

        return $this->toArray($result);
    }

    public function chargeAddonImmediately($subscription_id, $addonId, $addonQuantity)
    {
        $result = ChargeBee_Invoice::chargeAddon(array(
            "subscriptionId"  => $subscription_id,
            "addonId"         => $addonId,
            "addonQuantity"   => $addonQuantity));

        return $result->invoice()->getValues();
    }

    public function estimateBill($subscription)
    {
        $estimate = 0;

        if ($subscription->plan_id === $this->getProPlanId()) {
            $estimate += self::PRO_PLAN_FLAT_RATE_COST;
        }

        foreach ($subscription->addons as $addon) {
            if ($addon->addon_id === $this->getCreditBundleAddonId()) {
                $estimate += $addon->quantity * self::CREDIT_BUNDLE_COST;
            }
            else if ($addon->addon_id === $this->getUserBundleAddonId()) {
                $estimate += $addon->quantity * self::USER_BUNDLE_COST;
            }
        }

        return $estimate;
    }
}
