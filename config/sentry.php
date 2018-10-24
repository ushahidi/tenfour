<?php

return array(
    'dsn' => env('SENTRY_DSN'),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    'processors' => [
        'Raven_Processor_SanitizeHttpHeadersProcessor',
        'Raven_Processor_SanitizeDataProcessor'
    ],
    'processorOptions' => [
        'Raven_Processor_SanitizeDataProcessor' => [
            // @codingStandardsIgnoreLine
            'fields_re' => '/(authorization|password|passwd|secret|password_confirmation|card_number|auth_pw|authToken|api_key|client_secret)/i',
        ],
        'Raven_Processor_SanitizeHttpHeadersProcessor' => [
            'sanitize_http_headers' => [
                'Authorization',
                'Proxy-Authorization',
                'X-Csrf-Token',
                'X-CSRFToken',
                'X-XSRF-TOKEN',
                'X-Ushahidi-Signature',
            ]
        ]
    ]
);
