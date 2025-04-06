<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://*.telegram.org',
        'https://*.t.me',
        env('APP_URL'),
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'https://montpellierushka-backend-7c0b.twc1.net'
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