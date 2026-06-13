<?php

declare(strict_types=1);

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'files_path' => env('SESSION_FILE_PATH', 'storage/sessions'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'idle_timeout' => (int) env('SESSION_IDLE_TIMEOUT', 60),
    'path' => env('SESSION_PATH', '/'),
    'secure' => (bool) env('SESSION_SECURE', false),
    'http_only' => (bool) env('SESSION_HTTP_ONLY', true),
    'same_site' => env('SESSION_SAME_SITE', 'Lax'),
    'fingerprint' => [
        'user_agent' => (bool) env('SESSION_FINGERPRINT_USER_AGENT', true),
        'accept_language' => (bool) env('SESSION_FINGERPRINT_ACCEPT_LANGUAGE', true),
        'ip' => (bool) env('SESSION_FINGERPRINT_IP', false),
    ],
];
