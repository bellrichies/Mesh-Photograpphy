<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS gallery_media (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                gallery_id BIGINT UNSIGNED NOT NULL,
                media_id BIGINT UNSIGNED NOT NULL,
                caption TEXT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_gallery_media (gallery_id, media_id),
                INDEX idx_gallery_media_sort_order (sort_order),
                INDEX idx_gallery_media_featured (is_featured),
                INDEX idx_gallery_media_media_id (media_id),
                CONSTRAINT fk_gallery_media_gallery FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE,
                CONSTRAINT fk_gallery_media_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS gallery_media');
    }
};