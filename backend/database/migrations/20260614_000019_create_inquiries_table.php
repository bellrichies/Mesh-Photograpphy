<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create inquiries table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inquiries (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name         VARCHAR(150)    NOT NULL,
                email        VARCHAR(255)    NOT NULL,
                phone        VARCHAR(30)     NULL,
                subject      VARCHAR(255)    NULL,
                message      TEXT            NOT NULL,
                service_id   BIGINT UNSIGNED NULL,
                ip_address   VARCHAR(45)     NULL,
                is_read      TINYINT(1)      NOT NULL DEFAULT 0,
                read_at      DATETIME        NULL,
                replied_at   DATETIME        NULL,
                deleted_at   DATETIME        NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_inquiries_deleted_at (deleted_at),
                CONSTRAINT fk_inquiries_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS inquiries');
    }
};
