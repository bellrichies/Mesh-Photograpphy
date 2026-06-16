<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TeamMember;

class TeamMemberService
{
    private MediaFormatter $fmt;

    public function __construct(private readonly TeamMember $model)
    {
        $this->fmt = new MediaFormatter();
    }

    public function listPublished(): array
    {
        return array_map([$this, 'format'], $this->model->findPublished());
    }

    private function format(array $row): array
    {
        $photo = !empty($row['photo_path'])
            ? $this->fmt->formatCover($row, 'photo')
            : null;

        return [
            'id'            => (int) $row['id'],
            'name'          => $row['name'],
            'role'          => $row['role'] ?? null,
            'bio'           => $row['bio'] ?? null,
            'photo'         => $photo,
            'email'         => $row['email'] ?? null,
            'instagram_url' => $row['instagram_url'] ?? null,
            'status'        => $row['is_published'] ? 'published' : 'draft',
            'sort_order'    => (int) $row['sort_order'],
        ];
    }
}
