<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogPost
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = ['bp.deleted_at IS NULL', 'bp.is_published = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]  = 'bc.slug = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['tag'])) {
            $where[]  = 'EXISTS (
                SELECT 1 FROM blog_post_tags bpt
                JOIN blog_tags bt ON bpt.tag_id = bt.id
                WHERE bpt.post_id = bp.id AND bt.slug = ?
            )';
            $params[] = $filters['tag'];
        }

        if (!empty($filters['q'])) {
            $where[]  = '(bp.title LIKE ? OR bp.excerpt LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $offset = ($page - 1) * $perPage;

        $sql = 'SELECT bp.*, m.path AS cover_path, m.alt_text AS cover_alt,
                       m.uuid AS cover_uuid, m.original_name AS cover_original,
                       m.file_name AS cover_file, m.mime_type AS cover_mime,
                       m.file_size AS cover_size, m.width AS cover_width,
                       m.height AS cover_height,
                       bc.id AS cat_id, bc.name AS cat_name, bc.slug AS cat_slug
                FROM blog_posts bp
                LEFT JOIN media m ON bp.cover_image_id = m.id AND m.deleted_at IS NULL
                LEFT JOIN blog_categories bc ON bp.category_id = bc.id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY bp.published_at DESC
                LIMIT ? OFFSET ?';

        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function countPublished(array $filters = []): int
    {
        $where  = ['bp.deleted_at IS NULL', 'bp.is_published = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]  = 'bc.slug = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['tag'])) {
            $where[]  = 'EXISTS (
                SELECT 1 FROM blog_post_tags bpt
                JOIN blog_tags bt ON bpt.tag_id = bt.id
                WHERE bpt.post_id = bp.id AND bt.slug = ?
            )';
            $params[] = $filters['tag'];
        }

        if (!empty($filters['q'])) {
            $where[]  = '(bp.title LIKE ? OR bp.excerpt LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt
             FROM blog_posts bp
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE ' . implode(' AND ', $where),
            $params
        )->fetch();

        return (int) ($row['cnt'] ?? 0);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT bp.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height,
                    bc.id AS cat_id, bc.name AS cat_name, bc.slug AS cat_slug,
                    u.first_name AS author_first, u.last_name AS author_last
             FROM blog_posts bp
             LEFT JOIN media m ON bp.cover_image_id = m.id AND m.deleted_at IS NULL
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             LEFT JOIN users u ON bp.author_id = u.id
             WHERE bp.slug = ? AND bp.deleted_at IS NULL AND bp.is_published = 1
             LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }

    public function getTags(int $postId): array
    {
        return $this->db->query(
            'SELECT bt.id, bt.name, bt.slug
             FROM blog_tags bt
             JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
             WHERE bpt.post_id = ?
             ORDER BY bt.name ASC',
            [$postId]
        )->fetchAll();
    }

    public function getRelated(int $postId, ?int $categoryId, int $limit = 3): array
    {
        if ($categoryId) {
            return $this->db->query(
                'SELECT bp.*, m.path AS cover_path, m.alt_text AS cover_alt,
                        m.file_name AS cover_file, m.mime_type AS cover_mime,
                        m.file_size AS cover_size, m.width AS cover_width,
                        m.height AS cover_height
                 FROM blog_posts bp
                 LEFT JOIN media m ON bp.cover_image_id = m.id AND m.deleted_at IS NULL
                 WHERE bp.category_id = ? AND bp.id != ?
                   AND bp.deleted_at IS NULL AND bp.is_published = 1
                 ORDER BY bp.published_at DESC
                 LIMIT ?',
                [$categoryId, $postId, $limit]
            )->fetchAll();
        }

        return $this->db->query(
            'SELECT bp.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height
             FROM blog_posts bp
             LEFT JOIN media m ON bp.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE bp.id != ? AND bp.deleted_at IS NULL AND bp.is_published = 1
             ORDER BY bp.published_at DESC
             LIMIT ?',
            [$postId, $limit]
        )->fetchAll();
    }

    public function getPrev(int $id): ?array
    {
        return $this->db->query(
            'SELECT slug, title FROM blog_posts
             WHERE id < ? AND deleted_at IS NULL AND is_published = 1
             ORDER BY id DESC LIMIT 1',
            [$id]
        )->fetch() ?: null;
    }

    public function getNext(int $id): ?array
    {
        return $this->db->query(
            'SELECT slug, title FROM blog_posts
             WHERE id > ? AND deleted_at IS NULL AND is_published = 1
             ORDER BY id ASC LIMIT 1',
            [$id]
        )->fetch() ?: null;
    }

    public function getCategories(): array
    {
        return $this->db->query(
            'SELECT bc.id, bc.name, bc.slug, COUNT(bp.id) AS post_count
             FROM blog_categories bc
             LEFT JOIN blog_posts bp ON bp.category_id = bc.id
                 AND bp.deleted_at IS NULL AND bp.is_published = 1
             WHERE bc.deleted_at IS NULL
             GROUP BY bc.id, bc.name, bc.slug
             ORDER BY bc.name ASC'
        )->fetchAll();
    }

    public function findCategoryBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT * FROM blog_categories WHERE slug = ? AND deleted_at IS NULL LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }

    public function getTags(): array
    {
        return $this->db->query(
            'SELECT bt.id, bt.name, bt.slug, COUNT(bpt.post_id) AS post_count
             FROM blog_tags bt
             LEFT JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
             LEFT JOIN blog_posts bp ON bpt.post_id = bp.id
                 AND bp.deleted_at IS NULL AND bp.is_published = 1
             WHERE bt.deleted_at IS NULL
             GROUP BY bt.id, bt.name, bt.slug
             ORDER BY bt.name ASC'
        )->fetchAll();
    }

    public function findTagBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT * FROM blog_tags WHERE slug = ? AND deleted_at IS NULL LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }
}
