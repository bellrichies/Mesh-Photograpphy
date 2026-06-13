<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;

class PageService
{
    public function __construct(private readonly Page $pages)
    {
    }

    public function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->pages->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function normalizePublishedAt(string $status, string $publishedAt): ?string
    {
        $publishedAt = trim($publishedAt);

        if ($status !== 'published') {
            return null;
        }

        if ($publishedAt === '') {
            return date('Y-m-d H:i:s');
        }

        $timestamp = strtotime($publishedAt);
        if ($timestamp === false) {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}