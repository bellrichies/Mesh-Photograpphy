<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Page
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM pages WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM pages WHERE slug = :slug AND deleted_at IS NULL LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM pages WHERE slug = :slug AND status = :status AND deleted_at IS NULL LIMIT 1',
            [
                'slug' => $slug,
                'status' => 'published',
            ]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', string $status = '', string $template = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR slug LIKE :query OR excerpt LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($template !== '') {
            $whereParts[] = 'template = :template';
            $params['template'] = $template;
        }

        $sql = sprintf(
            'SELECT * FROM pages WHERE %s ORDER BY is_system DESC, sort_order ASC, updated_at DESC LIMIT %d OFFSET %d',
            implode(' AND ', $whereParts),
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', string $status = '', string $template = ''): int
    {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR slug LIKE :query OR excerpt LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($template !== '') {
            $whereParts[] = 'template = :template';
            $params['template'] = $template;
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM pages WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parentOptions(?int $excludeId = null): array
    {
        $sql = 'SELECT id, title, slug FROM pages WHERE deleted_at IS NULL';
        $params = [];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' ORDER BY is_system DESC, title ASC';

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM pages WHERE deleted_at IS NULL')->fetchColumn();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM pages WHERE slug = :slug AND deleted_at IS NULL';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO pages (
                title, slug, template, status, excerpt, body, featured_media_id, parent_id, sort_order,
                is_system, published_at, created_by, updated_by, created_at, updated_at
            ) VALUES (
                :title, :slug, :template, :status, :excerpt, :body, :featured_media_id, :parent_id, :sort_order,
                :is_system, :published_at, :created_by, :updated_by, NOW(), NOW()
            )',
            [
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'template' => $data['template'] ?? 'default',
                'status' => $data['status'] ?? 'draft',
                'excerpt' => $data['excerpt'] ?? null,
                'body' => $data['body'] ?? null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_system' => $data['is_system'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $this->database->query(
            'UPDATE pages SET
                title = :title,
                slug = :slug,
                template = :template,
                status = :status,
                excerpt = :excerpt,
                body = :body,
                featured_media_id = :featured_media_id,
                parent_id = :parent_id,
                sort_order = :sort_order,
                is_system = :is_system,
                published_at = :published_at,
                updated_by = :updated_by,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'template' => $data['template'] ?? 'default',
                'status' => $data['status'] ?? 'draft',
                'excerpt' => $data['excerpt'] ?? null,
                'body' => $data['body'] ?? null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_system' => $data['is_system'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE pages SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublished(): array
    {
        $rows = $this->database->query(
            'SELECT * FROM pages WHERE status = "published" AND deleted_at IS NULL ORDER BY sort_order ASC, id ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sections(int $pageId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM page_sections WHERE page_id = :page_id ORDER BY sort_order ASC, id ASC',
            ['page_id' => $pageId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}
