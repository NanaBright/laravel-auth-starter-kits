<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TOTP Window
    |--------------------------------------------------------------------------
    |
    | The number of 30-second windows to check before and after the current
    | time when validating TOTP codes. A value of 1 allows codes from 30
    | seconds before and after the current time (90 second total window).
    |
    */
    'window' => env('TWO_FACTOR_WINDOW', 1),

    /*
    |--------------------------------------------------------------------------
    | Recovery Codes Count
    |--------------------------------------------------------------------------
    |
    | The number of recovery codes to generate when enabling 2FA.
    |
    */
    'recovery_codes_count' => env('TWO_FACTOR_RECOVERY_CODES_COUNT', 8),

    /*
    |--------------------------------------------------------------------------
    | Remember Device
    |--------------------------------------------------------------------------
    |
    | How many days to remember a trusted device before requiring 2FA again.
    | Set to 0 to always require 2FA.
    |
    */
    'remember_device_days' => env('TWO_FACTOR_REMEMBER_DAYS', 30),
];
