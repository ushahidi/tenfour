<?php

return [
    // You can set the entity who gets subscribed here.
    'model' => TenFour\Models\Organization::class,

    'site' => env('CHARGEBEE_SITE'),
    'key' => env('CHARGEBEE_KEY'),

    'addons' => [
        'credits' => env('CHARGEBEE_ADDON_CREDITS', 'credit-bundle'),
        'users' => env('CHARGEBEE_ADDON_USERS', 'user-bundle'),
    ],

    'plans' => [
        'free'  => env('CHARGEBEE_FREE_PLAN', 'free-plan'),
        'pro'   => env('CHARGEBEE_PRO_PLAN', 'pro-plan')
    ],

    'webhook' => [
        'username' => env('CHARGEBEE_WEBHOOK_USERNAME', 'chargebee'),
        'password' => env('CHARGEBEE_WEBHOOK_PASSWORD', 'westgate')
    ]

];
