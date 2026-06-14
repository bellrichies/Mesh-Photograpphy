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

        $appUrl   = rtrim($_ENV['APP_URL'] ?? '', '/');
        $ogImage  = !empty($row['og_image_path'])
            ? $this->fmt->formatCover($row, 'og_image')
            : null;

        return [
            'id'       => (int) $row['id'],
            'title'    => $row['title'],
            'slug'     => $row['slug'],
            'template' => null,
            'sections' => [
                [
                    'id'           => 1,
                    'section_type' => 'rich_text',
                    'title'        => null,
                    'content'      => $row['body'],
                    'settings'     => (object) [],
                    'sort_order'   => 0,
                ],
            ],
            'seo' => [
                'meta_title'       => $row['seo_title'] ?? ($row['title'] . ' | Mesh Photography'),
                'meta_description' => $row['seo_description'] ?? null,
                'og_title'         => $row['seo_title'] ?? $row['title'],
                'og_description'   => $row['seo_description'] ?? null,
                'og_image_url'     => $ogImage ? $ogImage['url'] : null,
                'canonical_url'    => $appUrl . '/' . $row['slug'],
                'robots'           => 'index, follow',
                'schema_markup'    => null,
            ],
        ];
    }
}
