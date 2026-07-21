<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    
    'facebook' => [
        'client_id' => env('FB_CLIENT_ID'),
        'client_secret' => env('FB_CLIENT_SECRET'),
        'redirect' => env('FB_REDIRECT'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),
        'map_key'       => env('GOOGLE_MAP_API_KEY_DIRECTIONS'),
    ],

    'osrm' => [
        'url' => env('OSRM_URL', 'http://router.project-osrm.org'),
    ],

    // ─── PicMe AI : Groq (Llama 3) ───────────────────────────────────────────
    'groq' => [
        'enabled'  => env('PICME_AI_ENABLED', true),
        'api_key'  => env('GROQ_API_KEY', ''),
        'model'    => env('GROQ_MODEL', 'llama-3.1-8b-instant'), // Legacy
        'models'   => explode(',', env('GROQ_MODELS', 'llama-3.3-70b-versatile,llama-3.1-8b-instant,mixtral-8x7b-32768,gemma2-9b-it')), // Failover
        'cooldown' => env('GROQ_MODEL_COOLDOWN', 300), // 5 mins
        'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
        'timeout'  => env('GROQ_TIMEOUT', 60),
    ],

    // ─── Evolution API ───────────────────────────────────────────────────────
    'evolution' => [
        'url' => env('EVOLUTION_API_URL', 'http://evolution-api-service:8080'),
        'key' => env('EVOLUTION_API_KEY', 'picme225-evolution-secret-key'),
        'instance' => env('EVOLUTION_INSTANCE', 'picme_whatsapp'),
    ],

    // ─── Social Media Auto-Post ─────────────────────────────────────────────
    'facebook_page' => [
        'page_id'      => env('FACEBOOK_PAGE_ID', ''),
        'access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN', ''),
    ],

    'tiktok' => [
        'client_key'    => env('TIKTOK_CLIENT_KEY', ''),
        'client_secret' => env('TIKTOK_CLIENT_SECRET', ''),
        'access_token'  => env('TIKTOK_ACCESS_TOKEN', ''),
    ],

    'mapbox' => [
        'key' => env('MAPBOX_API_KEY'),
    ],

    // ─── Payment Gateway Toggle ───────────────────────────────────────────
    'payment_gateway' => env('PAYMENT_GATEWAY', 'MANUAL'),

    'cinetpay' => [
        'site_id' => env('CINETPAY_SITE_ID', ''),
        'apikey'  => env('CINETPAY_API_KEY', ''),
    ],

];
