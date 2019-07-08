<?php

return [
    // You can set the entity who gets subscribed here.
    'model' => TenFour\Models\Organization::class,
    'webhook' => [
        'username' => env('EMERGENCY_ALERTS_WEBHOOK_USERNAME', 'emergency-alerts'),
        'password' => env('EMERGENCY_ALERTS_WEBHOOK_PASSWORD', 'westgate')
    ]

];
