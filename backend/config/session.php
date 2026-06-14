<?php

declare(strict_types=1);

return [
    'driver'       => env('SESSION_DRIVER', 'file'),
    'lifetime'     => (int) env('SESSION_LIFETIME', 120),
    'secure'       => (bool) env('SESSION_SECURE', false),
    'http_only'    => (bool) env('SESSION_HTTP_ONLY', true),
    'same_site'    => env('SESSION_SAME_SITE', 'Lax'),
    'idle_timeout' => (int) env('SESSION_IDLE_TIMEOUT', 60),
    'files_path'   => env('SESSION_FILES_PATH', 'storage/sessions'),
    'path'         => '/',
    'cookie_name'  => 'mesh_session',
];
