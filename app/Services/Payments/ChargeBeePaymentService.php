<?php

namespace TenFour\Services\Payments;
use TenFour\Contracts\Services\PaymentService;

use ChargeBee_Environment;
use ChargeBee_HostedPage;
use ChargeBee_Subscription;
use ChargeBee_Coupon;
use ChargeBee_InvalidRequestException;
use Carbon\Carbon;

class ChargeBeePaymentService implements PaymentService
{
    const TRIAL_PERIOD_DAYS = 30;

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

    private function getFreePlanId()
    {
        return config("chargebee.plans.free");
    }

    private function getProPlanId()
    {
        return config("chargebee.plans.pro");
    }

    private function getTrialEnd()
    {
        return Carbon::now()->addDays(self::TRIAL_PERIOD_DAYS)->timestamp;
    }

    private function getCreditsAddonId()
    {
        return config("chargebee.addons.credits");
    }

    public function getProUpgradeHostedPageUrl($organization, $redirectUrl)
    {
        // $numUsers = count($organization->members);

        $checkout = array(
            "subscription" => array(
                "id" => $organization->currentSubscription()->subscription_id,
                "planId" => $this->getProPlanId(),
                "planQuantity" => 1,
                "autoCollection" => "on",
                "trialEnd" => 0,
            ),
            "embed" => true,
            "forceTermReset" => true,
            "redirectUrl" => $redirectUrl,
            "cancelledUrl" => $redirectUrl,
            "passThruContent" => json_encode(["organization_id" => $organization->id]),
        );

        // if ($isFreeTrial) {
        //     unset($checkout['subscription']['trialEnd']);
        // }
        //
        // if ($addonQuantity) {
        //     $checkout["addons"] = array(array(
        //         "id" => $this->getCreditsAddonId(),
        //         "quantity" => $addonQuantity
        //     ));
        // }

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

    // public function retrieveHostedPage($subscription_id)
    // {
    //     $result = ChargeBee_HostedPage::retrieve($subscription_id);
    //     $hostedPage = $result->hostedPage();
    //     $passThruContent = json_decode($hostedPage->passThruContent);
    //     $hostedPage->organization_id = $passThruContent->organization_id;
    //
    //     return $hostedPage;
    // }

    public function createSubscription($organization)
    {
        // create a freemium subscription for new organizations

        $result = ChargeBee_Subscription::create([
            "planId" => $this->getFreePlanId(),
            // "trial_end" => $this->getTrialEnd(),
            "customer" => array(
                "email" => $organization->owner()->email(),
                "company" => $organization->name,
                "phone" => $organization->owner()->phone()
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
        ]);

        return $this->toArray($result);
    }

    public function changeToProPlan($subscription_id)
    {
        $result = ChargeBee_Subscription::update($subscription_id, [
            "planId"            => $this->getProPlanId(),
            "status"            => "active",
            "replaceAddonList"  => true,
        ]);

        return $this->toArray($result);
    }
}
