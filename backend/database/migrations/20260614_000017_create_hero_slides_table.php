<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create hero_slides table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS hero_slides (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                heading      VARCHAR(255)    NOT NULL,
                subheading   VARCHAR(500)    NULL,
                cta_label    VARCHAR(100)    NULL,
                cta_url      VARCHAR(500)    NULL,
                image_id     BIGINT UNSIGNED NULL,
                is_published TINYINT(1)      NOT NULL DEFAULT 0,
                sort_order   SMALLINT        NOT NULL DEFAULT 0,
                deleted_at   DATETIME        NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_hero_slides_deleted_at (deleted_at),
                CONSTRAINT fk_hero_slides_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS hero_slides');
    }
};
