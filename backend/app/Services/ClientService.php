<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;

class ClientService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly Client $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(): array
    {
        return array_map([$this, 'format'], $this->model->findPublished());
    }

    private function format(array $row): array
    {
        $logo = !empty($row['logo_path'])
            ? $this->fmt->formatCover($row, 'logo')
            : null;

        return [
            'id'          => (int) $row['id'],
            'name'        => $row['name'],
            'website_url' => $row['website_url'] ?? null,
            'logo'        => $logo,
            'status'      => $row['is_published'] ? 'published' : 'draft',
            'sort_order'  => (int) $row['sort_order'],
        ];
    }
}
