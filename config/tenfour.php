<?php
$getNexmoFrom = function( $region ) {
  return explode('|', env('NEXMO_SRC_ADDR_' . $region, env('NEXMO_SRC_ADDR', 'tenfour')));
};

return [
    // The app's credentials for the API
    'app_client' => [
        'client_id' => env('RC_CLIENT_ID', '1'),
        'client_secret' => env('RC_CLIENT_SECRET', 'T7913s89oGgJ478J73MRHoO2gcRRLQ'),
    ],

    'domain' => env('APP_DOMAIN', 'tenfour.org'),

    'reserved_words' => ['app', 'rollcall', 'tenfour', 'www', 'staging', 'admin', 'dev', 'pwa', 'legacy'],

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
            'DE' => [
                'driver' => 'nexmo',
                'from' => $getNexmoFrom('DE')
            ],
            'AU' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'AT' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'BE' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'DK' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'FI' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'HK' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'LS' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'NA' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'NL' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'NO' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'PH' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'ZA' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'ES' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'SZ' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'SE' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'CH' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'TH' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],

            // These regions have NO local number available:

            'LB' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'IQ' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'JO' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],
            'SY' => [
                'driver' => 'bulksms',
                'from' => 'REPLIABLE'
            ],

            'default' => [
                'driver' => 'nexmo',
                'from' => explode('|', env('NEXMO_SRC_ADDR', 'tenfour'))
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
