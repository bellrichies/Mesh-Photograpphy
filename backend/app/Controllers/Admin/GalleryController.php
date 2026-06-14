<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class GalleryController extends Controller
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

        $where  = ['g.deleted_at IS NULL'];
        $params = [];

        if ($status = $request->query('status')) {
            $where[]  = 'g.is_published = ?';
            $params[] = $status === 'published' ? 1 : 0;
        }

        if ($q = $request->query('q')) {
            $where[]  = 'g.title LIKE ?';
            $params[] = '%' . $q . '%';
        }

        $whereStr = implode(' AND ', $where);

        $total = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM galleries g WHERE {$whereStr}",
            $params
        )->fetch()['cnt'];

        $rows = $db->query(
            "SELECT g.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height
             FROM galleries g
             LEFT JOIN media m ON g.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE {$whereStr}
             ORDER BY g.sort_order ASC, g.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        )->fetchAll();

        $items = array_map([$this, 'formatRow'], $rows);

        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }

    public function show(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $row = $this->findOrFail($db, $id);

        $media = $db->query(
            'SELECT m.*, gm.sort_order, gm.caption
             FROM gallery_media gm
             JOIN media m ON gm.media_id = m.id AND m.deleted_at IS NULL
             WHERE gm.gallery_id = ?
             ORDER BY gm.sort_order ASC',
            [$id]
        )->fetchAll();

        $data = $this->formatRow($row);
        $data['media'] = array_map([$this->fmt, 'formatMedia'], $media);

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

        if ($db->query('SELECT id FROM galleries WHERE slug = ? AND deleted_at IS NULL', [$data['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug is already in use.']]);
        }

        $db->query(
            'INSERT INTO galleries (title, slug, description, category, is_published, is_featured, cover_image_id, sort_order, seo_title, seo_description, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['title'],
                $data['slug'],
                $data['description']  ?? null,
                $data['category']     ?? null,
                !empty($data['is_published']) ? 1 : 0,
                !empty($data['is_featured'])  ? 1 : 0,
                $data['cover_image_id'] ?? null,
                $data['sort_order']   ?? 0,
                $data['seo_title']    ?? null,
                $data['seo_description'] ?? null,
            ]
        );

        $id  = (int) $db->lastInsertId();
        $row = $this->findOrFail($db, $id);

        return $this->created($this->formatRow($row));
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $this->findOrFail($db, $id);
        $data = $request->json();

        $err = $this->validate($data, [
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|max:255',
        ]);
        if ($err) return $err;

        $conflict = $db->query(
            'SELECT id FROM galleries WHERE slug = ? AND id != ? AND deleted_at IS NULL',
            [$data['slug'], $id]
        )->fetch();
        if ($conflict) {
            return $this->validationError(['slug' => ['Slug is already in use.']]);
        }

        $db->query(
            'UPDATE galleries SET title=?, slug=?, description=?, category=?, is_published=?, is_featured=?, cover_image_id=?, sort_order=?, seo_title=?, seo_description=?, updated_at=NOW()
             WHERE id=?',
            [
                $data['title'],
                $data['slug'],
                $data['description']    ?? null,
                $data['category']       ?? null,
                !empty($data['is_published']) ? 1 : 0,
                !empty($data['is_featured'])  ? 1 : 0,
                $data['cover_image_id'] ?? null,
                $data['sort_order']     ?? 0,
                $data['seo_title']      ?? null,
                $data['seo_description'] ?? null,
                $id,
            ]
        );

        return $this->success($this->formatRow($this->findOrFail($db, $id)));
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        $this->findOrFail($db, $id);
        $db->query('UPDATE galleries SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    public function attachMedia(Request $request, Response $response): Response
    {
        $db       = app_database();
        $id       = (int) $request->param('id');
        $this->findOrFail($db, $id);
        $data     = $request->json();
        $mediaId  = (int) ($data['media_id'] ?? 0);

        if (!$mediaId) {
            return $this->validationError(['media_id' => ['media_id is required.']]);
        }

        $maxOrder = $db->query(
            'SELECT MAX(sort_order) AS m FROM gallery_media WHERE gallery_id=?', [$id]
        )->fetch()['m'] ?? 0;

        $db->query(
            'INSERT INTO gallery_media (gallery_id, media_id, sort_order, caption) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE sort_order=sort_order',
            [$id, $mediaId, (int)$maxOrder + 1, $data['caption'] ?? null]
        );

        return $this->success(null, 'Media attached.');
    }

    public function updateMedia(Request $request, Response $response): Response
    {
        $db      = app_database();
        $id      = (int) $request->param('id');
        $mediaId = (int) $request->param('mediaId');
        $data    = $request->json();

        $db->query(
            'UPDATE gallery_media SET caption=? WHERE gallery_id=? AND media_id=?',
            [$data['caption'] ?? null, $id, $mediaId]
        );

        return $this->success(null, 'Updated.');
    }

    public function removeMedia(Request $request, Response $response): Response
    {
        $db      = app_database();
        $id      = (int) $request->param('id');
        $mediaId = (int) $request->param('mediaId');

        $db->query('DELETE FROM gallery_media WHERE gallery_id=? AND media_id=?', [$id, $mediaId]);

        return $this->noContent();
    }

    public function reorderMedia(Request $request, Response $response): Response
    {
        $db    = app_database();
        $id    = (int) $request->param('id');
        $order = $request->json()['order'] ?? [];

        foreach ($order as $i => $mediaId) {
            $db->query(
                'UPDATE gallery_media SET sort_order=? WHERE gallery_id=? AND media_id=?',
                [$i + 1, $id, (int)$mediaId]
            );
        }

        return $this->success(null, 'Reordered.');
    }

    private function findOrFail(\App\Core\Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT g.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height
             FROM galleries g
             LEFT JOIN media m ON g.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE g.id = ? AND g.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Gallery not found.');
        return $row;
    }

    private function formatRow(array $row): array
    {
        $mediaCount = (int) app_database()->query(
            'SELECT COUNT(*) AS c FROM gallery_media gm JOIN media m ON gm.media_id=m.id AND m.deleted_at IS NULL WHERE gm.gallery_id=?',
            [(int)$row['id']]
        )->fetch()['c'];

        return [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'slug'        => $row['slug'],
            'description' => $row['description'] ?? null,
            'category'    => $row['category']    ?? null,
            'cover'       => $this->fmt->formatCover($row),
            'is_featured' => (bool)$row['is_featured'],
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'media_count' => $mediaCount,
            'sort_order'  => (int)$row['sort_order'],
            'seo_title'   => $row['seo_title']       ?? null,
            'seo_description' => $row['seo_description'] ?? null,
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at'],
        ];
    }
}
