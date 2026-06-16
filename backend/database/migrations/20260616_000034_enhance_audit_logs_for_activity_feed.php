<?php
declare(strict_types=1);

return new class {
    public string $description = 'Add activity feed details to audit logs';

    public function up(\PDO $pdo): void
    {
        if (!$this->columnExists($pdo, 'audit_logs', 'description')) {
            $pdo->exec("
                ALTER TABLE audit_logs
                ADD COLUMN description VARCHAR(500) NULL AFTER model_id
            ");
        }

        if (!$this->columnExists($pdo, 'audit_logs', 'request_method')) {
            $pdo->exec("
                ALTER TABLE audit_logs
                ADD COLUMN request_method VARCHAR(10) NULL AFTER user_agent
            ");
        }

        if (!$this->columnExists($pdo, 'audit_logs', 'request_path')) {
            $pdo->exec("
                ALTER TABLE audit_logs
                ADD COLUMN request_path VARCHAR(255) NULL AFTER request_method
            ");
        }
    }

    public function down(\PDO $pdo): void
    {
        if ($this->columnExists($pdo, 'audit_logs', 'request_path')) {
            $pdo->exec('ALTER TABLE audit_logs DROP COLUMN request_path');
        }

        if ($this->columnExists($pdo, 'audit_logs', 'request_method')) {
            $pdo->exec('ALTER TABLE audit_logs DROP COLUMN request_method');
        }

        if ($this->columnExists($pdo, 'audit_logs', 'description')) {
            $pdo->exec('ALTER TABLE audit_logs DROP COLUMN description');
        }
    }

    private function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS cnt
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);

        return (int) $stmt->fetchColumn() > 0;
    }
};
