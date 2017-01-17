<?php
return [
		// The app's credentials for the API
		'app_client' => [
			'client_id' => env('RC_CLIENT_ID', 'webapp'),
			'client_secret' => env('RC_CLIENT_SECRET', 'T7913s89oGgJ478J73MRHoO2gcRRLQ'),
		],

		'domain' => env('APP_DOMAIN', 'rollcall.io'),

    'messaging' => [
        'incoming_driver' => env('RC_MESSAGING_INCOMING', 'aws-ses-sns'),

        // SMS drivers mapped by region code
        'sms_providers' => [
            'KE' => [
                'driver' => 'africastalking',
                'from' => '20880'
            ],
            'US' => [
                'driver' => 'nexmo',
                'from' => null
            ],
            'default' => [
                'driver' => 'africastalking',
                'from' => '20880'
            ],
        ],
    ]
];
