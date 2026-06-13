<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS media_variants (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id BIGINT UNSIGNED NOT NULL,
                variant_key VARCHAR(120) NOT NULL,
                stored_name VARCHAR(255) NOT NULL,
                directory VARCHAR(255) NOT NULL,
                disk VARCHAR(64) NOT NULL DEFAULT "public",
                mime_type VARCHAR(120) NOT NULL,
                extension VARCHAR(20) NOT NULL,
                size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
                width INT UNSIGNED NULL,
                height INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_media_variant (media_id, variant_key),
                INDEX idx_media_variants_variant_key (variant_key),
                CONSTRAINT fk_media_variants_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS media_variants');
    }
};
