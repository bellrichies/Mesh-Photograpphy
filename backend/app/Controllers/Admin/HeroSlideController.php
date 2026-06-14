<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class HeroSlideController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT h.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width, m.height AS cover_height
             FROM hero_slides h
             LEFT JOIN media m ON h.background_image_id = m.id AND m.deleted_at IS NULL
             WHERE h.deleted_at IS NULL
             ORDER BY h.sort_order ASC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, ['title' => 'required|string|max:255']);
        if ($err) return $err;

        $db = app_database();
        $db->query(
            'INSERT INTO hero_slides (title, subtitle, background_image_id, cta_label, cta_url, sort_order, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $data['title'],
                $data['subtitle']             ?? null,
                $data['background_image_id']  ?? null,
                $data['cta_label']            ?? null,
                $data['cta_url']              ?? null,
                $data['sort_order']           ?? 0,
                $data['status']               ?? 'draft',
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
        $err  = $this->validate($data, ['title' => 'required|string|max:255']);
        if ($err) return $err;

        $db->query(
            'UPDATE hero_slides SET title=?,subtitle=?,background_image_id=?,cta_label=?,cta_url=?,sort_order=?,status=?,updated_at=NOW() WHERE id=?',
            [
                $data['title'],
                $data['subtitle']            ?? null,
                $data['background_image_id'] ?? null,
                $data['cta_label']           ?? null,
                $data['cta_url']             ?? null,
                $data['sort_order']          ?? 0,
                $data['status']              ?? 'draft',
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
        $db->query('UPDATE hero_slides SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function findOrFail(\App\Core\Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT h.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width, m.height AS cover_height
             FROM hero_slides h
             LEFT JOIN media m ON h.background_image_id = m.id AND m.deleted_at IS NULL
             WHERE h.id=? AND h.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Hero slide not found.');
        return $row;
    }

    private function formatRow(array $row): array
    {
        return [
            'id'                   => (int)$row['id'],
            'title'                => $row['title'],
            'subtitle'             => $row['subtitle']            ?? null,
            'background_image'     => $this->fmt->formatCover($row),
            'background_image_id'  => $row['background_image_id'] ?? null,
            'cta_label'            => $row['cta_label']           ?? null,
            'cta_url'              => $row['cta_url']             ?? null,
            'sort_order'           => (int)$row['sort_order'],
            'status'               => $row['status'],
            'created_at'           => $row['created_at'],
            'updated_at'           => $row['updated_at'],
        ];
    }
}
