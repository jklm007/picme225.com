<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile Money Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les intégrations Mobile Money
    |
    */

    'providers' => [
        'orange' => [
            'api_url' => env('ORANGE_MONEY_API_URL', 'https://api.orange.com'),
            'client_id' => env('ORANGE_MONEY_CLIENT_ID'),
            'client_secret' => env('ORANGE_MONEY_CLIENT_SECRET'),
            'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY'),
            'webhook_secret' => env('ORANGE_MONEY_WEBHOOK_SECRET'),
        ],

        'mtn' => [
            'api_url' => env('MTN_MOMO_API_URL', 'https://sandbox.momodeveloper.mtn.com'),
            'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
            'api_user' => env('MTN_MOMO_API_USER'),
            'api_key' => env('MTN_MOMO_API_KEY'),
            'webhook_secret' => env('MTN_MOMO_WEBHOOK_SECRET'),
        ],

        'moov' => [
            'api_url' => env('MOOV_MONEY_API_URL', 'https://api.moovmoney.com'),
            'api_key' => env('MOOV_MONEY_API_KEY'),
            'merchant_id' => env('MOOV_MONEY_MERCHANT_ID'),
            'webhook_secret' => env('MOOV_MONEY_WEBHOOK_SECRET'),
        ],

        'cinetpay' => [
            'site_id' => env('CINETPAY_SITE_ID'),
            'api_key' => env('CINETPAY_API_KEY'),
            'api_url' => env('CINETPAY_API_URL', 'https://api-checkout.cinetpay.com/v2/payment'),
        ],
    ],

    'default_provider' => env('MOBILE_MONEY_DEFAULT_PROVIDER', 'orange'),
];

