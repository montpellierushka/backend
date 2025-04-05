<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://*.telegram.org',
        'https://*.t.me',
        env('APP_URL'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'X-Telegram-Init-Data',
        'Content-Type',
        'X-Requested-With',
        'Accept',
        'Authorization',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
]; 