<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class GalleryCategoryMap
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byGalleryId(int $galleryId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM gallery_category_map WHERE gallery_id = :gallery_id ORDER BY id ASC',
            ['gallery_id' => $galleryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byCategoryId(int $categoryId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM gallery_category_map WHERE category_id = :category_id ORDER BY id ASC',
            ['category_id' => $categoryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<int, int> $categoryIds
     */
    public function syncGalleryCategories(int $galleryId, array $categoryIds): void
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): int => (int) $value,
            $categoryIds
        ), static fn (int $value): bool => $value > 0)));

        $this->database->query('DELETE FROM gallery_category_map WHERE gallery_id = :gallery_id', ['gallery_id' => $galleryId]);

        foreach ($normalized as $categoryId) {
            $this->database->query(
                'INSERT INTO gallery_category_map (gallery_id, category_id, created_at) VALUES (:gallery_id, :category_id, NOW())',
                [
                    'gallery_id' => $galleryId,
                    'category_id' => $categoryId,
                ]
            );
        }
    }
}