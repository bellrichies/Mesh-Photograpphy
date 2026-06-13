<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogPostMedia
{
    public function __construct(private readonly Database $database)
    {
    }

    public function exists(int $postId, int $mediaId): bool
    {
        return (int) $this->database->query(
            'SELECT COUNT(*) FROM blog_post_media WHERE post_id = :post_id AND media_id = :media_id',
            ['post_id' => $postId, 'media_id' => $mediaId]
        )->fetchColumn() > 0;
    }

    public function nextSortOrder(int $postId): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM blog_post_media WHERE post_id = :post_id',
            ['post_id' => $postId]
        )->fetchColumn();
    }

    public function attach(int $postId, int $mediaId, string $caption = '', int $sortOrder = 0): void
    {
        $this->database->query(
            'INSERT INTO blog_post_media (post_id, media_id, caption, sort_order, created_at, updated_at)
             VALUES (:post_id, :media_id, :caption, :sort_order, NOW(), NOW())',
            [
                'post_id' => $postId,
                'media_id' => $mediaId,
                'caption' => $caption !== '' ? $caption : null,
                'sort_order' => $sortOrder,
            ]
        );
    }

    public function updateAttachment(int $postId, int $mediaId, string $caption, int $sortOrder): void
    {
        $this->database->query(
            'UPDATE blog_post_media
             SET caption = :caption,
                 sort_order = :sort_order,
                 updated_at = NOW()
             WHERE post_id = :post_id AND media_id = :media_id',
            [
                'post_id' => $postId,
                'media_id' => $mediaId,
                'caption' => $caption !== '' ? $caption : null,
                'sort_order' => $sortOrder,
            ]
        );
    }

    public function setSortOrder(int $postId, int $mediaId, int $sortOrder): void
    {
        $this->database->query(
            'UPDATE blog_post_media
             SET sort_order = :sort_order,
                 updated_at = NOW()
             WHERE post_id = :post_id AND media_id = :media_id',
            [
                'post_id' => $postId,
                'media_id' => $mediaId,
                'sort_order' => $sortOrder,
            ]
        );
    }

    public function remove(int $postId, int $mediaId): void
    {
        $this->database->query(
            'DELETE FROM blog_post_media WHERE post_id = :post_id AND media_id = :media_id',
            ['post_id' => $postId, 'media_id' => $mediaId]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function attachmentsForPost(int $postId): array
    {
        $rows = $this->database->query(
            'SELECT bpm.*, m.*
             FROM blog_post_media bpm
             INNER JOIN media m ON m.id = bpm.media_id
             WHERE bpm.post_id = :post_id AND m.deleted_at IS NULL
             ORDER BY bpm.sort_order ASC, bpm.id ASC',
            ['post_id' => $postId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function detailedByPostId(int $postId): array
    {
        return $this->attachmentsForPost($postId);
    }
}