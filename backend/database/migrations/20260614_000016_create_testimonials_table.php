<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create testimonials table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS testimonials (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                client_name  VARCHAR(150)    NOT NULL,
                client_title VARCHAR(150)    NULL,
                quote        TEXT            NOT NULL,
                rating       TINYINT         NOT NULL DEFAULT 5,
                avatar_id    BIGINT UNSIGNED NULL,
                service_id   BIGINT UNSIGNED NULL,
                is_featured  TINYINT(1)      NOT NULL DEFAULT 0,
                is_published TINYINT(1)      NOT NULL DEFAULT 0,
                sort_order   SMALLINT        NOT NULL DEFAULT 0,
                deleted_at   DATETIME        NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_testimonials_deleted_at (deleted_at),
                CONSTRAINT fk_testimonials_avatar  FOREIGN KEY (avatar_id)  REFERENCES media(id)    ON DELETE SET NULL,
                CONSTRAINT fk_testimonials_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS testimonials');
    }
};
