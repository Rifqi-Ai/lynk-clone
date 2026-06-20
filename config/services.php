<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ───── Google OAuth (via Socialite) ─────
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    // ───── Duitku Payment Gateway ─────
    // Set DUITKU_PRODUCTION=true and provide real credentials to go live.
    // In dev, the service returns a mock URL (no real payment processed).
    'duitku' => [
        'api_key' => env('DUITKU_API_KEY', 'dev_mock_key'),
        'merchant_code' => env('DUITKU_MERCHANT_CODE', 'dev_mock_merchant'),
        'production' => env('DUITKU_PRODUCTION', false),
        'callback_url' => env('DUITKU_CALLBACK_URL', env('APP_URL') . '/payment/callback'),
        'return_url' => env('DUITKU_RETURN_URL', env('APP_URL') . '/payment/success'),
        'default_method' => env('DUITKU_DEFAULT_METHOD', 'VC'), // Virtual Account
    ],

    // ───── WhatsApp Notifications ─────
    // Supported providers: 'log' (dev), 'wablas', 'fonnte'
    // Set WHATSAPP_PRODUCTION=true + real keys to enable actual sending
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'log'), // log|wablas|fonnte
        'api_key' => env('WHATSAPP_API_KEY', ''),
        'sender' => env('WHATSAPP_SENDER', ''), // your registered sender number
        'production' => env('WHATSAPP_PRODUCTION', false),
    ],

];