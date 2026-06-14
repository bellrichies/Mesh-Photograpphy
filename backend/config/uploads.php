<?php

declare(strict_types=1);

return [
    'max_file_size_mb'   => (int) env('UPLOAD_MAX_FILE_SIZE_MB', 10),
    'max_image_width'    => (int) env('UPLOAD_MAX_IMAGE_WIDTH', 6000),
    'max_image_height'   => (int) env('UPLOAD_MAX_IMAGE_HEIGHT', 6000),
    'thumb_width'        => 640,
    'thumb_quality'      => 82,

    'allowed_mimes' => [
        'image'    => array_map('trim', explode(',', env('UPLOAD_ALLOWED_IMAGE_MIMES', 'image/jpeg,image/png,image/webp,image/gif'))),
        'document' => array_map('trim', explode(',', env('UPLOAD_ALLOWED_DOCUMENT_MIMES', 'application/pdf'))),
        'video'    => array_map('trim', explode(',', env('UPLOAD_ALLOWED_VIDEO_MIMES', 'video/mp4,video/quicktime'))),
    ],

    'allowed_extensions' => [
        'image'    => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'document' => ['pdf'],
        'video'    => ['mp4', 'mov'],
    ],

    'blocked_extensions' => ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'exe', 'sh', 'bat', 'cmd', 'ps1'],

    'upload_path' => 'uploads',
];
