<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create blog_tags and blog_post_tags tables';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_tags (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name       VARCHAR(100)    NOT NULL,
                slug       VARCHAR(100)    NOT NULL,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_blog_tag_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_post_tags (
                post_id BIGINT UNSIGNED NOT NULL,
                tag_id  BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (post_id, tag_id),
                CONSTRAINT fk_bpt_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_bpt_tag  FOREIGN KEY (tag_id)  REFERENCES blog_tags(id)  ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS blog_post_tags');
        $pdo->exec('DROP TABLE IF EXISTS blog_tags');
    }
};
