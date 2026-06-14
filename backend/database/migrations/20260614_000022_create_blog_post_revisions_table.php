<?php

declare(strict_types=1);

return new class {
    public string $description = 'Create blog_post_revisions table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_post_revisions (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id    BIGINT UNSIGNED NOT NULL,
                title      VARCHAR(255)    NOT NULL,
                body       LONGTEXT        NULL,
                saved_by   BIGINT UNSIGNED NULL,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_revision_post_id (post_id),
                KEY idx_revision_created_at (created_at),
                CONSTRAINT fk_revision_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_revision_user FOREIGN KEY (saved_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS blog_post_revisions');
    }
};
