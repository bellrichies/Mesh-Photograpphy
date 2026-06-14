<?php
declare(strict_types=1);

class CoreCmsSeeder
{
    public function run(\PDO $pdo): void
    {
        // Core site settings
        $settings = [
            ['site_name',            'Mesh Photography',                 'string',  'general'],
            ['site_tagline',         'Timeless Moments',                 'string',  'general'],
            ['contact_email',        'hello@meshphoto.com',              'string',  'general'],
            ['contact_phone',        '',                                 'string',  'general'],
            ['contact_address',      '',                                 'string',  'general'],
            ['social_instagram',     '',                                 'string',  'social'],
            ['social_facebook',      '',                                 'string',  'social'],
            ['social_pinterest',     '',                                 'string',  'social'],
            ['hero_heading',         'Timeless Moments',                 'string',  'hero'],
            ['hero_subheading',      'Professional photography for life\'s most meaningful events.', 'string', 'hero'],
            ['about_heading',        'About the Artist',                 'string',  'about'],
            ['about_body',           '',                                 'string',  'about'],
            ['booking_enabled',      '1',                                'boolean', 'booking'],
            ['inquiry_enabled',      '1',                                'boolean', 'contact'],
            ['google_analytics_id',  '',                                 'string',  'seo'],
            ['default_seo_title',    'Mesh Photography — Timeless Moments', 'string', 'seo'],
            ['default_seo_description', 'Professional photography for life\'s most meaningful events.', 'string', 'seo'],
        ];

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO site_settings (key_name, value, type, group_name)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($settings as $row) {
            $stmt->execute($row);
        }

        // Seed one blog category
        $pdo->prepare("INSERT IGNORE INTO blog_categories (name, slug) VALUES (?, ?)")
            ->execute(['Behind the Lens', 'behind-the-lens']);

        echo "[CoreCmsSeeder] Seeded " . count($settings) . " site settings and 1 blog category.\n";
    }
}
