<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create booking_requests table';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS booking_requests (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name         VARCHAR(150)    NOT NULL,
                email        VARCHAR(255)    NOT NULL,
                phone        VARCHAR(30)     NULL,
                service_id   BIGINT UNSIGNED NULL,
                event_date   DATE            NULL,
                event_type   VARCHAR(150)    NULL,
                location     VARCHAR(255)    NULL,
                guest_count  SMALLINT UNSIGNED NULL,
                budget       DECIMAL(10,2)   NULL,
                notes        TEXT            NULL,
                status       ENUM('new','contacted','booked','declined','cancelled') NOT NULL DEFAULT 'new',
                ip_address   VARCHAR(45)     NULL,
                admin_notes  TEXT            NULL,
                deleted_at   DATETIME        NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_bookings_deleted_at (deleted_at),
                KEY idx_bookings_status (status),
                CONSTRAINT fk_bookings_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS booking_requests');
    }
};
