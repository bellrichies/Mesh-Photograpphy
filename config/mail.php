<?php

declare(strict_types=1);

return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'enabled' => (bool) env('MAIL_ENABLED', true),
    'mailers' => [
        'smtp' => [
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => (int) env('MAIL_PORT', 1025),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', null),
            'timeout' => (int) env('MAIL_TIMEOUT', 30),
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Mesh Photography')),
    ],
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', ''),
        'name' => env('MAIL_REPLY_TO_NAME', ''),
    ],
];
