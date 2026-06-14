<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Service
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(): array
    {
        return $this->db->query(
            'SELECT s.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height
             FROM services s
             LEFT JOIN media m ON s.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE s.deleted_at IS NULL AND s.is_published = 1
             ORDER BY s.sort_order ASC, s.created_at ASC'
        )->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT s.*, m.path AS cover_path, m.alt_text AS cover_alt,
                    m.uuid AS cover_uuid, m.original_name AS cover_original,
                    m.file_name AS cover_file, m.mime_type AS cover_mime,
                    m.file_size AS cover_size, m.width AS cover_width,
                    m.height AS cover_height
             FROM services s
             LEFT JOIN media m ON s.cover_image_id = m.id AND m.deleted_at IS NULL
             WHERE s.slug = ? AND s.deleted_at IS NULL AND s.is_published = 1
             LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }
}
