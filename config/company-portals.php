<?php

return [
    'invoice_workbook_admin_password' => env('INVOICE_WORKBOOK_ADMIN_PASSWORD', 'HydroxAdmin'),

    'profile' => [
        'label' => 'Company Profile',
        'description' => 'Hydrox public website',
        'url' => env('PROFILE_PORTAL_URL', 'https://hydrox.au'),
        'icon' => 'profile',
    ],
    'info' => [
        'label' => 'Company Info',
        'description' => 'Hydrox services and booking information',
        'url' => env('INFO_PORTAL_URL', 'https://hydrox.au/services'),
        'icon' => 'info',
    ],
];
