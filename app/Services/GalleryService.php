<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gallery;
use App\Models\GalleryCategoryMap;
use App\Models\GalleryMedia;
use App\Models\MediaUsageMap;
use App\Repositories\GalleryRepository;

class GalleryService
{
    public function __construct(
        private readonly GalleryRepository $galleries,
        private readonly Gallery $galleryModel,
        private readonly GalleryCategoryMap $categoryMap,
        private readonly GalleryMedia $galleryMedia,
        private readonly MediaUsageService $mediaUsage,
    )
    {
    }

    public function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->galleries->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function normalizePublishedAt(string $status, string $publishedAt): ?string
    {
        $publishedAt = trim($publishedAt);

        if ($status !== 'published') {
            return null;
        }

        if ($publishedAt === '') {
            return date('Y-m-d H:i:s');
        }

        $timestamp = strtotime($publishedAt);
        if ($timestamp === false) {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * @param array<string, mixed> $galleryData
     * @param array<int, int> $categoryIds
     */
    public function createGallery(array $galleryData, array $categoryIds): int
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $galleryId = $this->galleryModel->create($galleryData);
            $this->categoryMap->syncGalleryCategories($galleryId, $categoryIds);
            $this->syncCoverUsage($galleryId, isset($galleryData['cover_media_id']) ? (int) $galleryData['cover_media_id'] : null);
            $pdo->commit();
            return $galleryId;
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    /**
     * @param array<string, mixed> $galleryData
     * @param array<int, int> $categoryIds
     */
    public function updateGallery(int $galleryId, array $galleryData, array $categoryIds): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $this->galleryModel->update($galleryId, $galleryData);
            $this->categoryMap->syncGalleryCategories($galleryId, $categoryIds);
            $this->syncCoverUsage($galleryId, isset($galleryData['cover_media_id']) ? (int) $galleryData['cover_media_id'] : null);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function deleteGallery(int $galleryId): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $this->galleryModel->softDelete($galleryId);
            $this->mediaUsage->removeEntityUsage('gallery', $galleryId);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function attachMedia(int $galleryId, int $mediaId, string $caption = '', bool $isFeatured = false): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            if ($isFeatured) {
                $this->galleryMedia->clearFeatured($galleryId);
            }

            if (! $this->galleryMedia->exists($galleryId, $mediaId)) {
                $this->galleryMedia->attach($galleryId, $mediaId, $caption, $this->galleryMedia->nextSortOrder($galleryId), $isFeatured);
            } else {
                $this->galleryMedia->updateAttachment($galleryId, $mediaId, $caption, $isFeatured);
            }

            $this->mediaUsage->registerUsage($mediaId, 'gallery', $galleryId, 'gallery_media');
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function updateGalleryMedia(int $galleryId, int $mediaId, string $caption, bool $isFeatured): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            if ($isFeatured) {
                $this->galleryMedia->clearFeatured($galleryId);
            }

            $this->galleryMedia->updateAttachment($galleryId, $mediaId, $caption, $isFeatured);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    /**
     * @param array<int, int> $orderedMediaIds
     */
    public function reorderMedia(int $galleryId, array $orderedMediaIds): void
    {
        $position = 1;
        foreach ($orderedMediaIds as $mediaId) {
            if ((int) $mediaId > 0) {
                $this->galleryMedia->setSortOrder($galleryId, (int) $mediaId, $position);
                $position++;
            }
        }
    }

    public function removeMedia(int $galleryId, int $mediaId): void
    {
        $pdo = app_database()->connection();
        $pdo->beginTransaction();

        try {
            $this->galleryMedia->remove($galleryId, $mediaId);
            $this->mediaUsage->removeFieldUsage('gallery', $galleryId, 'gallery_media', $mediaId);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    private function syncCoverUsage(int $galleryId, ?int $coverMediaId): void
    {
        $this->mediaUsage->removeFieldUsage('gallery', $galleryId, 'cover_media');

        if ($coverMediaId !== null && $coverMediaId > 0) {
            $this->mediaUsage->registerUsage($coverMediaId, 'gallery', $galleryId, 'cover_media');
        }
    }
}