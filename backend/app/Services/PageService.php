<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;

class PageService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly Page $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function getBySlug(string $slug): ?array
    {
        $row = $this->model->findBySlug($slug);
        if (!$row) return null;

        $appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');
        $ogImage = !empty($row['og_image_path'])
            ? $this->fmt->formatCover($row, 'og_image')
            : null;
        $canonical = $row['canonical_url'] ?? null;
        if (!$canonical && $appUrl !== '') {
            $canonical = $appUrl . '/' . ltrim((string) $row['slug'], '/');
        }

        $sections = $this->buildSections($row);

        return [
            'id'       => (int) $row['id'],
            'title'    => $row['title'],
            'slug'     => $row['slug'],
            'template' => $row['template'] ?? null,
            'sections' => $sections,
            'seo'      => [
                'meta_title'       => $row['seo_title'] ?? ($row['title'] . ' | Mesh Photography'),
                'meta_description' => $row['seo_description'] ?? null,
                'og_title'         => $row['og_title'] ?? $row['seo_title'] ?? $row['title'],
                'og_description'   => $row['og_description'] ?? $row['seo_description'] ?? null,
                'og_image_url'     => $ogImage ? $ogImage['url'] : null,
                'canonical_url'    => $canonical,
                'robots'           => $row['seo_robots'] ?? 'index, follow',
                'schema_markup'    => $row['schema_markup'] ?? null,
            ],
        ];
    }

    private function buildSections(array $row): array
    {
        $pageId = (int) $row['id'];

        // Load named sections from page_sections table
        $rawSections = $this->model->findSectionsByPageId($pageId);

        if (!empty($rawSections)) {
            return array_map(fn($s) => $this->formatSection($s), $rawSections);
        }

        // Fallback: wrap the legacy body column as a single rich_text section
        return [
            [
                'id'           => 1,
                'section_key'  => 'body',
                'section_type' => 'rich_text',
                'title'        => null,
                'content'      => $row['body'],
                'media_id'     => null,
                'media'        => null,
                'settings'     => (object) [],
                'sort_order'   => 0,
            ],
        ];
    }

    private function formatSection(array $section): array
    {
        $settings = null;
        if (!empty($section['settings_json'])) {
            $decoded = json_decode((string) $section['settings_json'], true);
            $settings = is_array($decoded) ? $decoded : null;
        }

        return [
            'id'           => (int) $section['id'],
            'section_key'  => $section['section_key'],
            'section_type' => $section['section_type'],
            'title'        => $section['label'],
            'content'      => $section['content'],
            'media_id'     => isset($section['media_id']) ? (int) $section['media_id'] : null,
            'media'        => !empty($section['media_path'])
                ? $this->fmt->formatCover($section, 'media')
                : null,
            'settings'     => $settings ?? (object) [],
            'sort_order'   => (int) $section['sort_order'],
        ];
    }
}
