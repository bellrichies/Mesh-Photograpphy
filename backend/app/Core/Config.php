<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $data = [];

    public static function load(string $configPath): void
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$data[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $data  = self::$data;

        foreach ($parts as $part) {
            if (!is_array($data) || !array_key_exists($part, $data)) {
                return $default;
            }
            $data = $data[$part];
        }

        return $data;
    }

    public static function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $data  = &self::$data;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $data[$part] = $value;
            } else {
                if (!isset($data[$part]) || !is_array($data[$part])) {
                    $data[$part] = [];
                }
                $data = &$data[$part];
            }
        }
    }

    public static function all(): array
    {
        return self::$data;
    }
}
