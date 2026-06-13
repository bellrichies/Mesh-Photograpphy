<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class GalleryCategory
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM gallery_categories WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM gallery_categories WHERE slug = :slug LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM gallery_categories WHERE slug = :slug AND status = "published" LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', string $status = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(name LIKE :query OR slug LIKE :query OR description LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        $sql = sprintf(
            'SELECT * FROM gallery_categories WHERE %s ORDER BY sort_order ASC, name ASC LIMIT %d OFFSET %d',
            implode(' AND ', $whereParts),
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', string $status = ''): int
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(name LIKE :query OR slug LIKE :query OR description LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM gallery_categories WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM gallery_categories WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublished(): array
    {
        $rows = $this->database->query(
            'SELECT * FROM gallery_categories WHERE status = "published" ORDER BY sort_order ASC, name ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function publishedWithGalleryCounts(): array
    {
        $rows = $this->database->query(
            'SELECT gc.*, COUNT(DISTINCT g.id) AS gallery_count
             FROM gallery_categories gc
             LEFT JOIN gallery_category_map gcm ON gcm.category_id = gc.id
             LEFT JOIN galleries g ON g.id = gcm.gallery_id AND g.status = "published" AND g.deleted_at IS NULL
             WHERE gc.status = "published"
             GROUP BY gc.id, gc.name, gc.slug, gc.description, gc.sort_order, gc.status, gc.created_at, gc.updated_at
             ORDER BY gc.sort_order ASC, gc.name ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForAdmin(): array
    {
        $rows = $this->database->query(
            'SELECT * FROM gallery_categories ORDER BY sort_order ASC, name ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM gallery_categories'
        )->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function galleries(int $categoryId): array
    {
        $rows = $this->database->query(
            'SELECT g.*
             FROM galleries g
             INNER JOIN gallery_category_map gcm ON gcm.gallery_id = g.id
             WHERE gcm.category_id = :category_id AND g.deleted_at IS NULL
             ORDER BY g.sort_order ASC, g.published_at DESC, g.id DESC',
            ['category_id' => $categoryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO gallery_categories (
                name, slug, description, sort_order, status, created_at, updated_at
            ) VALUES (
                :name, :slug, :description, :sort_order, :status, NOW(), NOW()
            )',
            [
                'name' => $data['name'] ?? '',
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'status' => $data['status'] ?? 'draft',
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
            'UPDATE gallery_categories SET
                name = :name,
                slug = :slug,
                description = :description,
                sort_order = :sort_order,
                status = :status,
                updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'name' => $data['name'] ?? '',
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'status' => $data['status'] ?? 'draft',
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->database->query('DELETE FROM gallery_categories WHERE id = :id', ['id' => $id]);
    }
}