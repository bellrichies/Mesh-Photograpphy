<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogPostRevision
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM blog_post_revisions WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forPost(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT bpr.*, u.first_name, u.last_name
             FROM blog_post_revisions bpr
             LEFT JOIN users u ON u.id = bpr.edited_by
             WHERE bpr.post_id = :post_id
             ORDER BY bpr.id DESC',
            ['post_id' => $postId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function create(int $postId, array $snapshot, ?int $editedBy = null): int
    {
        $this->database->query(
            'INSERT INTO blog_post_revisions (post_id, edited_by, title, excerpt, body_long, created_at)
             VALUES (:post_id, :edited_by, :title, :excerpt, :body_long, NOW())',
            [
                'post_id' => $postId,
                'edited_by' => $editedBy,
                'title' => $snapshot['title'] ?? '',
                'excerpt' => $snapshot['excerpt'] ?? null,
                'body_long' => $snapshot['body_long'] ?? null,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }
}