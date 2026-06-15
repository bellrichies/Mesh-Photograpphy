<?php
declare(strict_types=1);

return new class {
    public string $description = 'Enhance CMS pages and page sections with SEO, media, and settings fields';

    public function up(\PDO $pdo): void
    {
        $this->addColumn($pdo, 'pages', 'template', "VARCHAR(100) NULL AFTER body");
        $this->addColumn($pdo, 'pages', 'canonical_url', "VARCHAR(500) NULL AFTER seo_description");
        $this->addColumn($pdo, 'pages', 'og_title', "VARCHAR(255) NULL AFTER canonical_url");
        $this->addColumn($pdo, 'pages', 'og_description', "VARCHAR(500) NULL AFTER og_title");
        $this->addColumn($pdo, 'pages', 'seo_robots', "VARCHAR(100) NULL DEFAULT 'index, follow' AFTER og_image_id");
        $this->addColumn($pdo, 'pages', 'schema_markup', "LONGTEXT NULL AFTER seo_robots");

        $this->addColumn($pdo, 'page_sections', 'media_id', "BIGINT UNSIGNED NULL AFTER content");
        $this->addColumn($pdo, 'page_sections', 'settings_json', "LONGTEXT NULL AFTER media_id");

        if (!$this->indexExists($pdo, 'page_sections', 'idx_page_sections_media')) {
            $pdo->exec('ALTER TABLE page_sections ADD KEY idx_page_sections_media (media_id)');
        }

        if (!$this->constraintExists($pdo, 'page_sections', 'fk_page_sections_media')) {
            $pdo->exec('
                ALTER TABLE page_sections
                ADD CONSTRAINT fk_page_sections_media
                FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
            ');
        }
    }

    public function down(\PDO $pdo): void
    {
        if ($this->constraintExists($pdo, 'page_sections', 'fk_page_sections_media')) {
            $pdo->exec('ALTER TABLE page_sections DROP FOREIGN KEY fk_page_sections_media');
        }

        if ($this->indexExists($pdo, 'page_sections', 'idx_page_sections_media')) {
            $pdo->exec('ALTER TABLE page_sections DROP INDEX idx_page_sections_media');
        }

        foreach (['settings_json', 'media_id'] as $column) {
            $this->dropColumn($pdo, 'page_sections', $column);
        }

        foreach (['schema_markup', 'seo_robots', 'og_description', 'og_title', 'canonical_url', 'template'] as $column) {
            $this->dropColumn($pdo, 'pages', $column);
        }
    }

    private function addColumn(\PDO $pdo, string $table, string $column, string $definition): void
    {
        if (!$this->columnExists($pdo, $table, $column)) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function dropColumn(\PDO $pdo, string $table, string $column): void
    {
        if ($this->columnExists($pdo, $table, $column)) {
            $pdo->exec("ALTER TABLE {$table} DROP COLUMN {$column}");
        }
    }

    private function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare('
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ');
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function indexExists(\PDO $pdo, string $table, string $index): bool
    {
        $stmt = $pdo->prepare('
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ');
        $stmt->execute([$table, $index]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function constraintExists(\PDO $pdo, string $table, string $constraint): bool
    {
        $stmt = $pdo->prepare('
            SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
        ');
        $stmt->execute([$table, $constraint]);
        return (int) $stmt->fetchColumn() > 0;
    }
};
