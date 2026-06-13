<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPostRevision;

class BlogRevisionService
{
    public function __construct(private readonly BlogPostRevision $revisions)
    {
    }

    /**
     * @param array<string, mixed> $post
     */
    public function createSnapshot(int $postId, array $post, ?int $editedBy = null): int
    {
        return $this->revisions->create($postId, [
            'title' => $post['title'] ?? '',
            'excerpt' => $post['excerpt'] ?? null,
            'body_long' => $post['body_long'] ?? null,
        ], $editedBy);
    }
}