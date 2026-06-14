<?php

declare(strict_types=1);

return [
    'roles' => [
        'super-admin',
        'editor',
        'content-manager',
    ],

    'permissions' => [
        'manage-users',
        'manage-settings',
        'manage-media',
        'manage-pages',
        'manage-galleries',
        'manage-blog',
        'manage-testimonials',
        'manage-inquiries',
        'manage-services',
    ],

    'role_permissions' => [
        'super-admin'     => [
            'manage-users', 'manage-settings', 'manage-media', 'manage-pages',
            'manage-galleries', 'manage-blog', 'manage-testimonials',
            'manage-inquiries', 'manage-services',
        ],
        'editor'          => [
            'manage-media', 'manage-galleries', 'manage-blog',
            'manage-testimonials', 'manage-services',
        ],
        'content-manager' => [
            'manage-media', 'manage-pages', 'manage-blog',
        ],
    ],
];
