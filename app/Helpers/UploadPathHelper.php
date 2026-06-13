<?php

declare(strict_types=1);

namespace App\Helpers;

class UploadPathHelper
{
    public static function currentDatePath(): string
    {
        return date('Y') . DIRECTORY_SEPARATOR . date('m');
    }

    public static function normalize(string $path): string
    {
        return trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public static function toPublicPath(string $relativePath): string
    {
        return 'public/uploads/' . str_replace('\\', '/', trim($relativePath, '/\\'));
    }

    public static function join(string ...$segments): string
    {
        $filtered = [];
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            $filtered[] = trim($segment, '/\\');
        }

        return implode(DIRECTORY_SEPARATOR, $filtered);
    }
}
