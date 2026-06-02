
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
        'driver'  => env('WHATSAPP_DRIVER', 'evolution'),
        'api_url' => env('WHATSAPP_API_URL', ''),
        'token'   => env('WHATSAPP_API_TOKEN', ''),
    ],

    'evolution' => [
        'url'      => env('EVOLUTION_API_URL', ''),
        'key'      => env('EVOLUTION_API_KEY', ''),
        'instance' => env('EVOLUTION_API_INSTANCE', ''),
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

    'pay4all' => [
        'client_id'           => env('PAY4ALL_CLIENT_ID', '04278597'),
        'client_secret'       => env('PAY4ALL_CLIENT_SECRET', ''),
        'resource'            => env('PAY4ALL_RESOURCE', ''),
        'payment_method'      => env('PAY4ALL_PAYMENT_METHOD', ''),
        'notification_token'  => env('PAY4ALL_NOTIFICATION_TOKEN', ''),
        'merchant_identifier' => env('PAY4ALL_MERCHANT_IDENTIFIER', ''),
        'api_key'             => env('PAY4ALL_API_KEY', ''),
        'token_url'           => env('PAY4ALL_TOKEN_URL', 'https://login.microsoftonline.com/appypaydev.onmicrosoft.com/oauth2/token'),
        'api_url'             => env('PAY4ALL_API_URL', 'https://gwy-api-tst.appypay.co.ao/v2.0'),
        'webhook_url'         => env('PAY4ALL_WEBHOOK_URL', ''),
        'test_mode'           => env('PAY4ALL_TEST_MODE', true),
    ],

    // Integração MikroTik (RouterOS API)
    'mikrotik' => [
        'host'            => env('MIKROTIK_HOST', ''),
        'port'            => env('MIKROTIK_PORT', 8728),
        'username'        => env('MIKROTIK_USER', 'admin'),
        'password'        => env('MIKROTIK_PASSWORD', ''),
        'user_prefix'     => env('MIKROTIK_USER_PREFIX', ''),
        'default_profile' => env('MIKROTIK_DEFAULT_PROFILE', 'default'),
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
