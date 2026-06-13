<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;

class AboutPageSeeder
{
    private DemoContentFactory $factory;

    public function __construct(?DemoContentFactory $factory = null)
    {
        $this->factory = $factory ?? new DemoContentFactory();
    }

    public function run(Database $database): void
    {
        $page = $this->aboutPageDefinition();
        if ($page === null) {
            return;
        }

        $mediaId = null;
        if (($page['featured_media_uuid'] ?? null) !== null) {
            $mediaId = $this->lookupId($database, 'media', 'uuid', (string) $page['featured_media_uuid']);
        }

        $database->query(
            'UPDATE pages
             SET title = :title,
                 template = :template,
                 status = :status,
                 excerpt = :excerpt,
                 body = :body,
                 featured_media_id = :featured_media_id,
                 updated_at = NOW()
             WHERE slug = :slug AND deleted_at IS NULL',
            [
                'title' => $page['title'],
                'template' => $page['template'],
                'status' => $page['status'],
                'excerpt' => $page['excerpt'] ?? null,
                'body' => $page['body'] ?? null,
                'featured_media_id' => $mediaId,
                'slug' => $page['slug'],
            ]
        );

        $pageId = $this->lookupId($database, 'pages', 'slug', 'about');
        if ($pageId <= 0) {
            return;
        }

        foreach ($this->factory->aboutSections() as $section) {
            $sectionMediaId = null;
            if (($section['media_uuid'] ?? null) !== null) {
                $sectionMediaId = $this->lookupId($database, 'media', 'uuid', (string) $section['media_uuid']);
            }

            $database->query(
                'INSERT INTO page_sections (
                    page_id, section_key, section_type, title, subtitle, body, cta_label, cta_url,
                    media_id, json_payload, sort_order, status, created_at, updated_at
                ) VALUES (
                    :page_id, :section_key, :section_type, :title, :subtitle, :body, :cta_label, :cta_url,
                    :media_id, :json_payload, :sort_order, "published", NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    section_type = VALUES(section_type),
                    title = VALUES(title),
                    subtitle = VALUES(subtitle),
                    body = VALUES(body),
                    cta_label = VALUES(cta_label),
                    cta_url = VALUES(cta_url),
                    media_id = VALUES(media_id),
                    json_payload = VALUES(json_payload),
                    sort_order = VALUES(sort_order),
                    status = VALUES(status),
                    updated_at = NOW()',
                [
                    'page_id' => $pageId,
                    'section_key' => $section['section_key'],
                    'section_type' => $section['section_type'],
                    'title' => $section['title'] ?? null,
                    'subtitle' => $section['subtitle'] ?? null,
                    'body' => $section['body'] ?? null,
                    'cta_label' => $section['cta_label'] ?? null,
                    'cta_url' => $section['cta_url'] ?? null,
                    'media_id' => $sectionMediaId,
                    'json_payload' => $this->jsonEncode($section['json_payload'] ?? null),
                    'sort_order' => $section['sort_order'] ?? 0,
                ]
            );
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function aboutPageDefinition(): ?array
    {
        foreach ($this->factory->pages() as $page) {
            if ((string) ($page['slug'] ?? '') === 'about') {
                return $page;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>|null $value
     */
    private function jsonEncode(?array $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function lookupId(Database $database, string $table, string $column, string $value): int
    {
        $id = $database->query(
            sprintf('SELECT id FROM %s WHERE %s = :value LIMIT 1', $table, $column),
            ['value' => $value]
        )->fetchColumn();

        return (int) $id;
    }
}
