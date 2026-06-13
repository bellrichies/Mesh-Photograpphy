<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class MediaUsageMap
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forMedia(int $mediaId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM media_usage_map WHERE media_id = :media_id ORDER BY id ASC',
            ['media_id' => $mediaId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forEntity(string $entityType, int $entityId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM media_usage_map WHERE entity_type = :entity_type AND entity_id = :entity_id ORDER BY id ASC',
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function attach(int $mediaId, string $entityType, int $entityId, string $fieldName): void
    {
        $this->database->query(
            'INSERT IGNORE INTO media_usage_map (media_id, entity_type, entity_id, field_name, created_at)
             VALUES (:media_id, :entity_type, :entity_id, :field_name, NOW())',
            [
                'media_id' => $mediaId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'field_name' => $fieldName,
            ]
        );
    }

    public function detachField(string $entityType, int $entityId, string $fieldName, ?int $mediaId = null): void
    {
        $sql = 'DELETE FROM media_usage_map WHERE entity_type = :entity_type AND entity_id = :entity_id AND field_name = :field_name';
        $params = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'field_name' => $fieldName,
        ];

        if ($mediaId !== null && $mediaId > 0) {
            $sql .= ' AND media_id = :media_id';
            $params['media_id'] = $mediaId;
        }

        $this->database->query($sql, $params);
    }

    public function detachEntity(string $entityType, int $entityId): void
    {
        $this->database->query(
            'DELETE FROM media_usage_map WHERE entity_type = :entity_type AND entity_id = :entity_id',
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]
        );
    }
}
