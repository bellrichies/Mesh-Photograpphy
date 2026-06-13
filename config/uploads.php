<?php

declare(strict_types=1);

$csvToArray = static function (mixed $value): array {
    if (! is_string($value) || trim($value) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $value))));
};

return [
    'max_file_size_mb' => (int) env('UPLOAD_MAX_FILE_SIZE_MB', 10),
    'max_image_width' => (int) env('UPLOAD_MAX_IMAGE_WIDTH', 6000),
    'max_image_height' => (int) env('UPLOAD_MAX_IMAGE_HEIGHT', 6000),
    'allowed' => [
        'images' => $csvToArray(env('UPLOAD_ALLOWED_IMAGE_MIMES', '')),
        'documents' => $csvToArray(env('UPLOAD_ALLOWED_DOCUMENT_MIMES', '')),
        'videos' => $csvToArray(env('UPLOAD_ALLOWED_VIDEO_MIMES', '')),
    ],
    'paths' => [
        'base' => env('UPLOAD_BASE_PATH', 'public/uploads'),
        'images' => env('UPLOAD_IMAGES_PATH', 'public/uploads/images'),
        'documents' => env('UPLOAD_DOCUMENTS_PATH', 'public/uploads/documents'),
        'videos' => env('UPLOAD_VIDEOS_PATH', 'public/uploads/videos'),
        'variants' => env('UPLOAD_VARIANTS_PATH', 'public/uploads/variants'),
    ],
];
