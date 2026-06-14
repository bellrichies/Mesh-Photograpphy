<?php

declare(strict_types=1);

return new class {
    public string $description = 'Create inquiry_notes table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inquiry_notes (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                inquiry_id  BIGINT UNSIGNED NOT NULL,
                note        TEXT            NOT NULL,
                created_by  BIGINT UNSIGNED NULL,
                created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_inquiry_notes_inquiry_id (inquiry_id),
                CONSTRAINT fk_inquiry_notes_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
                CONSTRAINT fk_inquiry_notes_user    FOREIGN KEY (created_by)  REFERENCES users(id)    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS inquiry_notes');
    }
};
