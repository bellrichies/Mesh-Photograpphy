<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BlogCategory
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM blog_categories WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM blog_categories WHERE slug = :slug LIMIT 1',
            ['slug' => $slug]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(name LIKE :query OR slug LIKE :query OR description LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        $rows = $this->database->query(
            sprintf(
                'SELECT * FROM blog_categories WHERE %s ORDER BY sort_order ASC, name ASC LIMIT %d OFFSET %d',
                implode(' AND ', $whereParts),
                max(1, $limit),
                max(0, $offset)
            ),
            $params
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = ''): int
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(name LIKE :query OR slug LIKE :query OR description LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM blog_categories WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $rows = $this->database->query(
            'SELECT * FROM blog_categories ORDER BY sort_order ASC, name ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_categories WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM blog_categories'
        )->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function posts(int $categoryId): array
    {
        $rows = $this->database->query(
            'SELECT bp.*
             FROM blog_posts bp
             INNER JOIN blog_post_categories bpc ON bpc.post_id = bp.id
             WHERE bpc.category_id = :category_id AND bp.deleted_at IS NULL
             ORDER BY bp.published_at DESC, bp.id DESC',
            ['category_id' => $categoryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO blog_categories (name, slug, description, sort_order, created_at, updated_at)
             VALUES (:name, :slug, :description, :sort_order, NOW(), NOW())',
            [
                'name' => $data['name'] ?? '',
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $this->database->query(
            'UPDATE blog_categories
             SET name = :name,
                 slug = :slug,
                 description = :description,
                 sort_order = :sort_order,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'name' => $data['name'] ?? '',
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->database->query('DELETE FROM blog_categories WHERE id = :id', ['id' => $id]);
    }
}