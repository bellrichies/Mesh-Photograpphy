<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogPost
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            $this->selectBase() . ' WHERE bp.id = :id AND bp.deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            $this->selectBase() . ' WHERE bp.slug = :slug AND bp.deleted_at IS NULL LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            $this->selectBase() . ' WHERE bp.slug = :slug AND bp.deleted_at IS NULL AND bp.status = "published" AND bp.visibility = "public" AND (bp.archived_at IS NULL OR bp.archived_at > NOW()) AND (bp.published_at IS NULL OR bp.published_at <= NOW()) LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_posts WHERE slug = :slug AND deleted_at IS NULL';
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
    public function categories(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT bc.*
             FROM blog_categories bc
             INNER JOIN blog_post_categories bpc ON bpc.category_id = bc.id
             WHERE bpc.post_id = :post_id
             ORDER BY bc.sort_order ASC, bc.name ASC',
            ['post_id' => $postId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tags(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT bt.*
             FROM blog_tags bt
             INNER JOIN blog_post_tags bpt ON bpt.tag_id = bt.id
             WHERE bpt.post_id = :post_id
             ORDER BY bt.name ASC',
            ['post_id' => $postId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function media(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT bpm.*, m.*
             FROM blog_post_media bpm
             INNER JOIN media m ON m.id = bpm.media_id
             WHERE bpm.post_id = :post_id AND m.deleted_at IS NULL
             ORDER BY bpm.sort_order ASC, bpm.id ASC',
            ['post_id' => $postId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function revisions(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT bpr.*, u.first_name, u.last_name
             FROM blog_post_revisions bpr
             LEFT JOIN users u ON u.id = bpr.edited_by
             WHERE bpr.post_id = :post_id
             ORDER BY bpr.id DESC',
            ['post_id' => $postId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', string $status = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['bp.deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(bp.title LIKE :query OR bp.slug LIKE :query OR bp.excerpt LIKE :query OR bp.meta_summary LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'bp.status = :status';
            $params['status'] = $status;
        }

        $rows = $this->database->query(
            sprintf(
                '%s WHERE %s ORDER BY bp.is_featured DESC, bp.published_at DESC, bp.id DESC LIMIT %d OFFSET %d',
                $this->selectBase(),
                implode(' AND ', $whereParts),
                max(1, $limit),
                max(0, $offset)
            ),
            $params
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', string $status = ''): int
    {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR slug LIKE :query OR excerpt LIKE :query OR meta_summary LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM blog_posts WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    public function nextSortCandidate(): int
    {
        return (int) $this->database->query('SELECT COALESCE(COUNT(*), 0) + 1 FROM blog_posts WHERE deleted_at IS NULL')->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO blog_posts (
                author_id, title, slug, excerpt, body_long, featured_image_id, cover_gallery_id,
                status, visibility, is_featured, allow_comments, published_at, scheduled_at,
                archived_at, reading_time, meta_summary, canonical_url, view_count, created_at,
                updated_at
            ) VALUES (
                :author_id, :title, :slug, :excerpt, :body_long, :featured_image_id, :cover_gallery_id,
                :status, :visibility, :is_featured, :allow_comments, :published_at, :scheduled_at,
                :archived_at, :reading_time, :meta_summary, :canonical_url, :view_count, NOW(), NOW()
            )',
            [
                'author_id' => $data['author_id'] ?? 0,
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'excerpt' => $data['excerpt'] ?? null,
                'body_long' => $data['body_long'] ?? null,
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'cover_gallery_id' => $data['cover_gallery_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'visibility' => $data['visibility'] ?? 'public',
                'is_featured' => $data['is_featured'] ?? 0,
                'allow_comments' => $data['allow_comments'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'archived_at' => $data['archived_at'] ?? null,
                'reading_time' => $data['reading_time'] ?? null,
                'meta_summary' => $data['meta_summary'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'view_count' => $data['view_count'] ?? 0,
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
            'UPDATE blog_posts SET
                author_id = :author_id,
                title = :title,
                slug = :slug,
                excerpt = :excerpt,
                body_long = :body_long,
                featured_image_id = :featured_image_id,
                cover_gallery_id = :cover_gallery_id,
                status = :status,
                visibility = :visibility,
                is_featured = :is_featured,
                allow_comments = :allow_comments,
                published_at = :published_at,
                scheduled_at = :scheduled_at,
                archived_at = :archived_at,
                reading_time = :reading_time,
                meta_summary = :meta_summary,
                canonical_url = :canonical_url,
                view_count = :view_count,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'author_id' => $data['author_id'] ?? 0,
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'excerpt' => $data['excerpt'] ?? null,
                'body_long' => $data['body_long'] ?? null,
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'cover_gallery_id' => $data['cover_gallery_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'visibility' => $data['visibility'] ?? 'public',
                'is_featured' => $data['is_featured'] ?? 0,
                'allow_comments' => $data['allow_comments'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'archived_at' => $data['archived_at'] ?? null,
                'reading_time' => $data['reading_time'] ?? null,
                'meta_summary' => $data['meta_summary'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'view_count' => $data['view_count'] ?? 0,
            ]
        );
    }

    public function incrementViewCount(int $id): void
    {
        $this->database->query(
            'UPDATE blog_posts SET view_count = view_count + 1, updated_at = updated_at WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE blog_posts SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }

    private function selectBase(): string
    {
        return 'SELECT bp.*, u.first_name AS author_first_name, u.last_name AS author_last_name,
                       m.directory AS featured_image_directory, m.stored_name AS featured_image_stored_name,
                       m.alt_text AS featured_image_alt_text, m.title AS featured_image_title,
                       g.title AS cover_gallery_title, g.slug AS cover_gallery_slug
                FROM blog_posts bp
                INNER JOIN users u ON u.id = bp.author_id
                LEFT JOIN media m ON m.id = bp.featured_image_id AND m.deleted_at IS NULL
                LEFT JOIN galleries g ON g.id = bp.cover_gallery_id AND g.deleted_at IS NULL';
    }
}