<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;

class CoreCmsSeeder
{
    public function run(Database $database): void
    {
        $this->seedSettings($database);
        $this->seedPages($database);
        $this->seedReusableBlocks($database);
        $this->seedSeoMeta($database);
    }

    private function seedSettings(Database $database): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'site_name', 'value_text' => 'Mesh Photography', 'is_public' => 1],
            ['group' => 'general', 'key' => 'tagline', 'value_text' => 'Editorial imagery for modern celebrations, portraits, and brands.', 'is_public' => 1],
            ['group' => 'general', 'key' => 'footer_text', 'value_text' => 'Serving couples, founders, and families with calm direction, thoughtful pacing, and imagery designed to feel timeless.', 'is_public' => 1],
            ['group' => 'general', 'key' => 'site_status', 'value_text' => 'live', 'is_public' => 1],
            ['group' => 'general', 'key' => 'show_author_credit', 'value_text' => '0', 'is_public' => 1],
            ['group' => 'contact', 'key' => 'contact_email', 'value_text' => 'hello@meshphotography.com', 'is_public' => 1],
            ['group' => 'contact', 'key' => 'phone', 'value_text' => '+1 (202) 555-0198', 'is_public' => 1],
            ['group' => 'contact', 'key' => 'address', 'value_text' => "Mesh Photography Studio\n1458 Penn Quarter Loft\nWashington, DC 20001\nUnited States", 'is_public' => 1],
            ['group' => 'contact', 'key' => 'business_hours', 'value_json' => json_encode([
                'timezone' => 'America/New_York',
                'days' => [
                    ['label' => 'Monday - Thursday', 'hours' => '10:00 AM - 5:00 PM'],
                    ['label' => 'Friday', 'hours' => '10:00 AM - 3:00 PM'],
                    ['label' => 'Saturday', 'hours' => 'By appointment'],
                    ['label' => 'Sunday', 'hours' => 'Closed'],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'is_public' => 1],
            ['group' => 'social', 'key' => 'instagram_url', 'value_text' => 'https://instagram.com/meshphotography', 'is_public' => 1],
            ['group' => 'social', 'key' => 'behance_url', 'value_text' => 'https://behance.net/meshphotography', 'is_public' => 1],
            ['group' => 'social', 'key' => 'youtube_url', 'value_text' => 'https://youtube.com/@meshphotography', 'is_public' => 1],
            ['group' => 'social', 'key' => 'social_links', 'value_json' => json_encode([
                'instagram' => 'https://instagram.com/meshphotography',
                'behance' => 'https://behance.net/meshphotography',
                'youtube' => 'https://youtube.com/@meshphotography',
                'pinterest' => 'https://pinterest.com/meshphotography',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'is_public' => 1],
            ['group' => 'branding', 'key' => 'brand_site_title', 'value_text' => 'Mesh Photography', 'is_public' => 1],
            ['group' => 'branding', 'key' => 'logo_alt_text', 'value_text' => 'Mesh Photography logo mark', 'is_public' => 1],
            ['group' => 'branding', 'key' => 'brand_primary_color', 'value_text' => '#9A7B5C', 'is_public' => 1],
            ['group' => 'branding', 'key' => 'brand_secondary_color', 'value_text' => '#1A1A1A', 'is_public' => 1],
            ['group' => 'email', 'key' => 'mailer_from_name', 'value_text' => 'Mesh Photography', 'is_public' => 0],
            ['group' => 'email', 'key' => 'mailer_from_email', 'value_text' => 'hello@meshphotography.com', 'is_public' => 0],
            ['group' => 'email', 'key' => 'mailer_reply_to', 'value_text' => 'hello@meshphotography.com', 'is_public' => 0],
            ['group' => 'email', 'key' => 'inquiry_recipients', 'value_json' => json_encode([
                'hello@meshphotography.com',
                'bookings@meshphotography.com',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'is_public' => 0],
            ['group' => 'email', 'key' => 'mail_enabled', 'value_text' => '1', 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'max_file_size_mb', 'value_text' => '20', 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'allowed_mime_groups', 'value_json' => json_encode([
                'images',
                'documents',
                'videos',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'max_image_width', 'value_text' => '6000', 'is_public' => 0],
            ['group' => 'uploads', 'key' => 'max_image_height', 'value_text' => '6000', 'is_public' => 0],
            ['group' => 'seo', 'key' => 'default_meta_title_pattern', 'value_text' => '{{title}} | {{site_name}}', 'is_public' => 1],
            ['group' => 'seo', 'key' => 'default_meta_description', 'value_text' => 'Mesh Photography creates refined visual narratives for weddings, portraits, and thoughtful brands.', 'is_public' => 1],
            ['group' => 'seo', 'key' => 'default_robots_index', 'value_text' => '1', 'is_public' => 1],
            ['group' => 'seo', 'key' => 'default_meta_keywords', 'value_json' => json_encode([
                'editorial wedding photographer washington dc',
                'portrait photographer washington dc',
                'brand photography studio washington dc',
                'luxury wedding photography',
                'editorial portrait sessions',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'is_public' => 1],
        ];

        foreach ($settings as $setting) {
            $database->query(
                'INSERT INTO settings (`group`, `key`, value_text, value_json, is_public, created_at, updated_at)
                 VALUES (:group_name, :key_name, :value_text, :value_json, :is_public, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE value_text = VALUES(value_text), value_json = VALUES(value_json), is_public = VALUES(is_public), updated_at = NOW()',
                [
                    'group_name' => $setting['group'],
                    'key_name' => $setting['key'],
                    'value_text' => $setting['value_text'] ?? null,
                    'value_json' => $setting['value_json'] ?? null,
                    'is_public' => $setting['is_public'],
                ]
            );
        }
    }

    private function seedPages(Database $database): void
    {
        $pages = [
            ['title' => 'Home', 'slug' => 'home', 'template' => 'homepage', 'status' => 'published', 'excerpt' => 'Hero landing page for the premium brand experience.', 'is_system' => 1],
            ['title' => 'About', 'slug' => 'about', 'template' => 'default', 'status' => 'published', 'excerpt' => 'Studio story and photographic philosophy.', 'is_system' => 0],
            ['title' => 'Services', 'slug' => 'services', 'template' => 'default', 'status' => 'published', 'excerpt' => 'Signature photography service offerings.', 'is_system' => 0],
            ['title' => 'Portfolio', 'slug' => 'portfolio', 'template' => 'default', 'status' => 'published', 'excerpt' => 'Curated galleries and featured work.', 'is_system' => 0],
            ['title' => 'Blog', 'slug' => 'blog', 'template' => 'default', 'status' => 'published', 'excerpt' => 'Editorial storytelling and guides.', 'is_system' => 0],
            ['title' => 'Testimonials', 'slug' => 'testimonials', 'template' => 'default', 'status' => 'published', 'excerpt' => 'Client stories and social proof.', 'is_system' => 0],
            ['title' => 'Contact', 'slug' => 'contact', 'template' => 'default', 'status' => 'published', 'excerpt' => 'Inquiry form and studio contact details.', 'is_system' => 0],
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'template' => 'legal', 'status' => 'published', 'excerpt' => 'Privacy and data handling policy.', 'is_system' => 1],
        ];

        foreach ($pages as $index => $page) {
            $database->query(
                'INSERT INTO pages (
                    title, slug, template, status, excerpt, body, sort_order, is_system, published_at, created_at, updated_at
                ) VALUES (
                    :title, :slug, :template, :status, :excerpt, :body, :sort_order, :is_system, NOW(), NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    template = VALUES(template),
                    status = VALUES(status),
                    excerpt = VALUES(excerpt),
                    body = VALUES(body),
                    sort_order = VALUES(sort_order),
                    is_system = VALUES(is_system),
                    updated_at = NOW()',
                [
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'template' => $page['template'],
                    'status' => $page['status'],
                    'excerpt' => $page['excerpt'],
                    'body' => null,
                    'sort_order' => $index + 1,
                    'is_system' => $page['is_system'],
                ]
            );
        }

        $homePageId = (int) $database->query('SELECT id FROM pages WHERE slug = :slug LIMIT 1', ['slug' => 'home'])->fetchColumn();
        if ($homePageId > 0) {
            $sections = [
                [
                    'section_key' => 'hero',
                    'section_type' => 'hero',
                    'title' => 'Crafted Stories, Timeless Frames',
                    'subtitle' => 'Mesh Photography',
                    'body' => 'Editorial minimalism, cinematic warmth, and a calm production rhythm for weddings, portraits, and brand narratives.',
                    'cta_label' => 'Start Your Inquiry',
                    'cta_url' => '/contact',
                    'sort_order' => 1,
                ],
                [
                    'section_key' => 'brand-intro',
                    'section_type' => 'brand-intro',
                    'title' => 'A refined studio experience, built around story and atmosphere.',
                    'subtitle' => 'Brand Intro',
                    'body' => 'Use the About page and homepage sections to shape the first impression visitors receive.',
                    'sort_order' => 2,
                ],
                [
                    'section_key' => 'featured-galleries',
                    'section_type' => 'featured-galleries',
                    'title' => 'Featured Galleries',
                    'subtitle' => 'A curated selection of signature narratives.',
                    'sort_order' => 3,
                ],
                [
                    'section_key' => 'services-teaser',
                    'section_type' => 'services-teaser',
                    'title' => 'Signature Services',
                    'subtitle' => 'Designed for modern celebrations, portraits, and editorial campaigns.',
                    'sort_order' => 4,
                ],
                [
                    'section_key' => 'testimonials-strip',
                    'section_type' => 'testimonials-strip',
                    'title' => 'Client Stories',
                    'subtitle' => 'Social proof from recent collaborations.',
                    'sort_order' => 5,
                ],
                [
                    'section_key' => 'featured-blog-posts',
                    'section_type' => 'featured-blog-posts',
                    'title' => 'From the Journal',
                    'subtitle' => 'Planning notes, stories, and editorial insights.',
                    'sort_order' => 6,
                ],
                [
                    'section_key' => 'cta-banner',
                    'section_type' => 'cta-banner',
                    'title' => 'Ready to shape your story?',
                    'body' => 'Share the atmosphere, timing, and feeling you want preserved. We will take it from there.',
                    'cta_label' => 'Get In Touch',
                    'cta_url' => '/contact',
                    'sort_order' => 7,
                ],
            ];

            foreach ($sections as $section) {
                $database->query(
                    'INSERT INTO page_sections (
                        page_id, section_key, section_type, title, subtitle, body, cta_label, cta_url, sort_order, status, created_at, updated_at
                    ) VALUES (
                        :page_id, :section_key, :section_type, :title, :subtitle, :body, :cta_label, :cta_url, :sort_order, "published", NOW(), NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        section_type = VALUES(section_type),
                        title = VALUES(title),
                        subtitle = VALUES(subtitle),
                        body = VALUES(body),
                        cta_label = VALUES(cta_label),
                        cta_url = VALUES(cta_url),
                        sort_order = VALUES(sort_order),
                        status = VALUES(status),
                        updated_at = NOW()',
                    [
                        'page_id' => $homePageId,
                        'section_key' => $section['section_key'],
                        'section_type' => $section['section_type'],
                        'title' => $section['title'] ?? null,
                        'subtitle' => $section['subtitle'] ?? null,
                        'body' => $section['body'] ?? null,
                        'cta_label' => $section['cta_label'] ?? null,
                        'cta_url' => $section['cta_url'] ?? null,
                        'sort_order' => $section['sort_order'],
                    ]
                );
            }
        }
    }

    private function seedReusableBlocks(Database $database): void
    {
        $blocks = [
            ['name' => 'Global CTA', 'block_key' => 'global-cta', 'block_type' => 'cta', 'title' => 'Let us photograph your story', 'body' => 'Tell us about your event and we will craft a visual narrative together.'],
            ['name' => 'Footer Note', 'block_key' => 'footer-note', 'block_type' => 'footer', 'title' => 'Mesh Photography', 'body' => 'Premium photography studio focused on editorial storytelling.'],
        ];

        foreach ($blocks as $block) {
            $database->query(
                'INSERT INTO reusable_blocks (
                    name, block_key, block_type, title, body, status, created_at, updated_at
                ) VALUES (
                    :name, :block_key, :block_type, :title, :body, "published", NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    block_type = VALUES(block_type),
                    title = VALUES(title),
                    body = VALUES(body),
                    status = VALUES(status),
                    updated_at = NOW()',
                $block
            );
        }
    }

    private function seedSeoMeta(Database $database): void
    {
        $rows = $database->query('SELECT id, slug, title, excerpt FROM pages WHERE deleted_at IS NULL')->fetchAll();
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $pageId = (int) ($row['id'] ?? 0);
            if ($pageId <= 0) {
                continue;
            }

            $title = (string) ($row['title'] ?? '');
            $slug = (string) ($row['slug'] ?? '');
            $excerpt = (string) ($row['excerpt'] ?? '');

            $database->query(
                'INSERT INTO seo_meta (
                    entity_type, entity_id, meta_title, meta_description, og_title, og_description,
                    canonical_url, robots_index, robots_follow, created_at, updated_at
                ) VALUES (
                    "page", :entity_id, :meta_title, :meta_description, :og_title, :og_description,
                    :canonical_url, 1, 1, NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description),
                    og_title = VALUES(og_title),
                    og_description = VALUES(og_description),
                    canonical_url = VALUES(canonical_url),
                    robots_index = VALUES(robots_index),
                    robots_follow = VALUES(robots_follow),
                    updated_at = NOW()',
                [
                    'entity_id' => $pageId,
                    'meta_title' => $title . ' | Mesh Photography',
                    'meta_description' => $excerpt !== '' ? $excerpt : 'Premium photography by Mesh Photography.',
                    'og_title' => $title,
                    'og_description' => $excerpt,
                    'canonical_url' => rtrim((string) config('app.url', ''), '/') . '/' . ltrim($slug, '/'),
                ]
            );
        }
    }
}
