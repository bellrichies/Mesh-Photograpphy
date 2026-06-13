<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Mesh Photography'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'settings_cache_ttl' => (int) env('APP_SETTINGS_CACHE_TTL', 300),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'admin_path' => env('ADMIN_PATH', '/admin'),
    'csrf_token_name' => env('CSRF_TOKEN_NAME', '_token'),
];
