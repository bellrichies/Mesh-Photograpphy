<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Setting
{
    public function __construct(private readonly Database $db) {}

    public function all(): array
    {
        return $this->db->query(
            'SELECT key_name, value, type, group_name FROM site_settings ORDER BY group_name, key_name'
        )->fetchAll();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->db->query(
            'SELECT value, type FROM site_settings WHERE key_name = ? LIMIT 1',
            [$key]
        )->fetch();

        if (!$row) return $default;

        return $this->cast($row['value'], $row['type']);
    }

    public function set(string $key, mixed $value): void
    {
        $serialized = is_array($value) ? json_encode($value) : (string) $value;

        $this->db->query(
            'INSERT INTO site_settings (key_name, value, updated_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()',
            [$key, $serialized, $serialized]
        );
    }

    private function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json'    => json_decode((string) $value, true),
            default   => $value,
        };
    }
}
