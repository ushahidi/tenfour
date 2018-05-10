<?php
namespace TenFour\Contracts\Services;

interface PaymentService
{
    public function getPlanId();
    public function getAddonId();

    public function checkoutHostedPage($organization, $redirectUrl, $addonQuantity = 0, $isFreeTrial = false);
    public function checkoutUpdateHostedPage($organization, $redirectUrl);
    public function retrieveHostedPage($subscription_id);

    public function createSubscription($organization);
    public function retrieveSubscription($subscription_id);
    public function cancelSubscription($subscription_id);
    public function reactivateSubscription($subscription_id);

    public function retrieveCoupon($promo_code);
}
