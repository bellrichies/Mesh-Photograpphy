<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class MediaRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(
        string $query = '',
        ?string $fileType = null,
        string $sort = 'newest',
        int $limit = 30,
        int $offset = 0
    ): array {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR original_name LIKE :query OR alt_text LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($fileType !== null && $fileType !== '') {
            $whereParts[] = 'file_type = :file_type';
            $params['file_type'] = $fileType;
        }

        $orderBy = match ($sort) {
            'oldest' => 'created_at ASC',
            'name' => 'original_name ASC',
            'size' => 'size_bytes DESC',
            default => 'created_at DESC',
        };

        $sql = sprintf(
            'SELECT * FROM media WHERE %s ORDER BY %s LIMIT %d OFFSET %d',
            implode(' AND ', $whereParts),
            $orderBy,
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', ?string $fileType = null): int
    {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR original_name LIKE :query OR alt_text LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($fileType !== null && $fileType !== '') {
            $whereParts[] = 'file_type = :file_type';
            $params['file_type'] = $fileType;
        }

        $sql = 'SELECT COUNT(*) FROM media WHERE ' . implode(' AND ', $whereParts);
        return (int) $this->database->query($sql, $params)->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM media WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function updateMetadata(int $id, array $fields): void
    {
        $this->database->query(
            'UPDATE media
             SET title = :title,
                 alt_text = :alt_text,
                 caption = :caption,
                 description = :description,
                 updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'title' => $fields['title'] ?? null,
                'alt_text' => $fields['alt_text'] ?? null,
                'caption' => $fields['caption'] ?? null,
                'description' => $fields['description'] ?? null,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE media SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }
}
