<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create blog_categories table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_categories (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name        VARCHAR(150)    NOT NULL,
                slug        VARCHAR(150)    NOT NULL,
                description TEXT            NULL,
                sort_order  SMALLINT        NOT NULL DEFAULT 0,
                created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_blog_cat_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS blog_categories');
    }
};
