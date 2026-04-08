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

    // Sistema de gestão (SG) principal - API em PROJECTO
    'sg' => [
        'url'                 => env('SG_URL', 'http://127.0.0.1:8000'),
        'client_id'           => env('SG_CLIENT_ID'),
        'client_secret'       => env('SG_CLIENT_SECRET'),
        // Token partilhado para acesso ao painel da loja a partir do SG
        'admin_token'         => env('SG_LOJA_ADMIN_TOKEN'),
        // Token de API estático para endpoints protegidos do SG (lookupCliente, syncJanela, activeClients)
        'api_token'           => env('SG_API_TOKEN'),
        // Endpoint do SG para contagem de clientes activos (stat bar da homepage)
        'active_clients_path' => env('SG_ACTIVE_CLIENTS_PATH', '/api/stats/active-clients'),
    ],

    // Gateway de pagamentos (webhooks de confirmação de planos familiares)
    'payment' => [
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    ],

];
