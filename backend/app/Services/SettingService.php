<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function __construct(private readonly Setting $model) {}

    public function getPublic(): array
    {
        $all = $this->model->all();
        $map = [];
        foreach ($all as $row) {
            $map[$row['key_name']] = $this->castValue($row['value'], $row['type']);
        }

        return [
            'site' => [
                'name'       => $map['site_name']    ?? 'Mesh Photography',
                'tagline'    => $map['site_tagline']  ?? null,
                'logo_url'   => $map['logo_url']      ?? null,
                'favicon_url'=> $map['favicon_url']   ?? null,
            ],
            'contact' => [
                'phone'   => $map['contact_phone']   ?? null,
                'email'   => $map['contact_email']   ?? null,
                'address' => $map['contact_address'] ?? null,
            ],
            'social' => [
                'instagram' => $map['social_instagram'] ?? null,
                'facebook'  => $map['social_facebook']  ?? null,
                'twitter'   => $map['social_twitter']   ?? null,
                'pinterest' => $map['social_pinterest'] ?? null,
                'youtube'   => $map['social_youtube']   ?? null,
            ],
            'seo' => [
                'default_title'       => $map['seo_default_title']       ?? null,
                'default_description' => $map['seo_default_description'] ?? null,
            ],
        ];
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json'    => json_decode((string) $value, true),
            default   => $value,
        };
    }
}
