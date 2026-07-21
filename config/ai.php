<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les services d'IA (OpenAI, etc.)
    |
    */

    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
    
    'default_model' => env('AI_DEFAULT_MODEL', 'gpt-4'),
    'temperature' => env('AI_TEMPERATURE', 0.7),
    'max_tokens' => env('AI_MAX_TOKENS', 1000),
];

