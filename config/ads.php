<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Advertising Platforms Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les plateformes publicitaires
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
    ],

    'facebook' => [
        'access_token' => env('FACEBOOK_ADS_ACCESS_TOKEN'),
        'ad_account_id' => env('FACEBOOK_ADS_ACCOUNT_ID'),
        'api_version' => env('FACEBOOK_ADS_API_VERSION', 'v18.0'),
    ],

    'tiktok' => [
        'access_token' => env('TIKTOK_ADS_ACCESS_TOKEN'),
        'advertiser_id' => env('TIKTOK_ADS_ADVERTISER_ID'),
        'api_url' => env('TIKTOK_ADS_API_URL', 'https://business-api.tiktok.com/open_api/v1.3'),
    ],
];

