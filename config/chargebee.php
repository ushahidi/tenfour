<?php

return [
    // You can set the entity who gets subscribed here.
    'model' => RollCall\Models\Organization::class,

    'plan' => env('CHARGEBEE_PLAN'),
    'site' => env('CHARGEBEE_SITE'),
    'key' => env('CHARGEBEE_KEY'),
    'addon' => env('CHARGEBEE_ADDON'),

    'webhook' => [
        'username' => env('CHARGEBEE_WEBHOOK_USERNAME', 'chargebee'),
        'password' => env('CHARGEBEE_WEBHOOK_PASSWORD', 'westgate')
    ]

];
