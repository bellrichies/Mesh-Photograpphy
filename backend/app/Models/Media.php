<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Media
{
    public function __construct(private readonly Database $db) {}

    public function findById(int $id): ?array
    {
        return $this->db->query(
            'SELECT * FROM media WHERE id = ? AND deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch() ?: null;
    }

    public function buildUrl(array $media): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . '/uploads/' . ltrim($media['path'], '/');
    }

    public function format(array $media): array
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        $path = ltrim($media['path'], '/');
        return [
            'id'            => (int) $media['id'],
            'uuid'          => $media['uuid'],
            'url'           => $base . '/uploads/' . $path,
            'thumb_url'     => $base . '/uploads/thumb_' . $path,
            'original_name' => $media['original_name'],
            'stored_name'   => $media['file_name'],
            'directory'     => dirname($media['path']),
            'mime_type'     => $media['mime_type'],
            'file_type'     => $this->resolveFileType($media['mime_type']),
            'size_bytes'    => (int) $media['file_size'],
            'width'         => $media['width'] ? (int) $media['width'] : null,
            'height'        => $media['height'] ? (int) $media['height'] : null,
            'alt_text'      => $media['alt_text'],
            'title'         => null,
            'caption'       => null,
            'status'        => 'active',
            'is_in_use'     => true,
            'created_at'    => $media['created_at'],
        ];
    }

    private function resolveFileType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }
}
