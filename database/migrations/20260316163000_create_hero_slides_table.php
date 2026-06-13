<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS hero_slides (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(190) NOT NULL,
                subtitle VARCHAR(190) NULL,
                description TEXT NULL,
                image_media_id BIGINT UNSIGNED NULL,
                image_alt_text VARCHAR(255) NULL,
                primary_cta_label VARCHAR(120) NULL,
                primary_cta_url VARCHAR(2048) NULL,
                secondary_cta_label VARCHAR(120) NULL,
                secondary_cta_url VARCHAR(2048) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                status ENUM("draft", "published", "archived") NOT NULL DEFAULT "draft",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                INDEX idx_hero_slides_status (status),
                INDEX idx_hero_slides_sort_order (sort_order),
                INDEX idx_hero_slides_image_media_id (image_media_id),
                CONSTRAINT fk_hero_slides_image_media FOREIGN KEY (image_media_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS hero_slides');
    }
};
