<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;

class SettingsSeeder
{
    public function __construct(private ?DemoContentFactory $factory = null)
    {
        $this->factory = $this->factory ?? new DemoContentFactory();
    }

    public function run(Database $database): void
    {
        $settings = $this->factory->settings();
        $settings[] = [
            'group' => 'branding',
            'key' => 'logo_media_id',
            'value_text' => $this->mediaIdByUuid($database, 'e1a9c91d-9503-4c2d-a15a-0da1685b91fb'),
            'is_public' => 1,
        ];
        $settings[] = [
            'group' => 'seo',
            'key' => 'default_og_image_media_id',
            'value_text' => $this->mediaIdByUuid($database, 'e1a9c91d-9503-4c2d-a15a-0da1685b91fb'),
            'is_public' => 1,
        ];

        $allowedKeysByGroup = [];

        foreach ($settings as $setting) {
            $group = (string) ($setting['group'] ?? '');
            $key = (string) ($setting['key'] ?? '');

            if ($group === '' || $key === '') {
                continue;
            }

            $allowedKeysByGroup[$group][] = $key;

            $database->query(
                'INSERT INTO settings (`group`, `key`, value_text, value_json, is_public, created_at, updated_at)
                 VALUES (:group_name, :key_name, :value_text, :value_json, :is_public, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    value_text = VALUES(value_text),
                    value_json = VALUES(value_json),
                    is_public = VALUES(is_public),
                    updated_at = NOW()',
                [
                    'group_name' => $group,
                    'key_name' => $key,
                    'value_text' => $setting['value_text'] ?? null,
                    'value_json' => array_key_exists('value_json', $setting) ? $this->jsonEncode($setting['value_json']) : null,
                    'is_public' => $setting['is_public'] ?? 0,
                ]
            );
        }

        foreach ($allowedKeysByGroup as $group => $keys) {
            $keys = array_values(array_unique(array_filter(array_map('strval', $keys), static fn (string $item): bool => $item !== '')));
            if ($keys === []) {
                continue;
            }

            $placeholders = implode(', ', array_fill(0, count($keys), '?'));
            $params = array_merge([$group], $keys);
            $database->query(
                'DELETE FROM settings WHERE `group` = ? AND `key` NOT IN (' . $placeholders . ')',
                $params
            );
        }

        $this->clearSettingsCache();
    }

    private function mediaIdByUuid(Database $database, string $uuid): ?string
    {
        $id = (int) $database->query('SELECT id FROM media WHERE uuid = :uuid LIMIT 1', ['uuid' => $uuid])->fetchColumn();

        return $id > 0 ? (string) $id : null;
    }

    private function jsonEncode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function clearSettingsCache(): void
    {
        $cacheFile = dirname(__DIR__, 2) . '/storage/cache/settings.php';
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
    }
}
