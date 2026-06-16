<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create newsletter subscriptions table and add WhatsApp social setting';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
                id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                email            VARCHAR(255)    NOT NULL,
                email_normalized VARCHAR(255)    NOT NULL,
                status           ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
                source           VARCHAR(50)     NOT NULL DEFAULT 'footer',
                ip_address       VARCHAR(45)     NULL,
                user_agent       VARCHAR(512)    NULL,
                subscribed_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                unsubscribed_at  DATETIME        NULL,
                created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_newsletter_email (email),
                UNIQUE KEY uq_newsletter_email_normalized (email_normalized),
                KEY idx_newsletter_status_subscribed_at (status, subscribed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO site_settings (key_name, value, type, group_name)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute(['social_whatsapp', '', 'string', 'social']);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS newsletter_subscriptions');

        $pdo->prepare("
            DELETE FROM site_settings
            WHERE key_name = ?
              AND (value IS NULL OR value = '')
        ")->execute(['social_whatsapp']);
    }
};
