<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS seo_meta (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                entity_type VARCHAR(120) NOT NULL,
                entity_id BIGINT UNSIGNED NOT NULL,
                meta_title VARCHAR(255) NULL,
                meta_description TEXT NULL,
                og_title VARCHAR(255) NULL,
                og_description TEXT NULL,
                og_image VARCHAR(255) NULL,
                canonical_url VARCHAR(255) NULL,
                robots_index TINYINT(1) NOT NULL DEFAULT 1,
                robots_follow TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_entity (entity_type, entity_id),
                INDEX idx_seo_entity_type (entity_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS seo_meta');
    }
};
