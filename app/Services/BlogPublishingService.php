<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostCategory;
use App\Models\BlogPostMedia;
use App\Models\BlogPostTag;
use App\Models\SeoMeta;

class BlogPublishingService
{
    public function __construct(
        private readonly BlogPost $blogPosts,
        private readonly BlogPostCategory $postCategories,
        private readonly BlogPostTag $postTags,
        private readonly BlogPostMedia $postMedia,
        private readonly SeoMeta $seoMeta,
        private readonly BlogRevisionService $revisionService,
        private readonly MediaUsageService $mediaUsage,
        private readonly BlogPostService $blogPostService,
    )
    {
    }

    public function resolveStatusFromAction(string $selectedStatus, string $publishAction): string
    {
        return match ($publishAction) {
            'save_draft' => 'draft',
            'publish_now' => 'published',
            'schedule_publish' => 'scheduled',
            'archive' => 'archived',
            default => $selectedStatus,
        };
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

    public function normalizeArchivedAt(string $status, ?string $existingArchivedAt = null): ?string
    {
        if ($status === 'archived') {
            return $existingArchivedAt !== null && $existingArchivedAt !== '' ? $existingArchivedAt : date('Y-m-d H:i:s');
        }

        return null;
    }

    /**
     * @param array<string, mixed> $postData
     * @param array<int, int> $categoryIds
     * @param array<int, int> $tagIds
     * @param array<string, mixed> $seo
     */
    public function createPost(array $postData, array $categoryIds, array $tagIds, array $seo): int
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $postId = $this->blogPosts->create($postData);
            $this->postCategories->syncCategories($postId, $categoryIds);
            $this->postTags->syncTags($postId, $tagIds);
            $this->seoMeta->upsert('blog_post', $postId, $seo);
            $this->syncPrimaryMediaUsage($postId, isset($postData['featured_image_id']) ? (int) $postData['featured_image_id'] : null, isset($seo['og_image']) ? (int) $seo['og_image'] : null);
            $this->revisionService->createSnapshot($postId, $postData, isset($postData['author_id']) ? (int) $postData['author_id'] : null);
            $pdo->commit();

            return $postId;
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    /**
     * @param array<string, mixed> $existing
     * @param array<string, mixed> $postData
     * @param array<int, int> $categoryIds
     * @param array<int, int> $tagIds
     * @param array<string, mixed> $seo
     */
    public function updatePost(int $postId, array $existing, array $postData, array $categoryIds, array $tagIds, array $seo, ?int $editedBy = null): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            if ($this->blogPostService->hasMeaningfulRevisionChange($existing, $postData)) {
                $this->revisionService->createSnapshot($postId, $existing, $editedBy);
            }

            $this->blogPosts->update($postId, $postData);
            $this->postCategories->syncCategories($postId, $categoryIds);
            $this->postTags->syncTags($postId, $tagIds);
            $this->seoMeta->upsert('blog_post', $postId, $seo);
            $this->syncPrimaryMediaUsage($postId, isset($postData['featured_image_id']) ? (int) $postData['featured_image_id'] : null, isset($seo['og_image']) ? (int) $seo['og_image'] : null);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function deletePost(int $postId): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $this->blogPosts->softDelete($postId);
            $this->mediaUsage->removeEntityUsage('blog_post', $postId);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function attachMedia(int $postId, int $mediaId, string $caption = ''): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            if (! $this->postMedia->exists($postId, $mediaId)) {
                $this->postMedia->attach($postId, $mediaId, $caption, $this->postMedia->nextSortOrder($postId));
            } else {
                $this->postMedia->updateAttachment($postId, $mediaId, $caption, $this->postMedia->nextSortOrder($postId));
            }

            $this->mediaUsage->registerUsage($mediaId, 'blog_post', $postId, 'supporting_media');
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function updateMedia(int $postId, int $mediaId, string $caption): void
    {
        $existing = $this->postMedia->attachmentsForPost($postId);
        $sortOrder = 0;

        foreach ($existing as $item) {
            if ((int) ($item['media_id'] ?? $item['id'] ?? 0) === $mediaId) {
                $sortOrder = (int) ($item['sort_order'] ?? 0);
                break;
            }
        }

        $this->postMedia->updateAttachment($postId, $mediaId, $caption, $sortOrder);
    }

    /**
     * @param array<int, int> $orderedMediaIds
     */
    public function reorderMedia(int $postId, array $orderedMediaIds): void
    {
        $position = 1;
        foreach ($orderedMediaIds as $mediaId) {
            if ((int) $mediaId > 0) {
                $this->postMedia->setSortOrder($postId, (int) $mediaId, $position);
                $position++;
            }
        }
    }

    public function removeMedia(int $postId, int $mediaId): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $this->postMedia->remove($postId, $mediaId);
            $this->mediaUsage->removeFieldUsage('blog_post', $postId, 'supporting_media', $mediaId);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    private function syncPrimaryMediaUsage(int $postId, ?int $featuredImageId, ?int $ogImageId): void
    {
        $this->mediaUsage->removeFieldUsage('blog_post', $postId, 'featured_image');
        $this->mediaUsage->removeFieldUsage('blog_post', $postId, 'og_image');

        if ($featuredImageId !== null && $featuredImageId > 0) {
            $this->mediaUsage->registerUsage($featuredImageId, 'blog_post', $postId, 'featured_image');
        }

        if ($ogImageId !== null && $ogImageId > 0) {
            $this->mediaUsage->registerUsage($ogImageId, 'blog_post', $postId, 'og_image');
        }
    }
}