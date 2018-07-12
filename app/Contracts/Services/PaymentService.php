<?php
namespace TenFour\Contracts\Services;

interface PaymentService
{
    public function getProUpgradeHostedPageUrl($organization, $redirectUrl);
    public function getUpdatePaymentInfoHostedPageUrl($organization, $redirectUrl);

    public function createSubscription($organization);
    public function retrieveSubscription($subscription_id);
    public function cancelSubscription($subscription_id);
    public function reactivateSubscription($subscription_id);

    public function retrieveCoupon($promo_code);
    public function changeToFreePlan($subscription_id);
    public function changeToProPlan($subscription_id);

    public function getFreePlanId();
    public function getProPlanId();
    public function getUserBundleAddonId();
    public function getCreditBundleAddonId();
    public function getCreditTopupAddonId();

    public function chargeAddonImmediately($subscription_id, $addon_id, $addon_quantity);
}
