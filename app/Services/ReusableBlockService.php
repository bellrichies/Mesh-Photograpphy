<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ReusableBlock;

class ReusableBlockService
{
    /** @var array<int, string> */
    public const DEFAULT_TYPES = [
        'cta',
        'footer',
        'trust',
        'announcement',
        'content',
        'snippet',
    ];

    public function __construct(private readonly ReusableBlock $blocks)
    {
    }

    public function uniqueBlockKey(string $source, ?int $ignoreId = null): string
    {
        $baseKey = slugify($source);
        $key = $baseKey;
        $suffix = 2;

        while ($this->blocks->keyExists($key, $ignoreId)) {
            $key = $baseKey . '-' . $suffix;
            $suffix++;
        }

        return $key;
    }

    public function renderPartialForType(string $type): string
    {
        return match (trim($type)) {
            'cta' => 'web/blocks/cta',
            'footer' => 'web/blocks/footer',
            'trust' => 'web/blocks/trust',
            'announcement' => 'web/blocks/announcement',
            'content' => 'web/blocks/generic',
            default => 'web/blocks/generic',
        };
    }

    /**
     * @return array<int, string>
     */
    public function availableTypes(): array
    {
        $types = $this->blocks->distinctTypes();
        if ($types === []) {
            return self::DEFAULT_TYPES;
        }

        return array_values(array_unique(array_merge(self::DEFAULT_TYPES, $types)));
    }
}
