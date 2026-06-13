<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogPostTag
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, int>
     */
    public function tagIdsForPost(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT tag_id FROM blog_post_tags WHERE post_id = :post_id ORDER BY id ASC',
            ['post_id' => $postId]
        )->fetchAll();

        if (! is_array($rows)) {
            return [];
        }

        return array_map(static fn (array $row): int => (int) ($row['tag_id'] ?? 0), $rows);
    }

    /**
     * @param array<int, int> $tagIds
     */
    public function syncTags(int $postId, array $tagIds): void
    {
        $tagIds = array_values(array_unique(array_filter(array_map('intval', $tagIds), static fn (int $id): bool => $id > 0)));
        $this->database->query('DELETE FROM blog_post_tags WHERE post_id = :post_id', ['post_id' => $postId]);

        foreach ($tagIds as $tagId) {
            $this->database->query(
                'INSERT INTO blog_post_tags (post_id, tag_id, created_at) VALUES (:post_id, :tag_id, NOW())',
                ['post_id' => $postId, 'tag_id' => $tagId]
            );
        }
    }
}