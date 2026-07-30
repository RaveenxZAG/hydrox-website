<?php

return [
    'hydrox_booking' => [
        'token' => env('HYDROX_BOOKING_TOKEN'),
    ],
    'microsoft_graph' => [
        'tenant_id' => env('MICROSOFT_GRAPH_TENANT_ID'),
        'client_id' => env('MICROSOFT_GRAPH_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_GRAPH_CLIENT_SECRET'),
        'sender' => env('MICROSOFT_GRAPH_SENDER', 'admin@hydrox.au'),
    ],
];
