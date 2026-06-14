<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class BlogPostController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $db      = app_database();
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $offset  = ($page - 1) * $perPage;

        $where  = ['bp.deleted_at IS NULL'];
        $params = [];

        if ($status = $request->query('status')) {
            if ($status === 'published') { $where[] = 'bp.is_published = 1'; }
            elseif ($status === 'draft') { $where[] = 'bp.is_published = 0'; }
        }

        if ($q = $request->query('q')) {
            $where[]  = 'bp.title LIKE ?';
            $params[] = '%' . $q . '%';
        }

        $whereStr = implode(' AND ', $where);
        $total    = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM blog_posts bp WHERE {$whereStr}", $params
        )->fetch()['cnt'];

        $rows = $db->query(
            "SELECT bp.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height,
                    bc.id AS cat_id, bc.name AS cat_name, bc.slug AS cat_slug,
                    u.first_name AS author_first, u.last_name AS author_last
             FROM blog_posts bp
             LEFT JOIN media m ON bp.cover_image_id = m.id AND m.deleted_at IS NULL
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id AND bc.deleted_at IS NULL
             LEFT JOIN users u ON bp.author_id = u.id
             WHERE {$whereStr}
             ORDER BY bp.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        )->fetchAll();

        $items = array_map([$this, 'formatSummary'], $rows);
        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }

    public function show(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $row  = $this->findOrFail($db, $id);
        $tags = $db->query(
            'SELECT bt.id, bt.name, bt.slug FROM blog_tags bt
             JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
             WHERE bpt.post_id = ? AND bt.deleted_at IS NULL ORDER BY bt.name',
            [$id]
        )->fetchAll();

        $data = $this->formatSummary($row);
        $data['body']   = $row['body'] ?? '';
        $data['tags']   = $tags;
        $data['excerpt'] = $row['excerpt'] ?? null;
        $data['seo_title'] = $row['seo_title'] ?? null;
        $data['seo_description'] = $row['seo_description'] ?? null;

        return $this->success($data);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, [
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|max:255',
        ]);
        if ($err) return $err;

        $db = app_database();
        if ($db->query('SELECT id FROM blog_posts WHERE slug=? AND deleted_at IS NULL', [$data['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug is already in use.']]);
        }

        $payload  = $request->authPayload();
        $authorId = $payload['user_id'] ?? $payload['sub'] ?? null;

        // Determine publish time: explicit scheduled date, or now if publishing immediately
        $scheduledAt = !empty($data['published_at']) ? $data['published_at'] : null;
        $isPublished = !empty($data['is_published']) ? 1 : 0;
        $publishedAt = $scheduledAt ?? ($isPublished ? date('Y-m-d H:i:s') : null);

        $db->query(
            'INSERT INTO blog_posts (title, slug, excerpt, body, category_id, author_id, cover_image_id, is_published, published_at, seo_title, seo_description, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $data['title'],
                $data['slug'],
                $data['excerpt']        ?? null,
                $data['body']           ?? '',
                $data['category_id']    ?? null,
                $authorId,
                $data['cover_image_id'] ?? null,
                $isPublished,
                $publishedAt,
                $data['seo_title']      ?? null,
                $data['seo_description'] ?? null,
            ]
        );

        $id = (int) $db->lastInsertId();
        $this->syncTags($db, $id, $data['tag_ids'] ?? []);

        return $this->created($this->formatSummary($this->findOrFail($db, $id)));
    }

    public function update(Request $request, Response $response): Response
    {
        $db      = app_database();
        $id      = (int) $request->param('id');
        $current = $this->findOrFail($db, $id);
        $data    = $request->json();

        $err = $this->validate($data, [
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|max:255',
        ]);
        if ($err) return $err;

        $conflict = $db->query(
            'SELECT id FROM blog_posts WHERE slug=? AND id!=? AND deleted_at IS NULL',
            [$data['slug'], $id]
        )->fetch();
        if ($conflict) return $this->validationError(['slug' => ['Slug is already in use.']]);

        // Save a revision of the current state before overwriting
        $payload = $request->authPayload();
        $userId  = $payload['user_id'] ?? $payload['sub'] ?? null;
        $db->query(
            'INSERT INTO blog_post_revisions (post_id, title, body, saved_by, created_at) VALUES (?,?,?,?,NOW())',
            [$id, $current['title'], $current['body'] ?? '', $userId]
        );

        // Scheduled publishing: explicit published_at overrides, else set on first publish
        $scheduledAt  = !empty($data['published_at']) ? $data['published_at'] : null;
        $nowPublished = !empty($data['is_published']) ? 1 : 0;
        $wasPublished = (bool) ($current['is_published'] ?? 0);

        if ($scheduledAt) {
            $publishedAt = $scheduledAt;
        } elseif ($nowPublished && !$wasPublished) {
            $publishedAt = date('Y-m-d H:i:s');
        } else {
            $publishedAt = null; // keep existing via COALESCE
        }

        $db->query(
            'UPDATE blog_posts SET title=?,slug=?,excerpt=?,body=?,category_id=?,cover_image_id=?,is_published=?,
             published_at=COALESCE(?,published_at),seo_title=?,seo_description=?,updated_at=NOW() WHERE id=?',
            [
                $data['title'],
                $data['slug'],
                $data['excerpt']         ?? null,
                $data['body']            ?? '',
                $data['category_id']     ?? null,
                $data['cover_image_id']  ?? null,
                $nowPublished,
                $publishedAt,
                $data['seo_title']       ?? null,
                $data['seo_description'] ?? null,
                $id,
            ]
        );

        $this->syncTags($db, $id, $data['tag_ids'] ?? []);

        return $this->success($this->formatSummary($this->findOrFail($db, $id)));
    }

    public function autosave(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $this->findOrFail($db, $id);
        $body = $request->json()['body'] ?? '';

        $db->query('UPDATE blog_posts SET body=?, updated_at=NOW() WHERE id=?', [$body, $id]);
        return $this->success(null, 'Autosaved.');
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $this->findOrFail($db, $id);
        $db->query('UPDATE blog_posts SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function findOrFail(\App\Core\Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT bp.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height,
                    bc.id AS cat_id, bc.name AS cat_name, bc.slug AS cat_slug,
                    u.first_name AS author_first, u.last_name AS author_last
             FROM blog_posts bp
             LEFT JOIN media m ON bp.cover_image_id = m.id AND m.deleted_at IS NULL
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id AND bc.deleted_at IS NULL
             LEFT JOIN users u ON bp.author_id = u.id
             WHERE bp.id=? AND bp.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Blog post not found.');
        return $row;
    }

    private function syncTags(\App\Core\Database $db, int $postId, array $tagIds): void
    {
        $db->query('DELETE FROM blog_post_tags WHERE post_id=?', [$postId]);
        foreach ($tagIds as $tagId) {
            $db->query('INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?,?)', [$postId, (int)$tagId]);
        }
    }

    private function formatSummary(array $row): array
    {
        return [
            'id'           => (int)$row['id'],
            'title'        => $row['title'],
            'slug'         => $row['slug'],
            'excerpt'      => $row['excerpt'] ?? null,
            'cover'        => $this->fmt->formatCover($row),
            'status'       => $row['is_published'] ? 'published' : 'draft',
            'published_at' => $row['published_at'] ?? null,
            'category'     => $row['cat_id'] ? ['id' => (int)$row['cat_id'], 'name' => $row['cat_name'], 'slug' => $row['cat_slug']] : null,
            'category_id'  => $row['category_id'] ?? null,
            'author'       => ($row['author_first'] ?? null) ? ['name' => trim($row['author_first'] . ' ' . $row['author_last'])] : null,
            'created_at'   => $row['created_at'],
            'updated_at'   => $row['updated_at'],
        ];
    }
}
