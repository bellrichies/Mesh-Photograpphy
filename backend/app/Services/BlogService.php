<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;

class BlogService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly BlogPost $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(array $filters, int $page, int $perPage): array
    {
        $items = $this->model->findPublished($filters, $page, $perPage);
        $total = $this->model->countPublished($filters);

        return [
            'items' => array_map([$this, 'formatSummary'], $items),
            'total' => $total,
        ];
    }

    public function getBySlug(string $slug): ?array
    {
        $row = $this->model->findBySlug($slug);
        if (!$row) return null;

        $tags    = $this->model->getTagsForPost((int) $row['id']);
        $categoryId = !empty($row['cat_id']) ? (int) $row['cat_id'] : null;
        $related = $this->model->getRelated((int) $row['id'], $categoryId);
        $appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');

        return array_merge($this->formatSummary($row), [
            'body'       => $row['body'] ?? '',
            'author'     => $row['author_first'] ? [
                'id'   => (int) $row['author_id'],
                'name' => trim($row['author_first'] . ' ' . $row['author_last']),
            ] : null,
            'tags'       => array_map(fn ($t) => [
                'id'         => (int) $t['id'],
                'name'       => $t['name'],
                'slug'       => $t['slug'],
                'post_count' => null,
            ], $tags),
            'seo'        => [
                'meta_title'       => $row['seo_title'] ?? ($row['title'] . ' | Mesh Photography'),
                'meta_description' => $row['seo_description'] ?? $row['excerpt'],
                'og_title'         => $row['seo_title'] ?? $row['title'],
                'og_description'   => $row['seo_description'] ?? $row['excerpt'],
                'og_image_url'     => $row['cover_path'] ? $this->fmt->formatCover($row)['url'] : null,
                'canonical_url'    => $appUrl . '/blog/' . $row['slug'],
                'robots'           => 'index, follow',
                'schema_markup'    => null,
            ],
            'related_posts' => array_map([$this, 'formatSummary'], $related),
            'prev_post'     => $this->model->getPrev((int) $row['id']),
            'next_post'     => $this->model->getNext((int) $row['id']),
        ]);
    }

    public function getCategories(): array
    {
        return array_map(fn (array $r) => [
            'id'         => (int) $r['id'],
            'name'       => $r['name'],
            'slug'       => $r['slug'],
            'post_count' => (int) $r['post_count'],
        ], $this->model->getCategories());
    }

    public function getCategoryBySlug(string $slug): ?array
    {
        return $this->model->findCategoryBySlug($slug);
    }

    public function getAllTags(): array
    {
        return array_map(fn (array $r) => [
            'id'         => (int) $r['id'],
            'name'       => $r['name'],
            'slug'       => $r['slug'],
            'post_count' => (int) $r['post_count'],
        ], $this->model->getTags());
    }

    public function getTagBySlug(string $slug): ?array
    {
        return $this->model->findTagBySlug($slug);
    }

    private function formatSummary(array $row): array
    {
        $cover = !empty($row['cover_path']) ? $this->fmt->formatCover($row) : null;

        $status = 'draft';
        if (!empty($row['is_published'])) $status = 'published';

        $categoryId = !empty($row['cat_id']) ? (int) $row['cat_id'] : null;

        return [
            'id'           => (int) $row['id'],
            'title'        => $row['title'],
            'slug'         => $row['slug'],
            'excerpt'      => $row['excerpt'] ?? null,
            'cover'        => $cover,
            'published_at' => $row['published_at'] ?? null,
            'status'       => $status,
            'categories'   => $categoryId ? [[
                'id'   => $categoryId,
                'name' => $row['cat_name'] ?? '',
                'slug' => $row['cat_slug'] ?? '',
            ]] : [],
            'tags'         => [],
        ];
    }
}
