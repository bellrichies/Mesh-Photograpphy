<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create cms pages table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                title           VARCHAR(255)    NOT NULL,
                slug            VARCHAR(255)    NOT NULL,
                body            LONGTEXT        NULL,
                is_published    TINYINT(1)      NOT NULL DEFAULT 0,
                seo_title       VARCHAR(255)    NULL,
                seo_description VARCHAR(500)    NULL,
                og_image_id     BIGINT UNSIGNED NULL,
                deleted_at      DATETIME        NULL,
                created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_pages_slug (slug),
                KEY idx_pages_deleted_at (deleted_at),
                CONSTRAINT fk_pages_og_image FOREIGN KEY (og_image_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS pages');
    }
};
