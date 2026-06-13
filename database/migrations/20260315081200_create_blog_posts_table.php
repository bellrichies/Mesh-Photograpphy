<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS blog_posts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                author_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                slug VARCHAR(190) NOT NULL UNIQUE,
                excerpt TEXT NULL,
                body_long LONGTEXT NULL,
                featured_image_id BIGINT UNSIGNED NULL,
                cover_gallery_id BIGINT UNSIGNED NULL,
                status ENUM("draft", "published", "scheduled", "archived") NOT NULL DEFAULT "draft",
                visibility ENUM("public", "unlisted", "private") NOT NULL DEFAULT "public",
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                allow_comments TINYINT(1) NOT NULL DEFAULT 0,
                published_at DATETIME NULL,
                scheduled_at DATETIME NULL,
                archived_at DATETIME NULL,
                reading_time INT UNSIGNED NULL,
                meta_summary TEXT NULL,
                canonical_url VARCHAR(255) NULL,
                view_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                INDEX idx_blog_posts_author_id (author_id),
                INDEX idx_blog_posts_status (status),
                INDEX idx_blog_posts_visibility (visibility),
                INDEX idx_blog_posts_featured (is_featured),
                INDEX idx_blog_posts_published_at (published_at),
                INDEX idx_blog_posts_scheduled_at (scheduled_at),
                INDEX idx_blog_posts_archived_at (archived_at),
                INDEX idx_blog_posts_deleted_at (deleted_at),
                CONSTRAINT fk_blog_posts_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
                CONSTRAINT fk_blog_posts_featured_image FOREIGN KEY (featured_image_id) REFERENCES media(id) ON DELETE SET NULL,
                CONSTRAINT fk_blog_posts_cover_gallery FOREIGN KEY (cover_gallery_id) REFERENCES galleries(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS blog_posts');
    }
};