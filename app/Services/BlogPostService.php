<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Repositories\BlogRepository;

class BlogPostService
{
    public function __construct(
        private readonly BlogRepository $blogRepository,
        private readonly BlogPost $blogPosts,
    )
    {
    }

    public function generateUniquePostSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->blogRepository->postSlugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function normalizePublishedAt(string $status, string $publishedAt, string $scheduledAt = ''): ?string
    {
        if ($status === 'published') {
            $timestamp = strtotime(trim($publishedAt)) ?: time();
            return date('Y-m-d H:i:s', $timestamp);
        }

        if ($status === 'scheduled' && trim($scheduledAt) !== '') {
            $timestamp = strtotime(trim($scheduledAt));
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }

        return null;
    }

    public function normalizeScheduledAt(string $status, string $scheduledAt): ?string
    {
        if ($status !== 'scheduled' || trim($scheduledAt) === '') {
            return null;
        }

        $timestamp = strtotime(trim($scheduledAt));
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    public function normalizeArchivedAt(string $status): ?string
    {
        return $status === 'archived' ? date('Y-m-d H:i:s') : null;
    }

    public function calculateReadingTime(string $body): ?int
    {
        $wordCount = str_word_count(trim(strip_tags($body)));
        if ($wordCount <= 0) {
            return null;
        }

        return max(1, (int) ceil($wordCount / 200));
    }

    public function findPost(int $postId): ?array
    {
        return $this->blogPosts->findById($postId);
    }

    /**
     * @param array<string, mixed> $existing
     * @param array<string, mixed> $incoming
     */
    public function hasMeaningfulRevisionChange(array $existing, array $incoming): bool
    {
        return trim((string) ($existing['title'] ?? '')) !== trim((string) ($incoming['title'] ?? ''))
            || trim((string) ($existing['excerpt'] ?? '')) !== trim((string) ($incoming['excerpt'] ?? ''))
            || trim((string) ($existing['body_long'] ?? '')) !== trim((string) ($incoming['body_long'] ?? ''));
    }
}