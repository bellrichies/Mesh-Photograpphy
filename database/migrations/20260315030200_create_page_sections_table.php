<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS page_sections (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id BIGINT UNSIGNED NOT NULL,
                section_key VARCHAR(190) NOT NULL,
                section_type VARCHAR(120) NOT NULL,
                title VARCHAR(255) NULL,
                subtitle VARCHAR(255) NULL,
                body LONGTEXT NULL,
                cta_label VARCHAR(120) NULL,
                cta_url VARCHAR(255) NULL,
                media_id BIGINT UNSIGNED NULL,
                json_payload JSON NULL,
                sort_order INT NOT NULL DEFAULT 0,
                status ENUM("draft", "published", "hidden") NOT NULL DEFAULT "draft",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_page_section_key (page_id, section_key),
                INDEX idx_page_sections_type (section_type),
                INDEX idx_page_sections_sort_order (sort_order),
                INDEX idx_page_sections_status (status),
                CONSTRAINT fk_page_sections_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS page_sections');
    }
};
