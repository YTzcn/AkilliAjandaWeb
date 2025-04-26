<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | Varsayılan LLM sağlayıcısını belirtir.
    | Kullanılabilir seçenekler: 'gemini', 'openai', 'openrouter'
    |
    */
    'default_provider' => env('LLM_DEFAULT_PROVIDER', 'gemini'),
    
    /*
    |--------------------------------------------------------------------------
    | Model Settings
    |--------------------------------------------------------------------------
    |
    | Her sağlayıcı için varsayılan model ayarları
    |
    */
    'models' => [
        'gemini' => [
            'default' => env('GEMINI_DEFAULT_MODEL', 'gemini-1.5-pro'),
        ],
        'openai' => [
            'default' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
        ],
        'openrouter' => [
            'default' => env('OPENROUTER_DEFAULT_MODEL', 'anthropic/claude-3-opus'),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Prompt Settings
    |--------------------------------------------------------------------------
    |
    | Prompt şablonları ve ayarları
    |
    */
    'prompts' => [
        'system_message' => 'Sen bir Akıllı Ajanda Uygulamasının Asistanısın. Kullanıcılar seninle konuşarak ajanda üzerindeki işlemlerini yapabilirler.',
    ],
]; 