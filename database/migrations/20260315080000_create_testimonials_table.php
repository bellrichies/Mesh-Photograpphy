<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS testimonials (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                client_name VARCHAR(190) NOT NULL,
                client_label VARCHAR(190) NULL,
                quote TEXT NOT NULL,
                long_form_story LONGTEXT NULL,
                rating TINYINT UNSIGNED NULL,
                featured TINYINT(1) NOT NULL DEFAULT 0,
                service_id BIGINT UNSIGNED NULL,
                gallery_id BIGINT UNSIGNED NULL,
                portrait_media_id BIGINT UNSIGNED NULL,
                event_date DATE NULL,
                location VARCHAR(190) NULL,
                status ENUM("draft", "published", "archived") NOT NULL DEFAULT "draft",
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                INDEX idx_testimonials_status (status),
                INDEX idx_testimonials_featured (featured),
                INDEX idx_testimonials_sort_order (sort_order),
                INDEX idx_testimonials_service_id (service_id),
                INDEX idx_testimonials_gallery_id (gallery_id),
                INDEX idx_testimonials_portrait_media_id (portrait_media_id),
                CONSTRAINT fk_testimonials_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
                CONSTRAINT fk_testimonials_gallery FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE SET NULL,
                CONSTRAINT fk_testimonials_portrait_media FOREIGN KEY (portrait_media_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS testimonials');
    }
};