<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create audit_logs table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id      BIGINT UNSIGNED NULL,
                action       VARCHAR(100)    NOT NULL,
                model_type   VARCHAR(100)    NULL,
                model_id     BIGINT UNSIGNED NULL,
                description  VARCHAR(500)    NULL,
                old_values   JSON            NULL,
                new_values   JSON            NULL,
                ip_address   VARCHAR(45)     NULL,
                user_agent   VARCHAR(500)    NULL,
                request_method VARCHAR(10)   NULL,
                request_path VARCHAR(255)    NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_audit_user_id (user_id),
                KEY idx_audit_model (model_type, model_id),
                KEY idx_audit_created_at (created_at),
                CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS audit_logs');
    }
};
