<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Testimonial
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(): array
    {
        return $this->db->query(
            'SELECT t.*, m.id AS portrait_id, m.path AS portrait_path, m.alt_text AS portrait_alt,
                    m.uuid AS portrait_uuid, m.original_name AS portrait_original,
                    m.file_name AS portrait_file, m.mime_type AS portrait_mime,
                    m.file_size AS portrait_size, m.width AS portrait_width,
                    m.height AS portrait_height
             FROM testimonials t
             LEFT JOIN media m ON t.avatar_id = m.id AND m.deleted_at IS NULL
             WHERE t.deleted_at IS NULL AND t.is_published = 1
             ORDER BY t.sort_order ASC, t.created_at DESC'
        )->fetchAll();
    }
}
