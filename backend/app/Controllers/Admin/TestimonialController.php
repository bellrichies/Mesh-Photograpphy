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
            'SELECT t.*, m.id AS cover_id, m.path AS cover_path, m.alt_text AS cover_alt,
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
        $payload = $this->normalizePayload($data);
        if ($payload['errors']) {
            return $this->validationError($payload['errors']);
        }

        $db = app_database();
        $db->query(
            'INSERT INTO testimonials (client_name, client_title, quote, rating, avatar_id, is_published, sort_order, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $payload['client_name'],
                $payload['client_role'],
                $payload['body'],
                $payload['rating'],
                $payload['portrait_id'],
                $payload['status'] === 'published' ? 1 : 0,
                $payload['sort_order'],
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
        $payload = $this->normalizePayload($data);
        if ($payload['errors']) {
            return $this->validationError($payload['errors']);
        }

        $db->query(
            'UPDATE testimonials SET client_name=?,client_title=?,quote=?,rating=?,avatar_id=?,is_published=?,sort_order=?,updated_at=NOW() WHERE id=?',
            [
                $payload['client_name'],
                $payload['client_role'],
                $payload['body'],
                $payload['rating'],
                $payload['portrait_id'],
                $payload['status'] === 'published' ? 1 : 0,
                $payload['sort_order'],
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
            'SELECT t.*, m.id AS cover_id, m.path AS cover_path, m.alt_text AS cover_alt,
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
            'portrait_id' => isset($row['avatar_id']) ? (int) $row['avatar_id'] : null,
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'sort_order'  => (int)$row['sort_order'],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at'],
        ];
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $clientName = $this->cleanText($data['client_name'] ?? null, 150);
        $clientRole = $this->cleanText($data['client_role'] ?? null, 150);
        $body = $this->cleanNullableString($data['body'] ?? null);
        $rating = $this->cleanInteger($data['rating'] ?? 5);
        $portraitId = $this->cleanInteger($data['portrait_id'] ?? null);
        $status = (string) ($data['status'] ?? 'draft');
        $sortOrder = $this->cleanInteger($data['sort_order'] ?? 0);

        if ($clientName === null) {
            $errors['client_name'] = 'Client name is required.';
        }

        if ($body === null) {
            $errors['body'] = 'Testimonial is required.';
        }

        if ($rating === null || $rating < 1 || $rating > 5) {
            $errors['rating'] = 'Rating must be a whole number between 1 and 5.';
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Status must be draft or published.';
        }

        if (array_key_exists('sort_order', $data) && $sortOrder === null) {
            $errors['sort_order'] = 'Sort order must be a whole number.';
        }

        if (($data['portrait_id'] ?? null) !== null && ($data['portrait_id'] ?? null) !== '' && $portraitId === null) {
            $errors['portrait_id'] = 'Recipient image must reference a valid media id.';
        } elseif ($portraitId !== null && !$this->mediaImageExists($portraitId)) {
            $errors['portrait_id'] = 'Recipient image was not found in the media library.';
        }

        return [
            'errors' => $errors,
            'client_name' => $clientName,
            'client_role' => $clientRole,
            'body' => $body,
            'rating' => $rating ?? 5,
            'portrait_id' => $portraitId,
            'status' => in_array($status, ['draft', 'published'], true) ? $status : 'draft',
            'sort_order' => $sortOrder ?? 0,
        ];
    }

    private function cleanText(mixed $value, int $max): ?string
    {
        $value = $this->cleanNullableString($value);
        if ($value === null) {
            return null;
        }

        return mb_substr(strip_tags($value), 0, $max);
    }

    private function cleanNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function cleanInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : null;
    }

    private function mediaImageExists(int $id): bool
    {
        return (bool) app_database()
            ->query(
                "SELECT id FROM media WHERE id = ? AND deleted_at IS NULL AND mime_type LIKE 'image/%' LIMIT 1",
                [$id]
            )
            ->fetch();
    }
}
