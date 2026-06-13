<?php

declare(strict_types=1);

namespace App\Core\Migrations;

use PDO;

abstract class Migration
{
    public function __construct(protected PDO $db)
    {
    }

    abstract public function up(): void;

    abstract public function down(): void;

    protected function statement(string $sql): void
    {
        $this->db->exec($sql);
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): void
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    protected function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare('SHOW TABLES LIKE :table');
        $stmt->execute(['table' => $table]);
        return (bool) $stmt->fetchColumn();
    }
}
