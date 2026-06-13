<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS media_usage_map (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id BIGINT UNSIGNED NOT NULL,
                entity_type VARCHAR(120) NOT NULL,
                entity_id BIGINT UNSIGNED NOT NULL,
                field_name VARCHAR(120) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_media_usage (media_id, entity_type, entity_id, field_name),
                INDEX idx_media_usage_entity (entity_type, entity_id),
                CONSTRAINT fk_media_usage_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS media_usage_map');
    }
};
