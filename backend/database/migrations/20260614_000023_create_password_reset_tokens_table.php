<?php

declare(strict_types=1);

return new class {
    public string $description = 'Create password_reset_tokens table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                email      VARCHAR(255)    NOT NULL,
                token      CHAR(64)        NOT NULL,
                expires_at DATETIME        NOT NULL,
                used_at    DATETIME        NULL,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_prt_email (email),
                KEY idx_prt_token (token),
                KEY idx_prt_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS password_reset_tokens');
    }
};
