<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Page
{
    public function __construct(private readonly Database $db) {}

    public function findBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT p.*, m.path AS og_image_path, m.alt_text AS og_image_alt,
                    m.file_name AS og_image_file, m.mime_type AS og_image_mime
             FROM pages p
             LEFT JOIN media m ON p.og_image_id = m.id AND m.deleted_at IS NULL
             WHERE p.slug = ? AND p.deleted_at IS NULL AND p.is_published = 1
             LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }

    public function findAll(): array
    {
        return $this->db->query(
            'SELECT id, title, slug, created_at, updated_at
             FROM pages WHERE deleted_at IS NULL AND is_published = 1
             ORDER BY title ASC'
        )->fetchAll();
    }
}
