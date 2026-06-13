<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class MediaArchiveService
{
    public function __construct(private readonly Database $database)
    {
    }

    public function archive(int $mediaId): void
    {
        $this->database->query(
            'UPDATE media SET status = "archived", updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $mediaId]
        );
    }

    public function softDelete(int $mediaId): void
    {
        $this->database->query(
            'UPDATE media SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $mediaId]
        );
    }
}
