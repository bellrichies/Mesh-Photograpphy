<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ActivityLog
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function create(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->database->query(
            'INSERT INTO activity_logs (
                user_id, action, entity_type, entity_id, description, metadata, ip_address, user_agent, created_at
            ) VALUES (
                :user_id, :action, :entity_type, :entity_id, :description, :metadata, :ip_address, :user_agent, NOW()
            )',
            [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'metadata' => $metadata !== [] ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latest(int $limit = 20): array
    {
        $rows = $this->database->query(
            'SELECT * FROM activity_logs ORDER BY id DESC LIMIT ' . max(1, $limit)
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}
