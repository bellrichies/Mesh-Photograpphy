<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS inquiries (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(120) NOT NULL,
                last_name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(60) NULL,
                company_name VARCHAR(190) NULL,
                service_interest VARCHAR(190) NULL,
                preferred_date DATE NULL,
                budget_range VARCHAR(120) NULL,
                location VARCHAR(190) NULL,
                referral_source VARCHAR(190) NULL,
                message TEXT NOT NULL,
                status ENUM("new", "in_progress", "responded", "archived", "spam") NOT NULL DEFAULT "new",
                source_ip VARCHAR(64) NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_inquiries_status (status),
                INDEX idx_inquiries_email (email),
                INDEX idx_inquiries_service_interest (service_interest),
                INDEX idx_inquiries_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS inquiries');
    }
};