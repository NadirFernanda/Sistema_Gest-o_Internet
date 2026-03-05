
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'twilio'),
        'api_url' => env('WHATSAPP_API_URL', 'https://api.ultramsg.com/instanceXXXX'),
        'token' => env('WHATSAPP_API_TOKEN', ''),
    ],

    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID', ''),
        'token' => env('TWILIO_AUTH_TOKEN', ''),
        'from' => env('TWILIO_WHATSAPP_FROM', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
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

    // Integração com a loja (Storefront)
    // Usado pelo dashboard do SG para montar o link do painel da loja
    'sg' => [
        // URL pública da loja; por padrão, usa LOJA_URL do .env
        'loja_url' => env('LOJA_URL', 'http://127.0.0.1:8001'),

        // Token partilhado com a loja para acesso ao /admin
        // Deve ser igual ao SG_LOJA_ADMIN_TOKEN definido na .env da loja
        'admin_token' => env('SG_LOJA_ADMIN_TOKEN', ''),
    ],

];
