<?php

declare(strict_types=1);

return [
    'name'       => env('APP_NAME', 'Mesh Photography'),
    'env'        => env('APP_ENV', 'local'),
    'debug'      => (bool) env('APP_DEBUG', false),
    'url'        => env('APP_URL', 'http://localhost:8000'),
    'timezone'   => env('APP_TIMEZONE', 'UTC'),
    'admin_path' => env('ADMIN_PATH', '/cms'),
    'settings_cache_ttl' => (int) env('APP_SETTINGS_CACHE_TTL', 300),
];
