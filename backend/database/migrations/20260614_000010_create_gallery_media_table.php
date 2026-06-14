<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create gallery_media pivot table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS gallery_media (
                gallery_id BIGINT UNSIGNED NOT NULL,
                media_id   BIGINT UNSIGNED NOT NULL,
                sort_order SMALLINT        NOT NULL DEFAULT 0,
                caption    VARCHAR(500)    NULL,
                PRIMARY KEY (gallery_id, media_id),
                CONSTRAINT fk_gm_gallery FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE,
                CONSTRAINT fk_gm_media   FOREIGN KEY (media_id)   REFERENCES media(id)     ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS gallery_media');
    }
};
