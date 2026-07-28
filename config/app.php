<?php

return [
    'name' => env('APP_NAME', 'Hydrox Portal'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Australia/Melbourne'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_AU'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
    'maintenance' => ['driver' => env('APP_MAINTENANCE_DRIVER', 'file'), 'store' => env('APP_MAINTENANCE_STORE', 'database')],
    'company_name' => env('COMPANY_NAME', 'Hydrox Facility Management'),
    'company_email' => env('COMPANY_EMAIL', 'admin@hydrox.au'),
    'company_phone' => env('COMPANY_PHONE', '0418 222 477'),
    'company_address' => env('COMPANY_ADDRESS', ''),
];
