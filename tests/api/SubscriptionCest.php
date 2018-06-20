<?php

class SubscriptionCest
{
    protected $webhookEndpoint = '/chargebee/webhook';

    // ChargeBee Webhook Controller tests

    private function makeChargeBeeEvent($eventType)
    {
        $content = [
            "subscription" => [
                "id" => "sub1",
                "customer_id" => "cust1",
                "plan_id" =>  "pro-plan",
                "plan_quantity" => 10,
                "status" => "active",
                "trial_start" => 1495125586,
                "trial_end" =>  1496335186,
                "next_billing_at" => 1496335186,
                "created_at" => 1495125586,
                "started_at" => 1495125586,
                "object" => "subscription",
                "currency_code" => "USD",
                "addons" => [[
                    "id" => "extra-credits",
                    "quantity" => 2000,
                ]]
            ],
            "card" => [
                "last4" => "4444",
                "card_type" => "smurfcard",
                "expiry_month" => 10,
                "expiry_year" => 30,
            ]
        ];

        return [
            "id" => "ev_HtZEwSZQJzmauG3LQ",
            "occurred_at" => 1495125587,
            "source" => "hosted_page",
            "object" => "event",
            "api_version" => "v2",
            "event_type" => $eventType,
            "content" => $content,
        ];
    }

    // TODO stub ChargeBee api calls

    // public function handleSubscriptionRenewalReminder(ApiTester $I)
    // {
    //     $payload = $this->makeChargeBeeEvent('subscription_renewal_reminder');
    //     $I->wantTo('Handle a ChargeBee subscription renewal reminder event');
    //     // $I->amAuthenticatedAsOrgAdmin();
    //     $I->sendPOST($this->webhookEndpoint, $payload);
    //     $I->seeResponseCodeIs(200);
    // }

    public function handlePaymentSucceeded(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('payment_succeeded');
        $I->wantTo('Handle a ChargeBee payment succeeded event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        // check that the credit adjustment has been made
        $I->seeRecord('credit_adjustments', [
            'organization_id'         => 2,
            'adjustment'              => 1047,
            'type'                    => 'topup',
        ]);

        // check the next billing is updated
        $I->seeRecord('subscriptions', [
            'subscription_id'         => 'sub1',
            'status'                  => 'active',
            'next_billing_at'         => date("Y-m-d H:i:s", $payload['content']['subscription']['next_billing_at']),
            'trial_ends_at'           => date("Y-m-d H:i:s", $payload['content']['subscription']['trial_end'])
        ]);

        // check a notification has been sent
        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'TenFour\Models\User',
            'type'                    => 'TenFour\Notifications\PaymentSucceeded',
        ]);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Payment Succeeded",
            'type'        => 'PaymentSucceeded',
            'to'          => 'org_owner@ushahidi.com',
        ]);
    }

    public function handlePaymentFailed(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('payment_failed');
        $payload['content']['subscription']['status'] = 'cancelled';

        $I->wantTo('Handle a ChargeBee payment failed event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        // check the next billing is updated
        $I->seeRecord('subscriptions', [
            'subscription_id'         => 'sub1',
            'status'                  => 'cancelled',
        ]);

        // check a notification has been sent
        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'TenFour\Models\User',
            'type'                    => 'TenFour\Notifications\PaymentFailed',
        ]);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Payment Failed",
            'type'        => 'PaymentFailed',
            'to'          => 'org_owner@ushahidi.com',
        ]);
    }

    // disable for now - can't test without mocking chargebee service.
    // subscription cancelled manually via chargebee is an edge case.
    //
    // public function handleSubscriptionCancelled(ApiTester $I)
    // {
    //     $payload = $this->makeChargeBeeEvent('subscription_cancelled');
    //     $payload['content']['subscription']['status'] = 'cancelled';
    //
    //     $I->wantTo('Handle a ChargeBee subscription cancelled event');
    //     $I->amAuthenticatedAsChargeBee();
    //     $I->sendPOST($this->webhookEndpoint, $payload);
    //     $I->seeResponseCodeIs(200);
    //
    //     // check the status is updated
    //     $I->seeRecord('subscriptions', [
    //         'subscription_id'         => 'sub1',
    //         'status'                  => 'cancelled',
    //     ]);
    // }

    public function handleSubscriptionReactivated(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('subscription_reactivated');

        $I->wantTo('Handle a ChargeBee subscription reactivated event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        // check the status is updated
        $I->seeRecord('subscriptions', [
            'subscription_id'         => 'sub1',
            'status'                  => 'active',
        ]);
    }

    public function handleSubscriptionRenewed(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('subscription_renewed');

        $I->wantTo('Handle a ChargeBee subscription renewed event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        // check the status is updated
        $I->seeRecord('subscriptions', [
            'subscription_id'         => 'sub1',
            'status'                  => 'active',
            'next_billing_at'         => date("Y-m-d H:i:s", $payload['content']['subscription']['next_billing_at']),
            'trial_ends_at'           => date("Y-m-d H:i:s", $payload['content']['subscription']['trial_end'])
        ]);
    }

    public function handleSubscriptionDeleted(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('subscription_deleted');

        $I->wantTo('Handle a ChargeBee subscription deleted event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        $I->dontSeeRecord('subscriptions', [
            'subscription_id'         => 'sub1',
        ]);

        $I->dontSeeRecord('addons', [
            'subscription_id'         => 1,
        ]);
    }

    public function handleSubscriptionChanged(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('subscription_changed');

        $I->wantTo('Handle a ChargeBee subscription changed event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        $I->seeRecord('subscriptions', [
            'subscription_id'         => 'sub1',
            'status'                  => 'active',
            'next_billing_at'         => date("Y-m-d H:i:s", $payload['content']['subscription']['next_billing_at']),
            'trial_ends_at'           => date("Y-m-d H:i:s", $payload['content']['subscription']['trial_end']),
            "last_four"               => $payload['content']['card']['last4'],
            "card_type"               => $payload['content']['card']['card_type'],
            "expiry_month"            => $payload['content']['card']['expiry_month'],
            "expiry_year"             => $payload['content']['card']['expiry_year']
        ]);

        $I->dontSeeRecord('addons', [
            'subscription_id'         => 1,
            'quantity'                => 1000
        ]);

        $I->seeRecord('addons', [
            'subscription_id'         => 1,
            'quantity'                => 2000
        ]);
    }

    public function handleSubscriptionTrialEndReminder(ApiTester $I)
    {
        $payload = $this->makeChargeBeeEvent('subscription_trial_end_reminder');

        $I->wantTo('Handle a ChargeBee trial end reminder event');
        $I->amAuthenticatedAsChargeBee();
        $I->sendPOST($this->webhookEndpoint, $payload);
        $I->seeResponseCodeIs(200);

        // check a notification has been sent
        $I->seeRecord('notifications', [
            'notifiable_id'           => '4',
            'notifiable_type'         => 'TenFour\Models\User',
            'type'                    => 'TenFour\Notifications\TrialEnding',
        ]);

        $I->seeRecord('outgoing_mail_log', [
            'subject'     => "Trial Ending",
            'type'        => 'TrialEnding',
            'to'          => 'org_owner@ushahidi.com',
        ]);
    }

}
