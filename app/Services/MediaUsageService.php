<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaUsageMap;

class MediaUsageService
{
    public function __construct(private readonly MediaUsageMap $usageMap)
    {
    }

    public function registerUsage(int $mediaId, string $entityType, int $entityId, string $fieldName): void
    {
        $this->usageMap->attach($mediaId, $entityType, $entityId, $fieldName);
    }

    public function removeFieldUsage(string $entityType, int $entityId, string $fieldName, ?int $mediaId = null): void
    {
        $this->usageMap->detachField($entityType, $entityId, $fieldName, $mediaId);
    }

    public function removeEntityUsage(string $entityType, int $entityId): void
    {
        $this->usageMap->detachEntity($entityType, $entityId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function usageForMedia(int $mediaId): array
    {
        return $this->usageMap->forMedia($mediaId);
    }
}
