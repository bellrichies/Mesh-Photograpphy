<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class GalleryRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * Stub for public gallery listing queries.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        [$whereClause, $params] = $this->publicWhere($filters);

        $sql = sprintf(
            '%s WHERE %s ORDER BY g.featured DESC, g.sort_order ASC, g.published_at DESC, g.id DESC LIMIT %d OFFSET %d',
            $this->publicSelect(),
            $whereClause,
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            $this->publicSelect() . ' WHERE g.slug = :slug AND g.status = "published" AND g.deleted_at IS NULL LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function countPublished(array $filters = []): int
    {
        [$whereClause, $params] = $this->publicWhere($filters);

        return (int) $this->database->query(
            'SELECT COUNT(DISTINCT g.id)
             FROM galleries g
             LEFT JOIN gallery_category_map gcm ON gcm.gallery_id = g.id
             WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM galleries WHERE slug = :slug AND deleted_at IS NULL';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * Stub for future admin index filters.
     *
     * @return array<int, array<string, mixed>>
     */
    public function adminIndex(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        [$whereClause, $params] = $this->adminWhere($filters);

        $sql = sprintf(
            'SELECT g.*, gc.name AS primary_category_name
             FROM galleries g
             LEFT JOIN gallery_categories gc ON gc.id = g.category_primary_id
             WHERE %s
             ORDER BY g.updated_at DESC, g.id DESC
             LIMIT %d OFFSET %d',
            $whereClause,
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function countAdmin(array $filters = []): int
    {
        [$whereClause, $params] = $this->adminWhere($filters);

        return (int) $this->database->query(
            'SELECT COUNT(DISTINCT g.id)
             FROM galleries g
             LEFT JOIN gallery_category_map gcm ON gcm.gallery_id = g.id
             WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function relatedByCategory(int $galleryId, int $categoryId, int $limit = 3): array
    {
        $rows = $this->database->query(
            sprintf(
                '%s
                 WHERE g.id <> :gallery_id
                   AND (g.category_primary_id = :category_primary_id OR EXISTS (
                       SELECT 1 FROM gallery_category_map map WHERE map.gallery_id = g.id AND map.category_id = :map_category_id
                   ))
                   AND g.status = "published"
                   AND g.deleted_at IS NULL
                 ORDER BY g.featured DESC, g.published_at DESC, g.id DESC
                 LIMIT %d',
                $this->publicSelect(),
                max(1, $limit)
            ),
            [
                'gallery_id' => $galleryId,
                'category_primary_id' => $categoryId,
                'map_category_id' => $categoryId,
            ]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sitemapItems(): array
    {
        $rows = $this->database->query(
            'SELECT g.slug, g.published_at, g.updated_at
             FROM galleries g
             WHERE g.deleted_at IS NULL AND g.status = "published"
             ORDER BY COALESCE(g.updated_at, g.published_at, g.created_at) DESC, g.id DESC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    private function publicSelect(): string
    {
        return 'SELECT g.*, gc.name AS primary_category_name, gc.slug AS primary_category_slug,
                       cover.directory AS cover_directory, cover.stored_name AS cover_stored_name,
                       cover.title AS cover_title, cover.alt_text AS cover_alt_text,
                  cover.caption AS cover_caption, cover.mime_type AS cover_mime_type,
                  cover.width AS cover_width, cover.height AS cover_height,
                  cover_thumb.directory AS cover_thumb_directory, cover_thumb.stored_name AS cover_thumb_stored_name,
                  cover_thumb.width AS cover_thumb_width, cover_thumb.height AS cover_thumb_height,
                       (SELECT COUNT(*) FROM gallery_media gm WHERE gm.gallery_id = g.id) AS media_count
                FROM galleries g
                LEFT JOIN gallery_categories gc ON gc.id = g.category_primary_id
              LEFT JOIN media cover ON cover.id = g.cover_media_id AND cover.deleted_at IS NULL
              LEFT JOIN media_variants cover_thumb ON cover_thumb.media_id = cover.id AND cover_thumb.variant_key = "thumb"';
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function publicWhere(array $filters): array
    {
        $whereParts = ['g.deleted_at IS NULL', 'g.status = "published"'];
        $params = [];

        if (isset($filters['featured']) && $filters['featured'] !== '' && $filters['featured'] !== null) {
            $whereParts[] = 'g.featured = :featured';
            $params['featured'] = (int) ((string) $filters['featured'] === '1' || $filters['featured'] === true || $filters['featured'] === 1);
        }

        if (isset($filters['category_primary_id']) && is_numeric($filters['category_primary_id']) && (int) $filters['category_primary_id'] > 0) {
            $whereParts[] = '(g.category_primary_id = :category_primary_id OR EXISTS (
                SELECT 1 FROM gallery_category_map map
                WHERE map.gallery_id = g.id AND map.category_id = :category_map_category_id
            ))';
            $params['category_primary_id'] = (int) $filters['category_primary_id'];
            $params['category_map_category_id'] = (int) $filters['category_primary_id'];
        }

        if (isset($filters['query']) && is_string($filters['query']) && trim($filters['query']) !== '') {
            $searchTerm = '%' . trim($filters['query']) . '%';
            $whereParts[] = '(g.title LIKE :query_title OR g.excerpt LIKE :query_excerpt OR g.location LIKE :query_location OR g.client_name LIKE :query_client)';
            $params['query_title'] = $searchTerm;
            $params['query_excerpt'] = $searchTerm;
            $params['query_location'] = $searchTerm;
            $params['query_client'] = $searchTerm;
        }

        return [implode(' AND ', $whereParts), $params];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function adminWhere(array $filters): array
    {
        $whereParts = ['g.deleted_at IS NULL'];
        $params = [];

        if (isset($filters['status']) && is_string($filters['status']) && $filters['status'] !== '') {
            $whereParts[] = 'g.status = :status';
            $params['status'] = $filters['status'];
        }

        if (isset($filters['featured']) && $filters['featured'] !== '' && $filters['featured'] !== null) {
            $whereParts[] = 'g.featured = :featured';
            $params['featured'] = (int) ((string) $filters['featured'] === '1');
        }

        if (isset($filters['category_id']) && is_numeric($filters['category_id']) && (int) $filters['category_id'] > 0) {
            $whereParts[] = '(g.category_primary_id = :category_id OR EXISTS (
                SELECT 1 FROM gallery_category_map map
                WHERE map.gallery_id = g.id AND map.category_id = :category_map_category_id
            ))';
            $params['category_id'] = (int) $filters['category_id'];
            $params['category_map_category_id'] = (int) $filters['category_id'];
        }

        if (isset($filters['query']) && is_string($filters['query']) && trim($filters['query']) !== '') {
            $searchTerm = '%' . trim($filters['query']) . '%';
            $whereParts[] = '(g.title LIKE :query_title OR g.slug LIKE :query_slug OR g.client_name LIKE :query_client OR g.location LIKE :query_location OR g.excerpt LIKE :query_excerpt)';
            $params['query_title'] = $searchTerm;
            $params['query_slug'] = $searchTerm;
            $params['query_client'] = $searchTerm;
            $params['query_location'] = $searchTerm;
            $params['query_excerpt'] = $searchTerm;
        }

        return [implode(' AND ', $whereParts), $params];
    }
}
