<?php

return [
    'hydrox_booking' => [
        'token' => env('HYDROX_BOOKING_TOKEN'),
    ],
    'hydrox_portal' => [
        'url' => env('HYDROX_PORTAL_URL', 'https://portal.hydrox.au'),
        'token' => env('HYDROX_BOOKING_TOKEN'),
    ],
    'microsoft_graph' => [
        'tenant_id' => env('MICROSOFT_GRAPH_TENANT_ID'),
        'client_id' => env('MICROSOFT_GRAPH_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_GRAPH_CLIENT_SECRET'),
        'sender' => env('MICROSOFT_GRAPH_SENDER', 'admin@hydrox.au'),
    ],
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
    ],
];
