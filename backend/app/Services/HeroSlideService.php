<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HeroSlide;

class HeroSlideService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly HeroSlide $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(): array
    {
        return array_map([$this, 'format'], $this->model->findPublished());
    }

    private function format(array $row): array
    {
        $bg = !empty($row['image_path'])
            ? $this->fmt->formatCover($row, 'image')
            : null;

        return [
            'id'               => (int) $row['id'],
            'title'            => $row['heading'],
            'subtitle'         => $row['subheading'] ?? null,
            'background_image' => $bg,
            'cta_label'        => $row['cta_label'] ?? null,
            'cta_url'          => $row['cta_url'] ?? null,
            'sort_order'       => (int) $row['sort_order'],
            'status'           => $row['is_published'] ? 'published' : 'draft',
        ];
    }
}
