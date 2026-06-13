<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS reusable_blocks (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(190) NOT NULL,
                block_key VARCHAR(190) NOT NULL UNIQUE,
                block_type VARCHAR(120) NOT NULL,
                title VARCHAR(255) NULL,
                body LONGTEXT NULL,
                json_payload JSON NULL,
                status ENUM("draft", "published", "hidden") NOT NULL DEFAULT "draft",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_reusable_blocks_type (block_type),
                INDEX idx_reusable_blocks_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS reusable_blocks');
    }
};
