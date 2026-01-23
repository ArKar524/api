<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control which cross-origin requests are allowed. The
    | wildcard path keeps headers on every route so the SPA/mobile client can
    | reach both API and web endpoints when running on another device.
    */

    // Send CORS headers on every route (web + api)
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // Allow your LAN IP + common localhost dev hosts; tweak if you use another.
    'allowed_origins' => [
        env('APP_URL'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://192.168.100.213:5173',
        'http://192.168.100.213:9690',
        'http://192.168.100.213',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Enable if your client sends cookies / Authorization headers (Sanctum, JWT)
    'supports_credentials' => true,
];
