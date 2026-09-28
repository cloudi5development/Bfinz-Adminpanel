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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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
    | Bfinz market-data providers
    |--------------------------------------------------------------------------
    |
    | Which adapter App\Providers\AppServiceProvider binds for each provider
    | interface (see App\Contracts\Providers). Swapping a provider is a config
    | change plus a new adapter class — nothing else in the sync/API layer
    | needs to change. See docs/BUILD_SPEC.md §13 Q2 (gold/silver provider is
    | still an open decision; "manual" is the placeholder until then).
    |
    */
    'metal_rate_provider' => env('METAL_RATE_PROVIDER', 'manual'),
    'forex_rate_provider' => env('FOREX_RATE_PROVIDER', 'frankfurter'),
    'push_sender' => env('PUSH_SENDER', 'log'),

];
