<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Role
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query('SELECT id, name, slug, description FROM roles WHERE slug = :slug LIMIT 1', [
            'slug' => $slug,
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $rows = $this->database->query('SELECT id, name, slug, description FROM roles ORDER BY name ASC')->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
