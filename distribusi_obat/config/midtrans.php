<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di file .env
    | Dapatkan key dari: https://dashboard.sandbox.midtrans.com
    | Settings → Access Keys
    |
    */

    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized'  => true,
    'is_3ds'        => true,

    /*
    | URL Snap:
    |   Sandbox    → https://app.sandbox.midtrans.com/snap/snap.js
    |   Production → https://app.midtrans.com/snap/snap.js
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
