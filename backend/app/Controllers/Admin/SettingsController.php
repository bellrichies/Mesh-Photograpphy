<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class SettingsController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT key_name, value FROM site_settings ORDER BY key_name'
        )->fetchAll();

        // Build a flat key→value map from the database
        $raw = [];
        foreach ($rows as $row) {
            $raw[$row['key_name']] = $row['value'];
        }

        // Return in the normalised structure the admin frontend expects.
        // This decouples the internal DB key naming from the admin API shape.
        return $this->success([
            'site' => [
                'name'        => $raw['site_name']    ?? '',
                'tagline'     => $raw['site_tagline']  ?? '',
                'logo_url'    => $raw['logo_url']      ?? '',
                'favicon_url' => $raw['favicon_url']   ?? '',
            ],
            'contact' => [
                'phone'         => $raw['contact_phone']         ?? '',
                'email'         => $raw['contact_email']         ?? '',
                'address'       => $raw['contact_address']       ?? '',
                'map_embed_url' => $raw['contact_map_embed_url'] ?? '',
            ],
            'social' => [
                'instagram' => $raw['social_instagram'] ?? '',
                'facebook'  => $raw['social_facebook']  ?? '',
                'twitter'   => $raw['social_x'] ?? $raw['social_twitter'] ?? '',
                'x'         => $raw['social_x'] ?? $raw['social_twitter'] ?? '',
                'youtube'   => $raw['social_youtube']   ?? '',
                'pinterest' => $raw['social_pinterest'] ?? '',
                'linkedin'  => $raw['social_linkedin']  ?? '',
                'tiktok'    => $raw['social_tiktok']    ?? '',
            ],
            'seo' => [
                'default_title'       => $raw['seo_default_title']       ?? $raw['default_seo_title']       ?? '',
                'default_description' => $raw['seo_default_description'] ?? $raw['default_seo_description'] ?? '',
            ],
            'theme' => [
                'primary_color'   => $raw['theme_primary_color']   ?? '#C4923B',
                'secondary_color' => $raw['theme_secondary_color'] ?? '#FAF9F7',
                'accent_color'    => $raw['theme_accent_color']    ?? '#B8860B',
                'text_color'      => $raw['theme_text_color']      ?? '#1A1A1A',
                'bg_color'        => $raw['theme_bg_color']        ?? '#FAF9F7',
                'display_font'    => $raw['theme_display_font']    ?? 'Cormorant Garamond',
                'body_font'       => $raw['theme_body_font']       ?? 'Inter',
            ],
        ]);
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $data = $request->json();

        $allowedColorKeys = ['primary_color', 'secondary_color', 'accent_color', 'text_color', 'bg_color'];
        $allowedFontKeys  = ['display_font', 'body_font'];
        $allowedFonts     = [
            'Inter', 'Lato', 'Open Sans', 'Montserrat', 'Raleway',
            'Cormorant Garamond', 'Playfair Display', 'Libre Baskerville',
            'DM Serif Display', 'EB Garamond',
        ];

        foreach ($data as $group => $keys) {
            if (!is_array($keys)) continue;
            foreach ($keys as $key => $value) {
                $value = (string) $value;

                // Validate theme colour values to prevent CSS injection
                if ($group === 'theme' && in_array($key, $allowedColorKeys, true)) {
                    if (!preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{2})?)?$/', $value)) {
                        continue;
                    }
                }

                // Validate theme font values against allowlist
                if ($group === 'theme' && in_array($key, $allowedFontKeys, true)) {
                    if (!in_array($value, $allowedFonts, true)) {
                        continue;
                    }
                }

                if ($this->isUrlField((string) $group, (string) $key) && $value !== '') {
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        continue;
                    }
                }

                $dbKey   = $this->resolveDbKey((string) $group, (string) $key);
                $dbGroup = (string) $group;

                // Upsert: update existing row, or insert a new one
                $exists = $db->query(
                    'SELECT id FROM site_settings WHERE key_name = ?',
                    [$dbKey]
                )->fetch();

                if ($exists) {
                    $db->query(
                        'UPDATE site_settings SET value=?, updated_at=NOW() WHERE key_name=?',
                        [$value, $dbKey]
                    );
                } else {
                    $db->query(
                        'INSERT INTO site_settings (key_name, value, type, group_name) VALUES (?, ?, ?, ?)',
                        [$dbKey, $value, 'string', $dbGroup]
                    );
                }
            }
        }

        app_settings_refresh_cache();

        return $this->success(null, 'Settings updated.');
    }

    /**
     * Maps the admin frontend's group.key pairs to the internal database key_name.
     * The DB uses legacy prefixed keys (e.g. 'site_name') while the admin API
     * uses a cleaner group/key structure (e.g. site→name).
     */
    private function resolveDbKey(string $group, string $key): string
    {
        $map = [
            'site.name'               => 'site_name',
            'site.tagline'            => 'site_tagline',
            'site.logo_url'           => 'logo_url',
            'site.favicon_url'        => 'favicon_url',
            'contact.phone'           => 'contact_phone',
            'contact.email'           => 'contact_email',
            'contact.address'         => 'contact_address',
            'contact.map_embed_url'   => 'contact_map_embed_url',
            'social.instagram'        => 'social_instagram',
            'social.facebook'         => 'social_facebook',
            'social.twitter'          => 'social_twitter',
            'social.x'                => 'social_x',
            'social.pinterest'        => 'social_pinterest',
            'social.youtube'          => 'social_youtube',
            'social.linkedin'         => 'social_linkedin',
            'social.tiktok'           => 'social_tiktok',
            'seo.default_title'       => 'default_seo_title',
            'seo.default_description' => 'default_seo_description',
            // Theme keys are stored prefixed to avoid collisions
            'theme.primary_color'     => 'theme_primary_color',
            'theme.secondary_color'   => 'theme_secondary_color',
            'theme.accent_color'      => 'theme_accent_color',
            'theme.text_color'        => 'theme_text_color',
            'theme.bg_color'          => 'theme_bg_color',
            'theme.display_font'      => 'theme_display_font',
            'theme.body_font'         => 'theme_body_font',
        ];

        return $map["{$group}.{$key}"] ?? $key;
    }

    private function isUrlField(string $group, string $key): bool
    {
        if ($group === 'social') {
            return true;
        }

        return in_array("{$group}.{$key}", [
            'site.logo_url',
            'site.favicon_url',
            'contact.map_embed_url',
        ], true);
    }
}
