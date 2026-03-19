<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Delivery Channels
    |--------------------------------------------------------------------------
    |
    | Configure the primary and fallback channels for OTP delivery.
    | Available channels: 'email', 'sms', 'both'
    |
    */
    'channels' => [
        'primary' => env('OTP_PRIMARY_CHANNEL', 'email'),
        'fallback' => env('OTP_FALLBACK_CHANNEL', null),
        'delivery_order' => ['email', 'sms'], // Order of delivery attempts
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Code Settings
    |--------------------------------------------------------------------------
    |
    | Configure the OTP code generation settings.
    |
    */
    'code' => [
        'length' => env('OTP_LENGTH', 6),
        'expiry' => env('OTP_EXPIRY', 10), // minutes
        'type' => env('OTP_TYPE', 'numeric'), // numeric, alpha, alphanumeric
        'case_sensitive' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Codes
    |--------------------------------------------------------------------------
    |
    | Settings for backup/recovery codes.
    |
    */
    'backup_codes' => [
        'enabled' => true,
        'count' => 10,
        'length' => 8,
        'format' => 'alphanumeric', // numeric, alpha, alphanumeric
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for OTP requests.
    |
    */
    'rate_limiting' => [
        'enabled' => true,
        'max_send_attempts' => 5,
        'max_verify_attempts' => 10,
        'decay_minutes' => 10,
        'lockout_minutes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | SMS provider settings for OTP delivery.
    |
    */
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'),
        'providers' => [
            'vonage' => [
                'key' => env('VONAGE_KEY'),
                'secret' => env('VONAGE_SECRET'),
                'from' => env('VONAGE_FROM', 'OTP Auth'),
            ],
            'twilio' => [
                'sid' => env('TWILIO_SID'),
                'token' => env('TWILIO_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
            'log' => [
                'channel' => 'daily',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | Email settings for OTP delivery.
    |
    */
    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'OTP Auth'),
        'subject' => 'Your verification code',
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    |
    | Customize OTP messages.
    |
    */
    'messages' => [
        'sms' => 'Your verification code is: :code. Valid for :minutes minutes.',
        'email_subject' => 'Your verification code',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | OTP logging settings.
    |
    */
    'logging' => [
        'enabled' => env('OTP_LOG_ENABLED', true),
        'channel' => env('OTP_LOG_CHANNEL', 'daily'),
        'log_codes' => env('OTP_LOG_CODES', false), // Only enable in development
    ],
];
