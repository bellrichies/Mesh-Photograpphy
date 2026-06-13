<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Setting
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allGrouped(): array
    {
        $rows = $this->database->query(
            'SELECT `group`, `key`, value_text, value_json FROM settings ORDER BY `group` ASC, `key` ASC'
        )->fetchAll();

        if (! is_array($rows)) {
            return [];
        }

        $grouped = [];

        foreach ($rows as $row) {
            $group = (string) ($row['group'] ?? '');
            $key = (string) ($row['key'] ?? '');

            if ($group === '' || $key === '') {
                continue;
            }

            $grouped[$group][$key] = $this->normalizeValue($row['value_text'] ?? null, $row['value_json'] ?? null);
        }

        return $grouped;
    }

    /**
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        $rows = $this->database->query(
            'SELECT `key`, value_text, value_json FROM settings WHERE `group` = :group_name ORDER BY `key` ASC',
            [
                'group_name' => $group,
            ]
        )->fetchAll();

        if (! is_array($rows)) {
            return [];
        }

        $output = [];
        foreach ($rows as $row) {
            $key = (string) ($row['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $output[$key] = $this->normalizeValue($row['value_text'] ?? null, $row['value_json'] ?? null);
        }

        return $output;
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $row = $this->database->query(
            'SELECT value_text, value_json FROM settings WHERE `group` = :group_name AND `key` = :key_name LIMIT 1',
            [
                'group_name' => $group,
                'key_name' => $key,
            ]
        )->fetch();

        if (! is_array($row)) {
            return $default;
        }

        $value = $this->normalizeValue($row['value_text'] ?? null, $row['value_json'] ?? null);

        return $value ?? $default;
    }

    public function set(string $group, string $key, ?string $valueText = null, mixed $valueJson = null, bool $isPublic = false): void
    {
        $this->database->query(
            'INSERT INTO settings (`group`, `key`, value_text, value_json, is_public, created_at, updated_at)
             VALUES (:group_name, :key_name, :value_text, :value_json, :is_public, NOW(), NOW())
             ON DUPLICATE KEY UPDATE value_text = VALUES(value_text), value_json = VALUES(value_json), is_public = VALUES(is_public), updated_at = NOW()',
            [
                'group_name' => $group,
                'key_name' => $key,
                'value_text' => $valueText,
                'value_json' => $valueJson !== null ? json_encode($valueJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'is_public' => $isPublic ? 1 : 0,
            ]
        );
    }

    private function normalizeValue(mixed $valueText, mixed $valueJson): mixed
    {
        if ($valueJson !== null && $valueJson !== '') {
            $decoded = json_decode((string) $valueJson, true);
            return $decoded ?? null;
        }

        return $valueText;
    }
}
