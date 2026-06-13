<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Media
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM media WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findByUuid(string $uuid): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM media WHERE uuid = :uuid AND deleted_at IS NULL LIMIT 1',
            ['uuid' => $uuid]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function variants(int $mediaId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM media_variants WHERE media_id = :media_id ORDER BY id ASC',
            ['media_id' => $mediaId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function usage(int $mediaId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM media_usage_map WHERE media_id = :media_id ORDER BY id ASC',
            ['media_id' => $mediaId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}
