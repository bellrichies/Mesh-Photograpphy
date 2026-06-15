<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class TestimonialController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT t.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width, m.height AS cover_height
             FROM testimonials t
             LEFT JOIN media m ON t.avatar_id = m.id AND m.deleted_at IS NULL
             WHERE t.deleted_at IS NULL
             ORDER BY t.sort_order ASC, t.created_at DESC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, [
            'client_name' => 'required|string|max:255',
            'body'        => 'required|string',
        ]);
        if ($err) return $err;

        $db = app_database();
        $db->query(
            'INSERT INTO testimonials (client_name, client_title, quote, rating, avatar_id, is_published, sort_order, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $data['client_name'],
                $data['client_role']  ?? null,
                $data['body'],
                min(5, max(1, (int)($data['rating'] ?? 5))),
                $data['portrait_id']  ?? null,
                ($data['status'] ?? 'draft') === 'published' ? 1 : 0,
                $data['sort_order']   ?? 0,
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
        $err  = $this->validate($data, ['client_name' => 'required|string|max:255', 'body' => 'required|string']);
        if ($err) return $err;

        $db->query(
            'UPDATE testimonials SET client_name=?,client_title=?,quote=?,rating=?,avatar_id=?,is_published=?,sort_order=?,updated_at=NOW() WHERE id=?',
            [
                $data['client_name'],
                $data['client_role']  ?? null,
                $data['body'],
                min(5, max(1, (int)($data['rating'] ?? 5))),
                $data['portrait_id']  ?? null,
                ($data['status'] ?? 'draft') === 'published' ? 1 : 0,
                $data['sort_order']   ?? 0,
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
        $db->query('UPDATE testimonials SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function findOrFail(\App\Core\Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT t.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width, m.height AS cover_height
             FROM testimonials t
             LEFT JOIN media m ON t.avatar_id = m.id AND m.deleted_at IS NULL
             WHERE t.id=? AND t.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Testimonial not found.');
        return $row;
    }

    private function formatRow(array $row): array
    {
        return [
            'id'          => (int)$row['id'],
            'client_name' => $row['client_name'],
            'client_role' => $row['client_title'] ?? null,
            'body'        => $row['quote'],
            'rating'      => (int)$row['rating'],
            'portrait'    => $this->fmt->formatCover($row, 'cover'),
            'portrait_id' => $row['avatar_id']    ?? null,
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'sort_order'  => (int)$row['sort_order'],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at'],
        ];
    }
}
