<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS media (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                uuid CHAR(36) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                stored_name VARCHAR(255) NOT NULL,
                directory VARCHAR(255) NOT NULL,
                disk VARCHAR(64) NOT NULL DEFAULT "public",
                extension VARCHAR(20) NOT NULL,
                mime_type VARCHAR(120) NOT NULL,
                file_type ENUM("image", "video", "document", "other") NOT NULL DEFAULT "other",
                size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
                width INT UNSIGNED NULL,
                height INT UNSIGNED NULL,
                duration_seconds DECIMAL(10,2) NULL,
                alt_text VARCHAR(255) NULL,
                title VARCHAR(255) NULL,
                caption TEXT NULL,
                description TEXT NULL,
                checksum CHAR(64) NULL,
                is_public TINYINT(1) NOT NULL DEFAULT 1,
                status ENUM("active", "archived") NOT NULL DEFAULT "active",
                uploaded_by BIGINT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                UNIQUE KEY uniq_media_uuid (uuid),
                INDEX idx_media_status (status),
                INDEX idx_media_file_type (file_type),
                INDEX idx_media_created_at (created_at),
                INDEX idx_media_checksum (checksum),
                INDEX idx_media_deleted_at (deleted_at),
                CONSTRAINT fk_media_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS media');
    }
};
