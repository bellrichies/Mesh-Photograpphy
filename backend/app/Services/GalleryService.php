<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gallery;

class GalleryService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly Gallery $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(array $filters, int $page, int $perPage): array
    {
        $items = $this->model->findPublished($filters);
        $total = $this->model->countPublished($filters);

        $offset = ($page - 1) * $perPage;
        $paged  = array_slice($items, $offset, $perPage);

        return [
            'items' => array_map([$this, 'formatSummary'], $paged),
            'total' => $total,
        ];
    }

    public function getBySlug(string $slug): ?array
    {
        $row = $this->model->findBySlug($slug);
        if (!$row) return null;

        $media     = $this->model->getMedia((int) $row['id']);
        $mediaCount = $this->model->countMedia((int) $row['id']);
        $appUrl    = rtrim($_ENV['APP_URL'] ?? '', '/');
        $canonical = $appUrl . '/portfolio/' . $row['slug'];

        return array_merge($this->formatSummary($row), [
            'description' => $row['description'],
            'media_count' => $mediaCount,
            'media'       => array_map([$this->fmt, 'formatMedia'], $media),
            'seo'         => [
                'meta_title'       => $row['seo_title'] ?? ($row['title'] . ' | Mesh Photography'),
                'meta_description' => $row['seo_description'] ?? $row['description'],
                'og_title'         => $row['seo_title'] ?? $row['title'],
                'og_description'   => $row['seo_description'] ?? $row['description'],
                'og_image_url'     => $this->fmt->formatCover($row) ? $this->fmt->formatCover($row)['url'] : null,
                'canonical_url'    => $canonical,
                'robots'           => 'index, follow',
                'schema_markup'    => null,
            ],
            'prev_gallery' => $this->model->getPrev((int) $row['id'], $row['category'] ?? null),
            'next_gallery' => $this->model->getNext((int) $row['id'], $row['category'] ?? null),
        ]);
    }

    public function getCategories(): array
    {
        $rows = $this->model->getCategories();
        return array_map(fn (array $r) => [
            'id'            => 0,
            'name'          => ucfirst((string) $r['name']),
            'slug'          => (string) $r['slug'],
            'gallery_count' => (int) $r['gallery_count'],
        ], $rows);
    }

    private function formatSummary(array $row): array
    {
        $cover = $this->fmt->formatCover($row);

        return [
            'id'          => (int) $row['id'],
            'title'       => $row['title'],
            'slug'        => $row['slug'],
            'description' => $row['description'] ?? null,
            'cover'       => $cover,
            'is_featured' => (bool) $row['is_featured'],
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'media_count' => 0,
            'categories'  => $row['category'] ? [
                ['id' => 0, 'name' => ucfirst((string) $row['category']), 'slug' => (string) $row['category']],
            ] : [],
            'sort_order'  => (int) $row['sort_order'],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at'],
        ];
    }
}
