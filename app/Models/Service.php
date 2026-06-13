<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Service
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT s.*, m.directory AS cover_directory, m.stored_name AS cover_stored_name,
                    m.alt_text AS cover_alt_text, m.title AS cover_title
             FROM services s
             LEFT JOIN media m ON m.id = s.cover_media_id AND m.deleted_at IS NULL
             WHERE s.id = :id AND s.deleted_at IS NULL
             LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT s.*, m.directory AS cover_directory, m.stored_name AS cover_stored_name,
                    m.alt_text AS cover_alt_text, m.title AS cover_title
             FROM services s
             LEFT JOIN media m ON m.id = s.cover_media_id AND m.deleted_at IS NULL
             WHERE s.slug = :slug AND s.deleted_at IS NULL
             LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT s.*, m.directory AS cover_directory, m.stored_name AS cover_stored_name,
                    m.alt_text AS cover_alt_text, m.title AS cover_title
             FROM services s
             LEFT JOIN media m ON m.id = s.cover_media_id AND m.deleted_at IS NULL
             WHERE s.slug = :slug AND s.status = "published" AND s.deleted_at IS NULL
             LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', string $status = '', string $featured = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['s.deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(s.title LIKE :query OR s.slug LIKE :query OR s.short_description LIKE :query OR s.price_display LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 's.status = :status';
            $params['status'] = $status;
        }

        if ($featured !== '') {
            $whereParts[] = 's.featured = :featured';
            $params['featured'] = (int) ($featured === '1');
        }

        $sql = sprintf(
            'SELECT s.*, m.directory AS cover_directory, m.stored_name AS cover_stored_name,
                    m.alt_text AS cover_alt_text, m.title AS cover_title
             FROM services s
             LEFT JOIN media m ON m.id = s.cover_media_id AND m.deleted_at IS NULL
             WHERE %s
             ORDER BY s.sort_order ASC, s.updated_at DESC, s.id DESC
             LIMIT %d OFFSET %d',
            implode(' AND ', $whereParts),
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', string $status = '', string $featured = ''): int
    {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR slug LIKE :query OR short_description LIKE :query OR price_display LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($featured !== '') {
            $whereParts[] = 'featured = :featured';
            $params['featured'] = (int) ($featured === '1');
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM services WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM services WHERE deleted_at IS NULL'
        )->fetchColumn();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM services WHERE slug = :slug AND deleted_at IS NULL';
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
            'INSERT INTO services (
                title, slug, short_description, full_description, cover_media_id,
                sort_order, featured, status, price_display, created_at, updated_at
            ) VALUES (
                :title, :slug, :short_description, :full_description, :cover_media_id,
                :sort_order, :featured, :status, :price_display, NOW(), NOW()
            )',
            [
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'short_description' => $data['short_description'] ?? null,
                'full_description' => $data['full_description'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'featured' => $data['featured'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'price_display' => $data['price_display'] ?? null,
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
            'UPDATE services SET
                title = :title,
                slug = :slug,
                short_description = :short_description,
                full_description = :full_description,
                cover_media_id = :cover_media_id,
                sort_order = :sort_order,
                featured = :featured,
                status = :status,
                price_display = :price_display,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'short_description' => $data['short_description'] ?? null,
                'full_description' => $data['full_description'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'featured' => $data['featured'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'price_display' => $data['price_display'] ?? null,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE services SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublished(int $limit = 50): array
    {
        $rows = $this->database->query(
            sprintf(
                'SELECT s.*, m.directory AS cover_directory, m.stored_name AS cover_stored_name,
                        m.alt_text AS cover_alt_text, m.title AS cover_title
                 FROM services s
                 LEFT JOIN media m ON m.id = s.cover_media_id AND m.deleted_at IS NULL
                 WHERE s.status = "published" AND s.deleted_at IS NULL
                 ORDER BY s.featured DESC, s.sort_order ASC, s.id ASC
                 LIMIT %d',
                max(1, $limit)
            )
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function relatedPublished(int $serviceId, int $limit = 3): array
    {
        $rows = $this->database->query(
            sprintf(
                'SELECT s.*, m.directory AS cover_directory, m.stored_name AS cover_stored_name,
                        m.alt_text AS cover_alt_text, m.title AS cover_title
                 FROM services s
                 LEFT JOIN media m ON m.id = s.cover_media_id AND m.deleted_at IS NULL
                 WHERE s.id <> :service_id AND s.status = "published" AND s.deleted_at IS NULL
                 ORDER BY s.featured DESC, s.sort_order ASC, s.id ASC
                 LIMIT %d',
                max(1, $limit)
            ),
            ['service_id' => $serviceId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}