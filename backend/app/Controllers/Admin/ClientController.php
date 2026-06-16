<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class ClientController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT c.*, m.path AS logo_path, m.alt_text AS logo_alt,
                    m.uuid AS logo_uuid, m.original_name AS logo_original,
                    m.file_name AS logo_file, m.mime_type AS logo_mime,
                    m.file_size AS logo_size, m.width AS logo_width, m.height AS logo_height
             FROM clients c
             LEFT JOIN media m ON c.logo_id = m.id AND m.deleted_at IS NULL
             WHERE c.deleted_at IS NULL
             ORDER BY c.sort_order ASC, c.created_at ASC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function store(Request $request, Response $response): Response
    {
        $payload = $this->normalizePayload($request->json());
        if ($payload['errors']) {
            return $this->validationError($payload['errors']);
        }

        $db = app_database();
        $db->query(
            'INSERT INTO clients (name, website_url, logo_id, is_published, sort_order, created_at, updated_at)
             VALUES (?,?,?,?,?,NOW(),NOW())',
            [
                $payload['name'],
                $payload['website_url'],
                $payload['logo_id'],
                $payload['status'] === 'published' ? 1 : 0,
                $payload['sort_order'],
            ]
        );

        $id = (int) $db->lastInsertId();
        return $this->created($this->formatRow($this->findOrFail($db, $id)));
    }

    public function update(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        $this->findOrFail($db, $id);

        $payload = $this->normalizePayload($request->json());
        if ($payload['errors']) {
            return $this->validationError($payload['errors']);
        }

        $db->query(
            'UPDATE clients SET name=?,website_url=?,logo_id=?,is_published=?,sort_order=?,updated_at=NOW() WHERE id=?',
            [
                $payload['name'],
                $payload['website_url'],
                $payload['logo_id'],
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
        $db->query('UPDATE clients SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function findOrFail(Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT c.*, m.path AS logo_path, m.alt_text AS logo_alt,
                    m.uuid AS logo_uuid, m.original_name AS logo_original,
                    m.file_name AS logo_file, m.mime_type AS logo_mime,
                    m.file_size AS logo_size, m.width AS logo_width, m.height AS logo_height
             FROM clients c
             LEFT JOIN media m ON c.logo_id = m.id AND m.deleted_at IS NULL
             WHERE c.id=? AND c.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Client not found.');
        return $row;
    }

    private function formatRow(array $row): array
    {
        return [
            'id'          => (int) $row['id'],
            'name'        => $row['name'],
            'website_url' => $row['website_url'] ?? null,
            'logo'        => $this->fmt->formatCover($row, 'logo'),
            'logo_id'     => isset($row['logo_id']) ? (int) $row['logo_id'] : null,
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'sort_order'  => (int) $row['sort_order'],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at'],
        ];
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $name      = $this->cleanText($data['name'] ?? null, 150);
        $website   = $this->cleanText($data['website_url'] ?? null, 255);
        $logoId    = $this->cleanInteger($data['logo_id'] ?? null);
        $status    = (string) ($data['status'] ?? 'draft');
        $sortOrder = $this->cleanInteger($data['sort_order'] ?? 0);

        if ($name === null) {
            $errors['name'] = 'Client name is required.';
        }

        if ($website !== null && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors['website_url'] = 'Website must be a valid URL.';
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Status must be draft or published.';
        }

        if (array_key_exists('sort_order', $data) && $sortOrder === null) {
            $errors['sort_order'] = 'Sort order must be a whole number.';
        }

        if (($data['logo_id'] ?? null) !== null && ($data['logo_id'] ?? null) !== '' && $logoId === null) {
            $errors['logo_id'] = 'Logo must reference a valid media id.';
        } elseif ($logoId !== null && !$this->mediaImageExists($logoId)) {
            $errors['logo_id'] = 'Logo was not found in the media library.';
        }

        return [
            'errors'      => $errors,
            'name'        => $name,
            'website_url' => $website,
            'logo_id'     => $logoId,
            'status'      => in_array($status, ['draft', 'published'], true) ? $status : 'draft',
            'sort_order'  => $sortOrder ?? 0,
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
