<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class GalleryMedia
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
            'SELECT * FROM gallery_media WHERE gallery_id = :gallery_id ORDER BY sort_order ASC, id ASC',
            ['gallery_id' => $galleryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function featuredForGallery(int $galleryId): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM gallery_media WHERE gallery_id = :gallery_id AND is_featured = 1 ORDER BY sort_order ASC, id ASC LIMIT 1',
            ['gallery_id' => $galleryId]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function detailedByGalleryId(int $galleryId, ?int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT gm.gallery_id, gm.media_id, gm.caption AS gallery_caption, gm.sort_order AS gallery_sort_order,
                    gm.is_featured AS gallery_is_featured, gm.created_at AS attached_at,
                    m.id, m.uuid, m.original_name, m.stored_name, m.directory, m.mime_type, m.file_type,
                    m.size_bytes, m.width, m.height, m.alt_text, m.title, m.caption, m.description,
                    mv.directory AS thumb_directory, mv.stored_name AS thumb_stored_name,
                    mv.width AS thumb_width, mv.height AS thumb_height
             FROM gallery_media gm
             INNER JOIN media m ON m.id = gm.media_id
             LEFT JOIN media_variants mv ON mv.media_id = m.id AND mv.variant_key = "thumb"
             WHERE gm.gallery_id = :gallery_id AND m.deleted_at IS NULL
             ORDER BY gm.sort_order ASC, gm.id ASC';

        if ($limit !== null) {
            $sql .= sprintf(' LIMIT %d OFFSET %d', max(1, $limit), max(0, $offset));
        }

        $rows = $this->database->query($sql, ['gallery_id' => $galleryId])->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function countForGallery(int $galleryId): int
    {
        return (int) $this->database->query(
            'SELECT COUNT(*)
             FROM gallery_media gm
             INNER JOIN media m ON m.id = gm.media_id
             WHERE gm.gallery_id = :gallery_id AND m.deleted_at IS NULL',
            ['gallery_id' => $galleryId]
        )->fetchColumn();
    }

    public function exists(int $galleryId, int $mediaId): bool
    {
        return (int) $this->database->query(
            'SELECT COUNT(*) FROM gallery_media WHERE gallery_id = :gallery_id AND media_id = :media_id',
            ['gallery_id' => $galleryId, 'media_id' => $mediaId]
        )->fetchColumn() > 0;
    }

    public function nextSortOrder(int $galleryId): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM gallery_media WHERE gallery_id = :gallery_id',
            ['gallery_id' => $galleryId]
        )->fetchColumn();
    }

    public function attach(int $galleryId, int $mediaId, string $caption, int $sortOrder, bool $isFeatured): void
    {
        $this->database->query(
            'INSERT INTO gallery_media (gallery_id, media_id, caption, sort_order, is_featured, created_at)
             VALUES (:gallery_id, :media_id, :caption, :sort_order, :is_featured, NOW())',
            [
                'gallery_id' => $galleryId,
                'media_id' => $mediaId,
                'caption' => $caption !== '' ? $caption : null,
                'sort_order' => $sortOrder,
                'is_featured' => $isFeatured ? 1 : 0,
            ]
        );
    }

    public function updateAttachment(int $galleryId, int $mediaId, string $caption, bool $isFeatured): void
    {
        $this->database->query(
            'UPDATE gallery_media SET caption = :caption, is_featured = :is_featured WHERE gallery_id = :gallery_id AND media_id = :media_id',
            [
                'gallery_id' => $galleryId,
                'media_id' => $mediaId,
                'caption' => $caption !== '' ? $caption : null,
                'is_featured' => $isFeatured ? 1 : 0,
            ]
        );
    }

    public function clearFeatured(int $galleryId): void
    {
        $this->database->query(
            'UPDATE gallery_media SET is_featured = 0 WHERE gallery_id = :gallery_id',
            ['gallery_id' => $galleryId]
        );
    }

    public function setSortOrder(int $galleryId, int $mediaId, int $sortOrder): void
    {
        $this->database->query(
            'UPDATE gallery_media SET sort_order = :sort_order WHERE gallery_id = :gallery_id AND media_id = :media_id',
            [
                'gallery_id' => $galleryId,
                'media_id' => $mediaId,
                'sort_order' => $sortOrder,
            ]
        );
    }

    public function remove(int $galleryId, int $mediaId): void
    {
        $this->database->query(
            'DELETE FROM gallery_media WHERE gallery_id = :gallery_id AND media_id = :media_id',
            ['gallery_id' => $galleryId, 'media_id' => $mediaId]
        );
    }
}