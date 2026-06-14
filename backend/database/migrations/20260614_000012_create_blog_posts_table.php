<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create blog_posts table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_posts (
                id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                title             VARCHAR(255)    NOT NULL,
                slug              VARCHAR(255)    NOT NULL,
                excerpt           TEXT            NULL,
                body              LONGTEXT        NULL,
                cover_image_id    BIGINT UNSIGNED NULL,
                author_id         BIGINT UNSIGNED NULL,
                category_id       BIGINT UNSIGNED NULL,
                is_featured       TINYINT(1)      NOT NULL DEFAULT 0,
                is_published      TINYINT(1)      NOT NULL DEFAULT 0,
                published_at      DATETIME        NULL,
                reading_time_mins TINYINT UNSIGNED NULL,
                seo_title         VARCHAR(255)    NULL,
                seo_description   VARCHAR(500)    NULL,
                deleted_at        DATETIME        NULL,
                created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_blog_slug (slug),
                KEY idx_blog_deleted_at (deleted_at),
                KEY idx_blog_published (is_published, deleted_at),
                CONSTRAINT fk_blog_cover    FOREIGN KEY (cover_image_id) REFERENCES media(id)           ON DELETE SET NULL,
                CONSTRAINT fk_blog_author   FOREIGN KEY (author_id)      REFERENCES users(id)            ON DELETE SET NULL,
                CONSTRAINT fk_blog_category FOREIGN KEY (category_id)    REFERENCES blog_categories(id)  ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS blog_posts');
    }
};
