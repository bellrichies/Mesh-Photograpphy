<?php

declare(strict_types=1);

return [
    'secret'         => env('JWT_SECRET'),
    'access_ttl'     => (int) env('JWT_ACCESS_TTL', 900),
    'refresh_ttl'    => (int) env('JWT_REFRESH_TTL', 604800),
    'refresh_cookie' => env('JWT_REFRESH_COOKIE', 'mesh_refresh_token'),
    'algorithm'      => 'HS256',
];
