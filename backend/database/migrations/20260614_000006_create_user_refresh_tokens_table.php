<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create user_refresh_tokens table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_refresh_tokens (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id    BIGINT UNSIGNED NOT NULL,
                token_hash CHAR(64)        NOT NULL,
                expires_at DATETIME        NOT NULL,
                revoked_at DATETIME        NULL,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_urt_token_hash (token_hash),
                KEY idx_urt_user_id (user_id),
                CONSTRAINT fk_urt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS user_refresh_tokens');
    }
};
