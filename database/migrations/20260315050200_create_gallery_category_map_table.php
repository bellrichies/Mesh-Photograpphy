<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS gallery_category_map (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                gallery_id BIGINT UNSIGNED NOT NULL,
                category_id BIGINT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_gallery_category_map (gallery_id, category_id),
                INDEX idx_gallery_category_map_category (category_id),
                CONSTRAINT fk_gallery_category_map_gallery FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE,
                CONSTRAINT fk_gallery_category_map_category FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS gallery_category_map');
    }
};