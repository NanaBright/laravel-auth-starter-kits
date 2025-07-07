<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your SMS gateway settings here. You can use various methods:
    | 1. HTTP API Gateway (most common)
    | 2. SMPP Protocol (direct carrier connection)
    | 3. Email-to-SMS Gateway (for testing)
    |
    */

    'default_method' => env('SMS_METHOD', 'http_api'),

    /*
    |--------------------------------------------------------------------------
    | HTTP API Gateway Settings
    |--------------------------------------------------------------------------
    */
    'gateway_url' => env('SMS_GATEWAY_URL', 'https://api.your-sms-gateway.com/send'),
    'username' => env('SMS_USERNAME'),
    'password' => env('SMS_PASSWORD'),
    'sender_id' => env('SMS_SENDER_ID', 'YourApp'),

    /*
    |--------------------------------------------------------------------------
    | SMPP Settings (for direct carrier connection)
    |--------------------------------------------------------------------------
    */
    'smpp' => [
        'host' => env('SMPP_HOST', '127.0.0.1'),
        'port' => env('SMPP_PORT', 2775),
        'username' => env('SMPP_USERNAME'),
        'password' => env('SMPP_PASSWORD'),
        'timeout' => env('SMPP_TIMEOUT', 60000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email-to-SMS Gateway (for testing)
    |--------------------------------------------------------------------------
    */
    'email_gateway' => [
        'enabled' => env('SMS_EMAIL_GATEWAY_ENABLED', false),
        'default_carrier' => env('SMS_DEFAULT_CARRIER', 'verizon'),
        'carriers' => [
            'verizon' => '@vtext.com',
            'att' => '@txt.att.net',
            'tmobile' => '@tmomail.net',
            'sprint' => '@messaging.sprintpcs.com',
            'boost' => '@myboostmobile.com',
            'cricket' => '@sms.cricketwireless.net',
            'metropcs' => '@mymetropcs.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'expires_in_minutes' => 10,
        'resend_delay_seconds' => 60,
        'max_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_sms_per_hour' => 10,
        'max_sms_per_day' => 50,
        'max_attempts_per_phone' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_all_sms' => env('SMS_LOG_ALL', false),
    'log_failed_sms' => env('SMS_LOG_FAILED', true),
];
