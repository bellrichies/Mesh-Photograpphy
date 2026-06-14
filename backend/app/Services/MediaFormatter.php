<?php

declare(strict_types=1);

namespace App\Services;

class MediaFormatter
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
    }

    public function formatCover(?array $row, string $prefix = 'cover'): ?array
    {
        if (empty($row[$prefix . '_path'])) return null;

        $path = $row[$prefix . '_path'];
        return [
            'id'            => (int) ($row['cover_image_id'] ?? 0),
            'uuid'          => $row[$prefix . '_uuid'] ?? '',
            'url'           => $this->baseUrl . '/uploads/' . ltrim($path, '/'),
            'thumb_url'     => $this->baseUrl . '/uploads/thumb_' . ltrim($path, '/'),
            'original_name' => $row[$prefix . '_original'] ?? '',
            'stored_name'   => $row[$prefix . '_file'] ?? '',
            'mime_type'     => $row[$prefix . '_mime'] ?? 'image/jpeg',
            'file_type'     => 'image',
            'size_bytes'    => (int) ($row[$prefix . '_size'] ?? 0),
            'width'         => isset($row[$prefix . '_width']) ? (int) $row[$prefix . '_width'] : null,
            'height'        => isset($row[$prefix . '_height']) ? (int) $row[$prefix . '_height'] : null,
            'alt_text'      => $row[$prefix . '_alt'] ?? null,
        ];
    }

    public function formatMedia(array $media): array
    {
        $path = ltrim($media['path'], '/');
        return [
            'id'            => (int) $media['id'],
            'uuid'          => $media['uuid'],
            'url'           => $this->baseUrl . '/uploads/' . $path,
            'thumb_url'     => $this->baseUrl . '/uploads/thumb_' . $path,
            'original_name' => $media['original_name'],
            'stored_name'   => $media['file_name'],
            'mime_type'     => $media['mime_type'],
            'file_type'     => str_starts_with($media['mime_type'], 'image/') ? 'image' : 'document',
            'size_bytes'    => (int) $media['file_size'],
            'width'         => isset($media['width']) ? (int) $media['width'] : null,
            'height'        => isset($media['height']) ? (int) $media['height'] : null,
            'alt_text'      => $media['alt_text'] ?? null,
            'sort_order'    => (int) ($media['sort_order'] ?? 0),
            'caption'       => $media['caption'] ?? null,
        ];
    }
}
