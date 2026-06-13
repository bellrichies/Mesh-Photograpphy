<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogPostCategory
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, int>
     */
    public function categoryIdsForPost(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT category_id FROM blog_post_categories WHERE post_id = :post_id ORDER BY id ASC',
            ['post_id' => $postId]
        )->fetchAll();

        if (! is_array($rows)) {
            return [];
        }

        return array_map(static fn (array $row): int => (int) ($row['category_id'] ?? 0), $rows);
    }

    /**
     * @param array<int, int> $categoryIds
     */
    public function syncCategories(int $postId, array $categoryIds): void
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds), static fn (int $id): bool => $id > 0)));
        $this->database->query('DELETE FROM blog_post_categories WHERE post_id = :post_id', ['post_id' => $postId]);

        foreach ($categoryIds as $categoryId) {
            $this->database->query(
                'INSERT INTO blog_post_categories (post_id, category_id, created_at) VALUES (:post_id, :category_id, NOW())',
                ['post_id' => $postId, 'category_id' => $categoryId]
            );
        }
    }
}