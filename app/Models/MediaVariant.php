<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class MediaVariant
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
            'SELECT * FROM media_variants WHERE media_id = :media_id ORDER BY id ASC',
            ['media_id' => $mediaId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function findByKey(int $mediaId, string $variantKey): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM media_variants WHERE media_id = :media_id AND variant_key = :variant_key LIMIT 1',
            [
                'media_id' => $mediaId,
                'variant_key' => $variantKey,
            ]
        )->fetch();

        return is_array($row) ? $row : null;
    }
}
