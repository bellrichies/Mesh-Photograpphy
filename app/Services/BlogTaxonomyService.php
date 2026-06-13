<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BlogRepository;

class BlogTaxonomyService
{
    public function __construct(private readonly BlogRepository $blogRepository)
    {
    }

    public function generateUniqueCategorySlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->blogRepository->categorySlugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function generateUniqueTagSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->blogRepository->tagSlugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}