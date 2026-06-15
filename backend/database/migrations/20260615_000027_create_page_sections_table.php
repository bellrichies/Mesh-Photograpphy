<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create page_sections table for structured CMS page content';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS page_sections (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                page_id      BIGINT UNSIGNED NOT NULL,
                section_key  VARCHAR(100)    NOT NULL,
                section_type ENUM('text','rich_text','image','json') NOT NULL DEFAULT 'text',
                label        VARCHAR(255)    NULL,
                content      LONGTEXT        NULL,
                sort_order   INT             NOT NULL DEFAULT 0,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_page_section_key (page_id, section_key),
                KEY idx_page_sections_page  (page_id),
                KEY idx_page_sections_order (page_id, sort_order),
                CONSTRAINT fk_page_sections_page
                    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS page_sections');
    }
};
