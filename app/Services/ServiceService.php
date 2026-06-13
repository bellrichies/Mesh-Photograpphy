<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;

class ServiceService
{
    public function __construct(private readonly Service $services)
    {
    }

    public function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->services->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}