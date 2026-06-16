<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class TeamMember
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(): array
    {
        return $this->db->query(
            'SELECT t.*, m.path AS photo_path, m.alt_text AS photo_alt,
                    m.uuid AS photo_uuid, m.original_name AS photo_original,
                    m.file_name AS photo_file, m.mime_type AS photo_mime,
                    m.file_size AS photo_size, m.width AS photo_width,
                    m.height AS photo_height
             FROM team_members t
             LEFT JOIN media m ON t.photo_id = m.id AND m.deleted_at IS NULL
             WHERE t.deleted_at IS NULL AND t.is_published = 1
             ORDER BY t.sort_order ASC, t.created_at ASC'
        )->fetchAll();
    }
}
