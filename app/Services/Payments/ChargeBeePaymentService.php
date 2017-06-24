<?php

namespace RollCall\Services\Payments;
use RollCall\Contracts\Services\PaymentService;

use ChargeBee_Environment;
use ChargeBee_HostedPage;
use ChargeBee_Subscription;

class ChargeBeePaymentService implements PaymentService
{
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
            'card' => $card->getValues(),
            'customer' => $customer->getValues(),
        ];
    }

    public function getPlanId()
    {
        return config("chargebee.plan");
    }

    public function getAddonId()
    {
        return config("chargebee.addon");
    }

    public function checkoutHostedPage($organization, $redirectUrl, $addonQuantity = 0, $isFreeTrial = false)
    {
        $numUsers = count($organization->members);

        $checkout = array(
          "subscription" => array(
            "planId" => $this->getPlanId(),
            "planQuantity" => $numUsers,
            "autoCollection" => "on",
            "trialEnd" => 0,
            "customer" => array(
              "email" => $organization->owner()->email(),
              "company" => $organization->name,
              "phone" => $organization->owner()->phone()
            ),
          ),
          "embed" => true,
          "redirectUrl" => $redirectUrl,
          "cancelledUrl" => $redirectUrl,
          "passThruContent" => json_encode(["organization_id" => $organization->id]),
        );

        if ($isFreeTrial) {
            unset($checkout['subscription']['trialEnd']);
        }

        if ($addonQuantity) {
            $checkout["addons"] = array(array(
                "id" => $this->getAddonId(),
                "quantity" => $addonQuantity
            ));
        }

        $hostedPage = ChargeBee_HostedPage::checkoutNew($checkout)->hostedPage();

        return $hostedPage->url;
    }

    public function checkoutUpdateHostedPage($organization, $redirectUrl)
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

    public function retrieveHostedPage($subscription_id)
    {
        $result = ChargeBee_HostedPage::retrieve($subscription_id);
        $hostedPage = $result->hostedPage();
        $passThruContent = json_decode($hostedPage->passThruContent);
        $hostedPage->organization_id = $passThruContent->organization_id;

        return $hostedPage;
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
}
