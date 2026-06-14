<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class HeroSlide
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(): array
    {
        return $this->db->query(
            'SELECT hs.*, m.path AS image_path, m.alt_text AS image_alt,
                    m.uuid AS image_uuid, m.original_name AS image_original,
                    m.file_name AS image_file, m.mime_type AS image_mime,
                    m.file_size AS image_size, m.width AS image_width,
                    m.height AS image_height
             FROM hero_slides hs
             LEFT JOIN media m ON hs.image_id = m.id AND m.deleted_at IS NULL
             WHERE hs.deleted_at IS NULL AND hs.is_published = 1
             ORDER BY hs.sort_order ASC'
        )->fetchAll();
    }
}
