<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Album
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM albums WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byGalleryId(int $galleryId): array
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
    public function media(int $albumId): array
    {
        $rows = $this->database->query(
            'SELECT am.*, m.*
             FROM album_media am
             INNER JOIN media m ON m.id = am.media_id
             WHERE am.album_id = :album_id AND m.deleted_at IS NULL
             ORDER BY am.sort_order ASC, am.id ASC',
            ['album_id' => $albumId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}