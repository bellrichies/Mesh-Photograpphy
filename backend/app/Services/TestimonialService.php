<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Testimonial;

class TestimonialService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly Testimonial $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(): array
    {
        return array_map([$this, 'format'], $this->model->findPublished());
    }

    private function format(array $row): array
    {
        $portrait = !empty($row['portrait_path'])
            ? $this->fmt->formatCover($row, 'portrait')
            : null;

        return [
            'id'          => (int) $row['id'],
            'client_name' => $row['client_name'],
            'client_role' => $row['client_title'] ?? null,
            'body'        => $row['quote'],
            'rating'      => (int) $row['rating'],
            'portrait'    => $portrait,
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'sort_order'  => (int) $row['sort_order'],
        ];
    }
}
