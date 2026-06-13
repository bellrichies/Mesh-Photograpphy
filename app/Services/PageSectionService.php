<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PageSection;

class PageSectionService
{
    /** @var array<int, string> */
    public const TYPES = [
        'hero',
        'brand-intro',
        'intro',
        'split-content',
        'featured-galleries',
        'services-teaser',
        'testimonials-strip',
        'featured-blog-posts',
        'cta-banner',
        'story-block',
        'faq',
    ];

    public function __construct(private readonly PageSection $sections)
    {
    }

    public function uniqueSectionKey(int $pageId, string $source, ?int $ignoreId = null): string
    {
        $baseKey = slugify($source);
        $key = $baseKey;
        $suffix = 2;

        while ($this->sections->keyExists($pageId, $key, $ignoreId)) {
            $key = $baseKey . '-' . $suffix;
            $suffix++;
        }

        return $key;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reorder(int $pageId, int $sectionId, string $direction): array
    {
        $items = $this->sections->byPageId($pageId);
        $currentIndex = null;

        foreach ($items as $index => $item) {
            if ((int) ($item['id'] ?? 0) === $sectionId) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null) {
            return $items;
        }

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
        if (! isset($items[$swapIndex])) {
            return $items;
        }

        $current = $items[$currentIndex];
        $swap = $items[$swapIndex];

        $this->sections->setSortOrder((int) $current['id'], (int) ($swap['sort_order'] ?? $swapIndex + 1));
        $this->sections->setSortOrder((int) $swap['id'], (int) ($current['sort_order'] ?? $currentIndex + 1));

        return $this->sections->byPageId($pageId);
    }

    public function renderPartialForType(string $type): string
    {
        $normalized = trim($type);

        return match ($normalized) {
            'hero' => 'web/sections/hero',
            'brand-intro' => 'web/sections/brand-intro',
            'intro' => 'web/sections/intro',
            'split-content' => 'web/sections/split-content',
            'featured-galleries' => 'web/sections/featured-galleries',
            'services-teaser' => 'web/sections/services-teaser',
            'testimonials-strip' => 'web/sections/testimonials-strip',
            'featured-blog-posts' => 'web/sections/featured-blog-posts',
            'cta-banner' => 'web/sections/cta-banner',
            'story-block' => 'web/sections/story-block',
            'faq' => 'web/sections/faq',
            default => 'web/sections/generic',
        };
    }
}