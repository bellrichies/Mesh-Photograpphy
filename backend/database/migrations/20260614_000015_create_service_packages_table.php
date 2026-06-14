<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create service_packages table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_packages (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                service_id  BIGINT UNSIGNED NOT NULL,
                name        VARCHAR(150)    NOT NULL,
                description TEXT            NULL,
                price       DECIMAL(10,2)   NULL,
                features    JSON            NULL,
                sort_order  SMALLINT        NOT NULL DEFAULT 0,
                created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                CONSTRAINT fk_sp_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS service_packages');
    }
};
