<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class ServiceController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $db   = app_database();
        $rows = $db->query(
            'SELECT s.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width, m.height AS cover_height
             FROM services s
             LEFT JOIN media m ON s.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE s.deleted_at IS NULL
             ORDER BY s.sort_order ASC, s.title ASC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function show(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $row = $this->findOrFail($db, $id);
        return $this->success($this->formatRow($row));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, ['title' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        $db = app_database();
        if ($db->query('SELECT id FROM services WHERE slug=? AND deleted_at IS NULL', [$data['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug already in use.']]);
        }

        $db->query(
            'INSERT INTO services (title, slug, short_desc, description, price_label, cover_image_id, is_published, sort_order, seo_title, seo_description, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $data['title'],
                $data['slug'],
                $data['short_description'] ?? null,
                $data['description']       ?? null,
                $data['price_display']     ?? null,
                $data['cover_image_id']    ?? null,
                !empty($data['is_published']) ? 1 : 0,
                $data['sort_order']        ?? 0,
                $data['seo_title']         ?? null,
                $data['seo_description']   ?? null,
            ]
        );

        $id = (int) $db->lastInsertId();
        return $this->created($this->formatRow($this->findOrFail($db, $id)));
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $this->findOrFail($db, $id);
        $data = $request->json();
        $err  = $this->validate($data, ['title' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        if ($db->query('SELECT id FROM services WHERE slug=? AND id!=? AND deleted_at IS NULL', [$data['slug'], $id])->fetch()) {
            return $this->validationError(['slug' => ['Slug already in use.']]);
        }

        $db->query(
            'UPDATE services SET title=?,slug=?,short_desc=?,description=?,price_label=?,cover_image_id=?,is_published=?,sort_order=?,seo_title=?,seo_description=?,updated_at=NOW() WHERE id=?',
            [
                $data['title'],
                $data['slug'],
                $data['short_description'] ?? null,
                $data['description']       ?? null,
                $data['price_display']     ?? null,
                $data['cover_image_id']    ?? null,
                !empty($data['is_published']) ? 1 : 0,
                $data['sort_order']        ?? 0,
                $data['seo_title']         ?? null,
                $data['seo_description']   ?? null,
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
        $db->query('UPDATE services SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function findOrFail(\App\Core\Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT s.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width, m.height AS cover_height
             FROM services s
             LEFT JOIN media m ON s.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE s.id=? AND s.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Service not found.');
        return $row;
    }

    private function formatRow(array $row): array
    {
        return [
            'id'                => (int)$row['id'],
            'title'             => $row['title'],
            'slug'              => $row['slug'],
            'short_description' => $row['short_desc']      ?? null,
            'description'       => $row['description']     ?? null,
            'price_display'     => $row['price_label']     ?? null,
            'cover'             => $this->fmt->formatCover($row),
            'cover_image_id'    => $row['cover_image_id']  ?? null,
            'status'            => $row['is_published'] ? 'published' : 'draft',
            'sort_order'        => (int)$row['sort_order'],
            'seo_title'         => $row['seo_title']       ?? null,
            'seo_description'   => $row['seo_description'] ?? null,
            'created_at'        => $row['created_at'],
            'updated_at'        => $row['updated_at'],
        ];
    }
}
