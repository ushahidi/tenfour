<?php
$getNexmoFrom = function( $region ) {
  return explode('|', env('NEXMO_SRC_ADDR_' . $region, env('NEXMO_SRC_ADDR', 'rollcall')));
};

return [
    // The app's credentials for the API
    'app_client' => [
        'client_id' => env('RC_CLIENT_ID', 'webapp'),
        'client_secret' => env('RC_CLIENT_SECRET', 'T7913s89oGgJ478J73MRHoO2gcRRLQ'),
    ],

    'domain' => env('APP_DOMAIN', 'rollcall.io'),

    'reserved_words' => ['app', 'rollcall', 'www', 'staging', 'admin'],

    'messaging' => [
        'incoming_driver' => env('RC_MESSAGING_INCOMING', 'aws-ses-sns'),

        // SMS drivers mapped by region code
        'sms_providers' => [
            'KE' => [
                'driver' => 'africastalking',
                'from' => explode('|', env('AFRICASTALKING_SRC_ADDR', '20880|20881'))
            ],
            'US' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('US')
            ],
            'NZ' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('NZ')
            ],
            'CA' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('CA')
            ],
            'IE' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('IE')
            ],
            'GB' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('UK')
            ],
            'FR' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('FR')
            ],
            'HU' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('HU')
            ],
            'default' => [
                'driver' => 'nexmo',
                'from' => explode('|', env('NEXMO_SRC_ADDR', 'rollcall'))
            ],
        ],

        // Nexmo security secret for signed MOs
        'nexmo_security_secret' => env('NEXMO_SECURITY_SECRET', false),

        // Whether or not to validate AWS notifications.
        // This should probably only be turned off in a testing environment
        'validate_sns_message' => env('VALIDATE_SNS_MESSAGE', true),

        // Bounce and complaints thresholds
        'bounce_threshold' => env('BOUNCE_THRESHOLD', 3),
        'complaint_threshold' => env('COMPLAINT_THRESHOLD', 3),

        'skip_number_shuffle' => env('SKIP_NUMBER_SHUFFLE', false),
    ]
];
