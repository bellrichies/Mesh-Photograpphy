<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Client
{
    public function __construct(private readonly Database $db) {}

    public function findPublished(): array
    {
        return $this->db->query(
            'SELECT c.*, m.path AS logo_path, m.alt_text AS logo_alt,
                    m.uuid AS logo_uuid, m.original_name AS logo_original,
                    m.file_name AS logo_file, m.mime_type AS logo_mime,
                    m.file_size AS logo_size, m.width AS logo_width,
                    m.height AS logo_height
             FROM clients c
             LEFT JOIN media m ON c.logo_id = m.id AND m.deleted_at IS NULL
             WHERE c.deleted_at IS NULL AND c.is_published = 1
             ORDER BY c.sort_order ASC, c.created_at ASC'
        )->fetchAll();
    }
}
