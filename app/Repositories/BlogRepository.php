<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BlogRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function postSlugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_posts WHERE slug = :slug AND deleted_at IS NULL';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    public function categorySlugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_categories WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    public function tagSlugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_tags WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function adminIndex(array $filters, int $limit = 20, int $offset = 0): array
    {
        [$whereClause, $params] = $this->adminWhere($filters);

        $rows = $this->database->query(
            sprintf(
                'SELECT bp.*, u.first_name AS author_first_name, u.last_name AS author_last_name,
                        m.directory AS featured_image_directory, m.stored_name AS featured_image_stored_name,
                        m.alt_text AS featured_image_alt_text, m.title AS featured_image_title,
                       m.width AS featured_image_width, m.height AS featured_image_height,
                       mv.directory AS featured_image_thumb_directory, mv.stored_name AS featured_image_thumb_stored_name,
                       mv.width AS featured_image_thumb_width, mv.height AS featured_image_thumb_height,
                        GROUP_CONCAT(DISTINCT bc.name ORDER BY bc.sort_order ASC, bc.name ASC SEPARATOR ", ") AS category_names
                 FROM blog_posts bp
                 INNER JOIN users u ON u.id = bp.author_id
                 LEFT JOIN media m ON m.id = bp.featured_image_id AND m.deleted_at IS NULL
                   LEFT JOIN media_variants mv ON mv.media_id = m.id AND mv.variant_key = "thumb"
                 LEFT JOIN blog_post_categories bpc ON bpc.post_id = bp.id
                 LEFT JOIN blog_categories bc ON bc.id = bpc.category_id
                 WHERE %s
                 GROUP BY bp.id, bp.author_id, bp.title, bp.slug, bp.excerpt, bp.body_long, bp.featured_image_id,
                          bp.cover_gallery_id, bp.status, bp.visibility, bp.is_featured, bp.allow_comments,
                          bp.published_at, bp.scheduled_at, bp.archived_at, bp.reading_time, bp.meta_summary,
                          bp.canonical_url, bp.view_count, bp.created_at, bp.updated_at, bp.deleted_at,
                          u.first_name, u.last_name, m.directory, m.stored_name, m.alt_text, m.title, m.width, m.height,
                          mv.directory, mv.stored_name, mv.width, mv.height
                 ORDER BY bp.is_featured DESC,
                          COALESCE(bp.published_at, bp.scheduled_at, bp.created_at) DESC,
                          bp.id DESC
                 LIMIT %d OFFSET %d',
                $whereClause,
                max(1, $limit),
                max(0, $offset)
            ),
            $params
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function countAdmin(array $filters): int
    {
        [$whereClause, $params] = $this->adminWhere($filters);

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM blog_posts bp WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function publicIndex(array $filters = [], int $limit = 9, int $offset = 0): array
    {
        [$whereClause, $params] = $this->publicWhere($filters);

        $rows = $this->database->query(
            sprintf(
                '%s
                 WHERE %s
                 ORDER BY bp.is_featured DESC, COALESCE(bp.published_at, bp.created_at) DESC, bp.id DESC
                 LIMIT %d OFFSET %d',
                $this->publicSelect(),
                $whereClause,
                max(1, $limit),
                max(0, $offset)
            ),
            $params
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function countPublic(array $filters = []): int
    {
        [$whereClause, $params] = $this->publicWhere($filters);

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM blog_posts bp WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    public function featuredPost(array $filters = []): ?array
    {
        $filters['featured_only'] = true;
        $items = $this->publicIndex($filters, 1, 0);

        return $items[0] ?? null;
    }

    /**
     * @param array<int, int> $excludeIds
     * @return array<int, array<string, mixed>>
     */
    public function recentPosts(int $limit = 5, array $excludeIds = []): array
    {
        $filters = [];

        if ($excludeIds !== []) {
            $filters['exclude_ids'] = $excludeIds;
        }

        return $this->publicIndex($filters, $limit, 0);
    }

    public function relatedPosts(int $postId, int $limit = 3): array
    {
        $rows = $this->database->query(
            sprintf(
                'SELECT %s,
                    (
                        SELECT COUNT(*)
                        FROM blog_post_categories bpc
                        WHERE bpc.post_id = bp.id
                          AND bpc.category_id IN (
                              SELECT src.category_id FROM blog_post_categories src WHERE src.post_id = :source_post_id
                          )
                    ) + (
                        SELECT COUNT(*)
                        FROM blog_post_tags bpt
                        WHERE bpt.post_id = bp.id
                          AND bpt.tag_id IN (
                              SELECT src.tag_id FROM blog_post_tags src WHERE src.post_id = :source_tag_post_id
                          )
                    ) AS relevance_score
                 %s
                 WHERE %s
                   AND bp.id <> :post_id
                   AND (
                        EXISTS (
                            SELECT 1
                            FROM blog_post_categories match_categories
                            WHERE match_categories.post_id = bp.id
                              AND match_categories.category_id IN (
                                  SELECT src_categories.category_id
                                  FROM blog_post_categories src_categories
                                  WHERE src_categories.post_id = :source_category_post_id
                              )
                        )
                        OR EXISTS (
                            SELECT 1
                            FROM blog_post_tags match_tags
                            WHERE match_tags.post_id = bp.id
                              AND match_tags.tag_id IN (
                                  SELECT src_tags.tag_id
                                  FROM blog_post_tags src_tags
                                  WHERE src_tags.post_id = :source_related_tag_post_id
                              )
                        )
                   )
                 ORDER BY relevance_score DESC, bp.is_featured DESC, COALESCE(bp.published_at, bp.created_at) DESC, bp.id DESC
                 LIMIT %d',
                $this->publicSelectFields(),
                $this->publicFromClause(),
                $this->publishedWhereClause(),
                max(1, $limit)
            ),
            [
                'post_id' => $postId,
                'source_post_id' => $postId,
                'source_tag_post_id' => $postId,
                'source_category_post_id' => $postId,
                'source_related_tag_post_id' => $postId,
            ]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function archiveCounts(int $limit = 12): array
    {
        $rows = $this->database->query(
            sprintf(
                'SELECT DATE_FORMAT(COALESCE(bp.published_at, bp.created_at), "%%Y-%%m") AS archive_month,
                        DATE_FORMAT(COALESCE(bp.published_at, bp.created_at), "%%b %%Y") AS archive_label,
                        COUNT(*) AS post_count
                 FROM blog_posts bp
                 WHERE %s
                 GROUP BY DATE_FORMAT(COALESCE(bp.published_at, bp.created_at), "%%Y-%%m"),
                          DATE_FORMAT(COALESCE(bp.published_at, bp.created_at), "%%b %%Y")
                 ORDER BY archive_month DESC
                 LIMIT %d',
                $this->publishedWhereClause('bp'),
                max(1, $limit)
            )
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function publishedPosts(int $limit = 12, int $offset = 0): array
    {
        return $this->publicIndex([], $limit, $offset);
    }

    public function countPublishedPosts(): int
    {
        return $this->countPublic();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sitemapPosts(): array
    {
        $rows = $this->database->query(
            'SELECT bp.slug, bp.published_at, bp.updated_at
             FROM blog_posts bp
             WHERE ' . $this->publishedWhereClause('bp') . '
             ORDER BY COALESCE(bp.updated_at, bp.published_at, bp.created_at) DESC, bp.id DESC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            $this->publicSelect() . '
             WHERE bp.slug = :slug
               AND ' . $this->publishedWhereClause('bp') . '
             LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categoryCounts(): array
    {
        $rows = $this->database->query(
            'SELECT bc.*, COUNT(DISTINCT bp.id) AS post_count
             FROM blog_categories bc
             LEFT JOIN blog_post_categories bpc ON bpc.category_id = bc.id
             LEFT JOIN blog_posts bp ON bp.id = bpc.post_id
                AND bp.deleted_at IS NULL
                AND bp.status = "published"
                AND bp.visibility = "public"
                AND (bp.published_at IS NULL OR bp.published_at <= NOW())
                AND (bp.archived_at IS NULL OR bp.archived_at > NOW())
             GROUP BY bc.id, bc.name, bc.slug, bc.description, bc.sort_order, bc.created_at, bc.updated_at
             ORDER BY bc.sort_order ASC, bc.name ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tagCounts(): array
    {
        $rows = $this->database->query(
            'SELECT bt.*, COUNT(DISTINCT bp.id) AS post_count
             FROM blog_tags bt
             LEFT JOIN blog_post_tags bpt ON bpt.tag_id = bt.id
             LEFT JOIN blog_posts bp ON bp.id = bpt.post_id
                AND bp.deleted_at IS NULL
                AND bp.status = "published"
                AND bp.visibility = "public"
                AND (bp.published_at IS NULL OR bp.published_at <= NOW())
                AND (bp.archived_at IS NULL OR bp.archived_at > NOW())
             GROUP BY bt.id, bt.name, bt.slug, bt.description, bt.created_at, bt.updated_at
             ORDER BY bt.name ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    private function publicSelect(): string
    {
        return 'SELECT ' . $this->publicSelectFields() . '
                ' . $this->publicFromClause();
    }

    private function publicSelectFields(): string
    {
        return 'bp.*, u.first_name AS author_first_name, u.last_name AS author_last_name,
                       m.directory AS featured_image_directory, m.stored_name AS featured_image_stored_name,
                       m.alt_text AS featured_image_alt_text, m.title AS featured_image_title,
                       m.width AS featured_image_width, m.height AS featured_image_height,
                       mv.directory AS featured_image_thumb_directory, mv.stored_name AS featured_image_thumb_stored_name,
                       mv.width AS featured_image_thumb_width, mv.height AS featured_image_thumb_height,
                       (
                           SELECT GROUP_CONCAT(CONCAT_WS("::", bc.name, bc.slug) ORDER BY bc.sort_order ASC, bc.name ASC SEPARATOR "||")
                           FROM blog_post_categories bpc
                           INNER JOIN blog_categories bc ON bc.id = bpc.category_id
                           WHERE bpc.post_id = bp.id
                       ) AS category_links,
                       (
                           SELECT GROUP_CONCAT(CONCAT_WS("::", bt.name, bt.slug) ORDER BY bt.name ASC SEPARATOR "||")
                           FROM blog_post_tags bpt
                           INNER JOIN blog_tags bt ON bt.id = bpt.tag_id
                           WHERE bpt.post_id = bp.id
                       ) AS tag_links';
    }

    private function publicFromClause(): string
    {
        return 'FROM blog_posts bp
                INNER JOIN users u ON u.id = bp.author_id
                LEFT JOIN media m ON m.id = bp.featured_image_id AND m.deleted_at IS NULL
                LEFT JOIN media_variants mv ON mv.media_id = m.id AND mv.variant_key = "thumb"';
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function publicWhere(array $filters): array
    {
        $whereParts = [$this->publishedWhereClause('bp')];
        $params = [];

        if (isset($filters['featured_only']) && $filters['featured_only']) {
            $whereParts[] = 'bp.is_featured = 1';
        }

        if (isset($filters['category_id']) && is_numeric($filters['category_id']) && (int) $filters['category_id'] > 0) {
            $whereParts[] = 'EXISTS (
                SELECT 1 FROM blog_post_categories bpc
                WHERE bpc.post_id = bp.id AND bpc.category_id = :category_id
            )';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (isset($filters['tag_id']) && is_numeric($filters['tag_id']) && (int) $filters['tag_id'] > 0) {
            $whereParts[] = 'EXISTS (
                SELECT 1 FROM blog_post_tags bpt
                WHERE bpt.post_id = bp.id AND bpt.tag_id = :tag_id
            )';
            $params['tag_id'] = (int) $filters['tag_id'];
        }

        if (isset($filters['archive_month']) && is_string($filters['archive_month']) && preg_match('/^\d{4}-\d{2}$/', $filters['archive_month']) === 1) {
            $whereParts[] = 'DATE_FORMAT(COALESCE(bp.published_at, bp.created_at), "%Y-%m") = :archive_month';
            $params['archive_month'] = $filters['archive_month'];
        }

        if (isset($filters['query']) && is_string($filters['query']) && trim($filters['query']) !== '') {
            $searchTerm = '%' . trim($filters['query']) . '%';
            $whereParts[] = '(
                bp.title LIKE :query_title
                OR bp.slug LIKE :query_slug
                OR bp.excerpt LIKE :query_excerpt
                OR bp.body_long LIKE :query_body
                OR bp.meta_summary LIKE :query_summary
                OR EXISTS (
                    SELECT 1
                    FROM blog_post_categories bpc
                    INNER JOIN blog_categories bc ON bc.id = bpc.category_id
                    WHERE bpc.post_id = bp.id AND (bc.name LIKE :query_category_name OR bc.slug LIKE :query_category_slug)
                )
                OR EXISTS (
                    SELECT 1
                    FROM blog_post_tags bpt
                    INNER JOIN blog_tags bt ON bt.id = bpt.tag_id
                    WHERE bpt.post_id = bp.id AND (bt.name LIKE :query_tag_name OR bt.slug LIKE :query_tag_slug)
                )
            )';
            $params['query_title'] = $searchTerm;
            $params['query_slug'] = $searchTerm;
            $params['query_excerpt'] = $searchTerm;
            $params['query_body'] = $searchTerm;
            $params['query_summary'] = $searchTerm;
            $params['query_category_name'] = $searchTerm;
            $params['query_category_slug'] = $searchTerm;
            $params['query_tag_name'] = $searchTerm;
            $params['query_tag_slug'] = $searchTerm;
        }

        if (isset($filters['exclude_id']) && is_numeric($filters['exclude_id']) && (int) $filters['exclude_id'] > 0) {
            $whereParts[] = 'bp.id <> :exclude_id';
            $params['exclude_id'] = (int) $filters['exclude_id'];
        }

        if (isset($filters['exclude_ids']) && is_array($filters['exclude_ids']) && $filters['exclude_ids'] !== []) {
            $placeholders = [];

            foreach (array_values($filters['exclude_ids']) as $index => $excludeId) {
                if (! is_numeric($excludeId) || (int) $excludeId <= 0) {
                    continue;
                }

                $placeholder = 'exclude_id_' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = (int) $excludeId;
            }

            if ($placeholders !== []) {
                $whereParts[] = 'bp.id NOT IN (' . implode(', ', $placeholders) . ')';
            }
        }

        return [implode(' AND ', $whereParts), $params];
    }

    private function publishedWhereClause(string $alias = 'bp'): string
    {
        return sprintf(
            '%s.deleted_at IS NULL AND %s.status = "published" AND %s.visibility = "public" AND (%s.published_at IS NULL OR %s.published_at <= NOW()) AND (%s.archived_at IS NULL OR %s.archived_at > NOW())',
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function adminWhere(array $filters): array
    {
        $whereParts = ['bp.deleted_at IS NULL'];
        $params = [];

        if (isset($filters['query']) && is_string($filters['query']) && trim($filters['query']) !== '') {
            $searchTerm = '%' . trim($filters['query']) . '%';
            $whereParts[] = '(bp.title LIKE :query_title OR bp.slug LIKE :query_slug OR bp.excerpt LIKE :query_excerpt OR bp.meta_summary LIKE :query_summary)';
            $params['query_title'] = $searchTerm;
            $params['query_slug'] = $searchTerm;
            $params['query_excerpt'] = $searchTerm;
            $params['query_summary'] = $searchTerm;
        }

        if (isset($filters['status']) && is_string($filters['status']) && $filters['status'] !== '') {
            $whereParts[] = 'bp.status = :status';
            $params['status'] = $filters['status'];
        }

        if (isset($filters['author_id']) && is_numeric($filters['author_id']) && (int) $filters['author_id'] > 0) {
            $whereParts[] = 'bp.author_id = :author_id';
            $params['author_id'] = (int) $filters['author_id'];
        }

        if (isset($filters['featured']) && $filters['featured'] !== '' && $filters['featured'] !== null) {
            $whereParts[] = 'bp.is_featured = :featured';
            $params['featured'] = (int) ((string) $filters['featured'] === '1');
        }

        if (isset($filters['category_id']) && is_numeric($filters['category_id']) && (int) $filters['category_id'] > 0) {
            $whereParts[] = 'EXISTS (
                SELECT 1 FROM blog_post_categories bpc2
                WHERE bpc2.post_id = bp.id AND bpc2.category_id = :category_id
            )';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (isset($filters['date_from']) && is_string($filters['date_from']) && trim($filters['date_from']) !== '') {
            $whereParts[] = 'DATE(COALESCE(bp.published_at, bp.scheduled_at, bp.created_at)) >= :date_from';
            $params['date_from'] = trim($filters['date_from']);
        }

        if (isset($filters['date_to']) && is_string($filters['date_to']) && trim($filters['date_to']) !== '') {
            $whereParts[] = 'DATE(COALESCE(bp.published_at, bp.scheduled_at, bp.created_at)) <= :date_to';
            $params['date_to'] = trim($filters['date_to']);
        }

        return [implode(' AND ', $whereParts), $params];
    }
}
