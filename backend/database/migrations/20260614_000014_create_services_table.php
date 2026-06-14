<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create services table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS services (
                id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                title           VARCHAR(255)    NOT NULL,
                slug            VARCHAR(255)    NOT NULL,
                short_desc      VARCHAR(500)    NULL,
                description     TEXT            NULL,
                cover_image_id  BIGINT UNSIGNED NULL,
                price_from      DECIMAL(10,2)   NULL,
                price_label     VARCHAR(100)    NULL,
                duration_label  VARCHAR(100)    NULL,
                is_featured     TINYINT(1)      NOT NULL DEFAULT 0,
                is_published    TINYINT(1)      NOT NULL DEFAULT 0,
                sort_order      SMALLINT        NOT NULL DEFAULT 0,
                seo_title       VARCHAR(255)    NULL,
                seo_description VARCHAR(500)    NULL,
                deleted_at      DATETIME        NULL,
                created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_services_slug (slug),
                KEY idx_services_deleted_at (deleted_at),
                CONSTRAINT fk_services_cover FOREIGN KEY (cover_image_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS services');
    }
};
