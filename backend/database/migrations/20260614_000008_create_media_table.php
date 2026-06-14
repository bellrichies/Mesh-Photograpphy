<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create media table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS media (
                id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                uuid           CHAR(36)        NOT NULL,
                original_name  VARCHAR(255)    NOT NULL,
                file_name      VARCHAR(255)    NOT NULL,
                mime_type      VARCHAR(100)    NOT NULL,
                file_size      BIGINT UNSIGNED NOT NULL,
                disk           VARCHAR(50)     NOT NULL DEFAULT 'local',
                path           VARCHAR(500)    NOT NULL,
                width          SMALLINT UNSIGNED NULL,
                height         SMALLINT UNSIGNED NULL,
                alt_text       VARCHAR(255)    NULL,
                uploaded_by    BIGINT UNSIGNED NULL,
                deleted_at     DATETIME        NULL,
                created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_media_uuid (uuid),
                KEY idx_media_deleted_at (deleted_at),
                CONSTRAINT fk_media_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS media');
    }
};
