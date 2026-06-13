<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SeoMeta
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findFor(string $entityType, int $entityId): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM seo_meta WHERE entity_type = :entity_type AND entity_id = :entity_id LIMIT 1',
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function upsert(string $entityType, int $entityId, array $data): void
    {
        $this->database->query(
            'INSERT INTO seo_meta (
                entity_type, entity_id, meta_title, meta_description, og_title, og_description,
                og_image, canonical_url, robots_index, robots_follow, created_at, updated_at
            ) VALUES (
                :entity_type, :entity_id, :meta_title, :meta_description, :og_title, :og_description,
                :og_image, :canonical_url, :robots_index, :robots_follow, NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                meta_title = VALUES(meta_title),
                meta_description = VALUES(meta_description),
                og_title = VALUES(og_title),
                og_description = VALUES(og_description),
                og_image = VALUES(og_image),
                canonical_url = VALUES(canonical_url),
                robots_index = VALUES(robots_index),
                robots_follow = VALUES(robots_follow),
                updated_at = NOW()',
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'og_title' => $data['og_title'] ?? null,
                'og_description' => $data['og_description'] ?? null,
                'og_image' => $data['og_image'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'robots_index' => (int) ($data['robots_index'] ?? 1),
                'robots_follow' => (int) ($data['robots_follow'] ?? 1),
            ]
        );
    }
}
