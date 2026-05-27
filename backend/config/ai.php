<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "openai", "gemini"
    |
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model_summary' => env('OPENAI_MODEL_SUMMARY', 'gpt-4o-mini'),
        'model_embedding' => env('OPENAI_MODEL_EMBEDDING', 'text-embedding-3-small'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini Configuration
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model_summary' => env('GEMINI_MODEL_SUMMARY', 'gemini-2.5-flash'),
        'model_embedding' => env('GEMINI_MODEL_EMBEDDING', 'gemini-embedding-001'),
    ],
];
