<?php

declare(strict_types=1);

return [
    'allowed_origins'   => array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))
    ),
    'allowed_methods'   => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers'   => ['Content-Type', 'Authorization', 'X-Request-Id'],
    'allow_credentials' => true,
    'max_age'           => 86400,
];
