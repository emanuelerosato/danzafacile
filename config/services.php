<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PayPal REST API integration.
    | Uses srmklive/paypal package for payment processing.
    |
    */

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
        'sandbox' => [
            'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
            'app_id' => env('PAYPAL_SANDBOX_APP_ID', ''),
        ],
        'live' => [
            'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
            'app_id' => env('PAYPAL_LIVE_APP_ID', ''),
        ],
        'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Sale, Authorization, Order
        'currency' => env('PAYPAL_CURRENCY', 'EUR'),
        'notify_url' => env('PAYPAL_NOTIFY_URL', ''),
        'locale' => env('PAYPAL_LOCALE', 'it_IT'),
        'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google reCAPTCHA v3 integration.
    | Used for spam protection on public event registrations.
    |
    */

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
        'version' => env('RECAPTCHA_VERSION', 'v3'), // v2 or v3
        'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5), // v3 only (0.0 to 1.0)
        'enabled' => env('RECAPTCHA_ENABLED', true),
    ],

];
