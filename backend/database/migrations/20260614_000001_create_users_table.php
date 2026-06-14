<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create users table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                first_name    VARCHAR(100)    NOT NULL,
                last_name     VARCHAR(100)    NOT NULL,
                email         VARCHAR(255)    NOT NULL,
                password      VARCHAR(255)    NOT NULL,
                status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
                last_login_at DATETIME        NULL,
                created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at    DATETIME        NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_users_email (email),
                KEY idx_users_deleted_at (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS users');
    }
};
