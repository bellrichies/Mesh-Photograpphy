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
                'phone'         => $map['contact_phone']     ?? null,
                'email'         => $map['contact_email']     ?? null,
                'address'       => $map['contact_address']   ?? null,
                'map_embed_url' => $map['contact_map_embed_url'] ?? null,
            ],
            'social' => [
                'instagram' => $map['social_instagram'] ?? null,
                'facebook'  => $map['social_facebook']  ?? null,
                'twitter'   => $map['social_twitter']   ?? null,
                'pinterest' => $map['social_pinterest'] ?? null,
                'youtube'   => $map['social_youtube']   ?? null,
            ],
            'seo' => [
                // Support both the legacy CoreCmsSeeder key format and the new format
                'default_title'       => $map['seo_default_title']       ?? $map['default_seo_title']       ?? null,
                'default_description' => $map['seo_default_description'] ?? $map['default_seo_description'] ?? null,
            ],
            'theme' => [
                'primary_color'   => $this->sanitizeColor($map['theme_primary_color']   ?? '#C4923B'),
                'secondary_color' => $this->sanitizeColor($map['theme_secondary_color'] ?? '#FAF9F7'),
                'accent_color'    => $this->sanitizeColor($map['theme_accent_color']    ?? '#B8860B'),
                'text_color'      => $this->sanitizeColor($map['theme_text_color']      ?? '#1A1A1A'),
                'bg_color'        => $this->sanitizeColor($map['theme_bg_color']        ?? '#FAF9F7'),
                'display_font'    => $this->sanitizeFont($map['theme_display_font']     ?? 'Cormorant Garamond'),
                'body_font'       => $this->sanitizeFont($map['theme_body_font']        ?? 'Inter'),
            ],
        ];
    }

    private function sanitizeColor(mixed $value): string
    {
        $val = trim((string) $value);
        // Only allow valid hex colours (#rgb, #rrggbb, #rrggbbaa)
        if (preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{2})?)?$/', $val)) {
            return $val;
        }
        return '#C4923B';
    }

    private function sanitizeFont(mixed $value): string
    {
        $allowed = [
            'Inter', 'Lato', 'Open Sans', 'Montserrat', 'Raleway',
            'Cormorant Garamond', 'Playfair Display', 'Libre Baskerville',
            'DM Serif Display', 'EB Garamond',
        ];
        $val = trim((string) $value);
        return in_array($val, $allowed, true) ? $val : 'Inter';
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
