<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS activity_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                action VARCHAR(120) NOT NULL,
                entity_type VARCHAR(120) NULL,
                entity_id BIGINT UNSIGNED NULL,
                description TEXT NULL,
                metadata JSON NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_activity_logs_user_id (user_id),
                INDEX idx_activity_logs_action (action),
                INDEX idx_activity_logs_entity (entity_type, entity_id),
                CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS activity_logs');
    }
};
