<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create site_settings table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_settings (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                key_name   VARCHAR(100)    NOT NULL,
                value      TEXT            NULL,
                type       ENUM('string','boolean','integer','json') NOT NULL DEFAULT 'string',
                group_name VARCHAR(100)    NOT NULL DEFAULT 'general',
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_settings_key (key_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS site_settings');
    }
};
