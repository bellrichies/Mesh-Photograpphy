<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class TeamMemberController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT t.*, m.path AS photo_path, m.alt_text AS photo_alt,
                    m.uuid AS photo_uuid, m.original_name AS photo_original,
                    m.file_name AS photo_file, m.mime_type AS photo_mime,
                    m.file_size AS photo_size, m.width AS photo_width, m.height AS photo_height
             FROM team_members t
             LEFT JOIN media m ON t.photo_id = m.id AND m.deleted_at IS NULL
             WHERE t.deleted_at IS NULL
             ORDER BY t.sort_order ASC, t.created_at ASC'
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
            'INSERT INTO team_members (name, role, bio, photo_id, email, instagram_url, is_published, sort_order, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $payload['name'],
                $payload['role'],
                $payload['bio'],
                $payload['photo_id'],
                $payload['email'],
                $payload['instagram_url'],
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
            'UPDATE team_members SET name=?,role=?,bio=?,photo_id=?,email=?,instagram_url=?,is_published=?,sort_order=?,updated_at=NOW() WHERE id=?',
            [
                $payload['name'],
                $payload['role'],
                $payload['bio'],
                $payload['photo_id'],
                $payload['email'],
                $payload['instagram_url'],
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
        $db->query('UPDATE team_members SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function findOrFail(Database $db, int $id): array
    {
        $row = $db->query(
            'SELECT t.*, m.path AS photo_path, m.alt_text AS photo_alt,
                    m.uuid AS photo_uuid, m.original_name AS photo_original,
                    m.file_name AS photo_file, m.mime_type AS photo_mime,
                    m.file_size AS photo_size, m.width AS photo_width, m.height AS photo_height
             FROM team_members t
             LEFT JOIN media m ON t.photo_id = m.id AND m.deleted_at IS NULL
             WHERE t.id=? AND t.deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) throw new HttpException(404, 'Team member not found.');
        return $row;
    }

    private function formatRow(array $row): array
    {
        return [
            'id'            => (int) $row['id'],
            'name'          => $row['name'],
            'role'          => $row['role'] ?? null,
            'bio'           => $row['bio'] ?? null,
            'photo'         => $this->fmt->formatCover($row, 'photo'),
            'photo_id'      => isset($row['photo_id']) ? (int) $row['photo_id'] : null,
            'email'         => $row['email'] ?? null,
            'instagram_url' => $row['instagram_url'] ?? null,
            'status'        => $row['is_published'] ? 'published' : 'draft',
            'sort_order'    => (int) $row['sort_order'],
            'created_at'    => $row['created_at'],
            'updated_at'    => $row['updated_at'],
        ];
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $name         = $this->cleanText($data['name'] ?? null, 150);
        $role         = $this->cleanText($data['role'] ?? null, 150);
        $bio          = $this->cleanText($data['bio'] ?? null, 2000);
        $email        = $this->cleanText($data['email'] ?? null, 190);
        $instagram    = $this->cleanText($data['instagram_url'] ?? null, 255);
        $photoId      = $this->cleanInteger($data['photo_id'] ?? null);
        $status       = (string) ($data['status'] ?? 'draft');
        $sortOrder    = $this->cleanInteger($data['sort_order'] ?? 0);

        if ($name === null) {
            $errors['name'] = 'Name is required.';
        }

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be a valid address.';
        }

        if ($instagram !== null && !filter_var($instagram, FILTER_VALIDATE_URL)) {
            $errors['instagram_url'] = 'Instagram link must be a valid URL.';
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Status must be draft or published.';
        }

        if (array_key_exists('sort_order', $data) && $sortOrder === null) {
            $errors['sort_order'] = 'Sort order must be a whole number.';
        }

        if (($data['photo_id'] ?? null) !== null && ($data['photo_id'] ?? null) !== '' && $photoId === null) {
            $errors['photo_id'] = 'Photo must reference a valid media id.';
        } elseif ($photoId !== null && !$this->mediaImageExists($photoId)) {
            $errors['photo_id'] = 'Photo was not found in the media library.';
        }

        return [
            'errors'        => $errors,
            'name'          => $name,
            'role'          => $role,
            'bio'           => $bio,
            'email'         => $email,
            'instagram_url' => $instagram,
            'photo_id'      => $photoId,
            'status'        => in_array($status, ['draft', 'published'], true) ? $status : 'draft',
            'sort_order'    => $sortOrder ?? 0,
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
