<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS services (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(190) NOT NULL,
                slug VARCHAR(190) NOT NULL,
                short_description TEXT NULL,
                full_description LONGTEXT NULL,
                cover_media_id BIGINT UNSIGNED NULL,
                sort_order INT NOT NULL DEFAULT 0,
                featured TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM("draft", "published", "archived") NOT NULL DEFAULT "draft",
                price_display VARCHAR(190) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                UNIQUE KEY uniq_services_slug (slug),
                INDEX idx_services_status (status),
                INDEX idx_services_featured (featured),
                INDEX idx_services_sort_order (sort_order),
                INDEX idx_services_cover_media (cover_media_id),
                CONSTRAINT fk_services_cover_media FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS services');
    }
};