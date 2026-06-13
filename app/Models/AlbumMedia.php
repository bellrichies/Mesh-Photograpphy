<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class AlbumMedia
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byAlbumId(int $albumId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM album_media WHERE album_id = :album_id ORDER BY sort_order ASC, id ASC',
            ['album_id' => $albumId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function featuredForAlbum(int $albumId): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM album_media WHERE album_id = :album_id AND is_featured = 1 ORDER BY sort_order ASC, id ASC LIMIT 1',
            ['album_id' => $albumId]
        )->fetch();

        return is_array($row) ? $row : null;
    }
}