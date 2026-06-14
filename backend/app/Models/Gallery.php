<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Gallery
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(array $filters = []): array
    {
        $where  = ['g.deleted_at IS NULL', 'g.is_published = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]  = 'g.category = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['featured'])) {
            $where[]  = 'g.is_featured = 1';
        }

        $sql = 'SELECT g.*, m.path AS cover_path, m.alt_text AS cover_alt,
                       m.uuid AS cover_uuid, m.original_name AS cover_original,
                       m.file_name AS cover_file, m.mime_type AS cover_mime,
                       m.file_size AS cover_size, m.width AS cover_width,
                       m.height AS cover_height
                FROM galleries g
                LEFT JOIN media m ON g.cover_image_id = m.id AND m.deleted_at IS NULL
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY g.sort_order ASC, g.created_at DESC';

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function countPublished(array $filters = []): int
    {
        $where  = ['deleted_at IS NULL', 'is_published = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]  = 'category = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['featured'])) {
            $where[] = 'is_featured = 1';
        }

        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM galleries WHERE ' . implode(' AND ', $where),
            $params
        )->fetch();

        return (int) ($row['cnt'] ?? 0);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT g.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height
             FROM galleries g
             LEFT JOIN media m ON g.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE g.slug = ? AND g.deleted_at IS NULL AND g.is_published = 1
             LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }

    public function getMedia(int $galleryId): array
    {
        return $this->db->query(
            'SELECT m.*, gm.sort_order, gm.caption
             FROM gallery_media gm
             JOIN media m ON gm.media_id = m.id AND m.deleted_at IS NULL
             WHERE gm.gallery_id = ?
             ORDER BY gm.sort_order ASC',
            [$galleryId]
        )->fetchAll();
    }

    public function getCategories(): array
    {
        return $this->db->query(
            'SELECT category AS slug, category AS name,
                    COUNT(*) AS gallery_count
             FROM galleries
             WHERE deleted_at IS NULL AND is_published = 1 AND category IS NOT NULL
             GROUP BY category
             ORDER BY category ASC'
        )->fetchAll();
    }

    public function getPrev(int $id, string $category = null): ?array
    {
        $where  = ['deleted_at IS NULL', 'is_published = 1', 'id < ?'];
        $params = [$id];

        if ($category) {
            $where[]  = 'category = ?';
            $params[] = $category;
        }

        return $this->db->query(
            'SELECT slug, title FROM galleries
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY id DESC LIMIT 1',
            $params
        )->fetch() ?: null;
    }

    public function getNext(int $id, string $category = null): ?array
    {
        $where  = ['deleted_at IS NULL', 'is_published = 1', 'id > ?'];
        $params = [$id];

        if ($category) {
            $where[]  = 'category = ?';
            $params[] = $category;
        }

        return $this->db->query(
            'SELECT slug, title FROM galleries
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY id ASC LIMIT 1',
            $params
        )->fetch() ?: null;
    }

    public function countMedia(int $galleryId): int
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM gallery_media gm
             JOIN media m ON gm.media_id = m.id AND m.deleted_at IS NULL
             WHERE gm.gallery_id = ?',
            [$galleryId]
        )->fetch();

        return (int) ($row['cnt'] ?? 0);
    }
}
