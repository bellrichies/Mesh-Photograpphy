<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS blog_post_revisions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT UNSIGNED NOT NULL,
                edited_by BIGINT UNSIGNED NULL,
                title VARCHAR(190) NOT NULL,
                excerpt TEXT NULL,
                body_long LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_blog_post_revisions_post_id (post_id),
                INDEX idx_blog_post_revisions_edited_by (edited_by),
                CONSTRAINT fk_blog_post_revisions_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_blog_post_revisions_edited_by FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS blog_post_revisions');
    }
};