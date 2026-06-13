<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS inquiry_notes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                inquiry_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NULL,
                note TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_inquiry_notes_inquiry_id (inquiry_id),
                INDEX idx_inquiry_notes_user_id (user_id),
                CONSTRAINT fk_inquiry_notes_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
                CONSTRAINT fk_inquiry_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS inquiry_notes');
    }
};