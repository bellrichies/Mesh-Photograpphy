<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Gallery
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM galleries WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM galleries WHERE slug = :slug AND deleted_at IS NULL LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM galleries WHERE deleted_at IS NULL'
        )->fetchColumn();
    }

    public function primaryCategory(int $galleryId): ?array
    {
        $row = $this->database->query(
            'SELECT gc.*
             FROM gallery_categories gc
             INNER JOIN galleries g ON g.category_primary_id = gc.id
             WHERE g.id = :gallery_id AND g.deleted_at IS NULL
             LIMIT 1',
            ['gallery_id' => $galleryId]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categories(int $galleryId): array
    {
        $rows = $this->database->query(
            'SELECT gc.*
             FROM gallery_categories gc
             INNER JOIN gallery_category_map gcm ON gcm.category_id = gc.id
             WHERE gcm.gallery_id = :gallery_id
             ORDER BY gc.sort_order ASC, gc.name ASC',
            ['gallery_id' => $galleryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function media(int $galleryId): array
    {
        $rows = $this->database->query(
            'SELECT gm.*, m.*
             FROM gallery_media gm
             INNER JOIN media m ON m.id = gm.media_id
             WHERE gm.gallery_id = :gallery_id AND m.deleted_at IS NULL
             ORDER BY gm.sort_order ASC, gm.id ASC',
            ['gallery_id' => $galleryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function albums(int $galleryId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM albums WHERE gallery_id = :gallery_id ORDER BY sort_order ASC, id ASC',
            ['gallery_id' => $galleryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchableOptions(): array
    {
        $rows = $this->database->query(
            'SELECT id, title, slug FROM galleries WHERE deleted_at IS NULL ORDER BY title ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO galleries (
                title, slug, excerpt, story_intro, category_primary_id, cover_media_id, featured, status,
                location, event_date, client_name, sort_order, published_at, created_by, updated_by,
                created_at, updated_at
            ) VALUES (
                :title, :slug, :excerpt, :story_intro, :category_primary_id, :cover_media_id, :featured, :status,
                :location, :event_date, :client_name, :sort_order, :published_at, :created_by, :updated_by,
                NOW(), NOW()
            )',
            [
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'excerpt' => $data['excerpt'] ?? null,
                'story_intro' => $data['story_intro'] ?? null,
                'category_primary_id' => $data['category_primary_id'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'featured' => $data['featured'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'location' => $data['location'] ?? null,
                'event_date' => $data['event_date'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $this->database->query(
            'UPDATE galleries SET
                title = :title,
                slug = :slug,
                excerpt = :excerpt,
                story_intro = :story_intro,
                category_primary_id = :category_primary_id,
                cover_media_id = :cover_media_id,
                featured = :featured,
                status = :status,
                location = :location,
                event_date = :event_date,
                client_name = :client_name,
                sort_order = :sort_order,
                published_at = :published_at,
                updated_by = :updated_by,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'excerpt' => $data['excerpt'] ?? null,
                'story_intro' => $data['story_intro'] ?? null,
                'category_primary_id' => $data['category_primary_id'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'featured' => $data['featured'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'location' => $data['location'] ?? null,
                'event_date' => $data['event_date'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'published_at' => $data['published_at'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE galleries SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }
}