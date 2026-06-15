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
        $uuid = $row[$prefix . '_uuid'] ?? null;

        return [
            'id'            => (int) ($row[$prefix . '_id'] ?? $row['cover_image_id'] ?? 0),
            'uuid'          => $uuid ?? '',
            'url'           => $this->baseUrl . '/uploads/' . ltrim($path, '/'),
            'thumb_url'     => $this->thumbUrl($path, $uuid),
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
        $uuid = $media['uuid'] ?? null;

        return [
            'id'            => (int) $media['id'],
            'uuid'          => $uuid ?? '',
            'url'           => $this->baseUrl . '/uploads/' . $path,
            'thumb_url'     => $this->thumbUrl($path, $uuid),
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

    /**
     * Returns the URL for a 320 px WebP thumbnail if one was generated during
     * upload ({uuid}_320.webp in the same directory), otherwise falls back to
     * the original file URL so images are never silently broken.
     */
    private function thumbUrl(string $path, ?string $uuid): string
    {
        $path = ltrim($path, '/');

        if ($uuid) {
            $dir       = dirname($path);
            $thumbPath = ($dir === '.' ? '' : $dir . '/') . $uuid . '_320.webp';
            $diskRoot  = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
            if (is_file($diskRoot . '/public/uploads/' . $thumbPath)) {
                return $this->baseUrl . '/uploads/' . $thumbPath;
            }
        }

        // No thumbnail variant on disk — serve the original
        return $this->baseUrl . '/uploads/' . $path;
    }
}
