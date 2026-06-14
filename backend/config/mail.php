<?php

declare(strict_types=1);

return [
    'mailer'  => env('MAIL_MAILER', 'smtp'),
    'from'    => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@meshphoto.com'),
        'name'    => env('MAIL_FROM_NAME', 'Mesh Photography'),
    ],
    'mailers' => [
        'smtp' => [
            'host'       => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'port'       => (int) env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username'   => env('MAIL_USERNAME', ''),
            'password'   => env('MAIL_PASSWORD', ''),
        ],
    ],
];
