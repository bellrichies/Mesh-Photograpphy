<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS albums (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                gallery_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                slug VARCHAR(190) NOT NULL,
                description TEXT NULL,
                cover_media_id BIGINT UNSIGNED NULL,
                sort_order INT NOT NULL DEFAULT 0,
                status ENUM("draft", "published", "hidden") NOT NULL DEFAULT "draft",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_gallery_album_slug (gallery_id, slug),
                INDEX idx_albums_gallery_id (gallery_id),
                INDEX idx_albums_sort_order (sort_order),
                INDEX idx_albums_status (status),
                CONSTRAINT fk_albums_gallery FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE,
                CONSTRAINT fk_albums_cover_media FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS albums');
    }
};