<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS galleries (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(190) NOT NULL,
                slug VARCHAR(190) NOT NULL UNIQUE,
                excerpt TEXT NULL,
                story_intro LONGTEXT NULL,
                category_primary_id BIGINT UNSIGNED NULL,
                cover_media_id BIGINT UNSIGNED NULL,
                featured TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM("draft", "published", "archived") NOT NULL DEFAULT "draft",
                location VARCHAR(190) NULL,
                event_date DATE NULL,
                client_name VARCHAR(190) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                published_at DATETIME NULL,
                created_by BIGINT UNSIGNED NULL,
                updated_by BIGINT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                INDEX idx_galleries_status (status),
                INDEX idx_galleries_featured (featured),
                INDEX idx_galleries_sort_order (sort_order),
                INDEX idx_galleries_published_at (published_at),
                INDEX idx_galleries_deleted_at (deleted_at),
                INDEX idx_galleries_category_primary (category_primary_id),
                CONSTRAINT fk_galleries_category_primary FOREIGN KEY (category_primary_id) REFERENCES gallery_categories(id) ON DELETE SET NULL,
                CONSTRAINT fk_galleries_cover_media FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,
                CONSTRAINT fk_galleries_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_galleries_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS galleries');
    }
};