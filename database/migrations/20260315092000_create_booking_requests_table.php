<?php

declare(strict_types=1);

use App\Core\Migrations\Migration;

return new class($db) extends Migration {
    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS booking_requests (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                inquiry_id BIGINT UNSIGNED NULL,
                service_id BIGINT UNSIGNED NULL,
                first_name VARCHAR(120) NOT NULL,
                last_name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(60) NULL,
                requested_date DATE NOT NULL,
                requested_time TIME NULL,
                event_type VARCHAR(150) NOT NULL,
                location VARCHAR(190) NOT NULL,
                hours_needed DECIMAL(5,2) NULL,
                guest_count INT UNSIGNED NULL,
                notes TEXT NULL,
                status ENUM("new", "in_progress", "quoted", "confirmed", "archived") NOT NULL DEFAULT "new",
                source_ip VARCHAR(64) NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_booking_requests_inquiry_id (inquiry_id),
                INDEX idx_booking_requests_service_id (service_id),
                INDEX idx_booking_requests_requested_date (requested_date),
                INDEX idx_booking_requests_status (status),
                CONSTRAINT fk_booking_requests_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE SET NULL,
                CONSTRAINT fk_booking_requests_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(): void
    {
        $this->statement('DROP TABLE IF EXISTS booking_requests');
    }
};