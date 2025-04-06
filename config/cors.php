<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'X-Telegram-Init-Data',
        'Content-Type',
        'X-Requested-With',
        'Accept',
        'Authorization',
        '*'
    ],

    'exposed_headers' => [
        'X-Telegram-Init-Data',
        'Content-Type',
        'X-Requested-With',
        'Accept',
        'Authorization'
    ],

    'max_age' => 0,

    'supports_credentials' => false,
]; 