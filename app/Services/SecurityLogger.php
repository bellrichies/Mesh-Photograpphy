<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Models\ActivityLog;
use Throwable;

class SecurityLogger
{
    public function __construct(private readonly ActivityLog $activityLog)
    {
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function log(
        string $action,
        ?Request $request = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        array $metadata = [],
        ?int $userId = null
    ): void {
        try {
            $this->activityLog->create(
                $userId ?? app_auth()->id(),
                $action,
                $entityType,
                $entityId,
                $description,
                $metadata,
                $request?->ip() ?: trim((string) ($_SERVER['REMOTE_ADDR'] ?? '')),
                $request?->userAgent() ?: trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')),
            );
        } catch (Throwable) {
            // Logging should not interrupt primary request flow.
        }
    }
}