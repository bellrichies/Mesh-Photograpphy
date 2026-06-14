<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;

class ServiceService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly Service $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(): array
    {
        return array_map([$this, 'formatSummary'], $this->model->findPublished());
    }

    public function getBySlug(string $slug): ?array
    {
        $row = $this->model->findBySlug($slug);
        if (!$row) return null;

        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

        return array_merge($this->formatSummary($row), [
            'description' => $row['description'],
            'seo'         => [
                'meta_title'       => $row['seo_title'] ?? ($row['title'] . ' | Mesh Photography'),
                'meta_description' => $row['seo_description'] ?? $row['short_desc'],
                'og_title'         => $row['seo_title'] ?? $row['title'],
                'og_description'   => $row['seo_description'] ?? $row['short_desc'],
                'og_image_url'     => !empty($row['cover_path']) ? $this->fmt->formatCover($row)['url'] : null,
                'canonical_url'    => $appUrl . '/services/' . $row['slug'],
                'robots'           => 'index, follow',
                'schema_markup'    => null,
            ],
        ]);
    }

    private function formatSummary(array $row): array
    {
        return [
            'id'                => (int) $row['id'],
            'title'             => $row['title'],
            'slug'              => $row['slug'],
            'short_description' => $row['short_desc'] ?? null,
            'price_display'     => $row['price_label'] ?? null,
            'cover'             => !empty($row['cover_path']) ? $this->fmt->formatCover($row) : null,
            'sort_order'        => (int) $row['sort_order'],
            'status'            => $row['is_published'] ? 'published' : 'draft',
        ];
    }
}
