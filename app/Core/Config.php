<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Config
{
    /** @var array<string, array<string, mixed>> */
    private static array $items = [];

    public static function load(string $configPath): void
    {
        $files = glob(rtrim($configPath, '/\\') . DIRECTORY_SEPARATOR . '*.php') ?: [];

        $loaded = [];
        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $value = require $file;

            if (! is_array($value)) {
                throw new RuntimeException('Config file must return an array: ' . $file);
            }

            $loaded[$key] = $value;
        }

        self::$items = $loaded;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $default;
        }

        $segments = explode('.', $key);
        $cursor = self::$items;

        foreach ($segments as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return $default;
            }

            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $cursor = &self::$items;

        foreach ($segments as $segment) {
            if (! isset($cursor[$segment]) || ! is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            $cursor = &$cursor[$segment];
        }

        $cursor = $value;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$items;
    }
}
