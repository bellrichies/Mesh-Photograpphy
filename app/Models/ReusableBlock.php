<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ReusableBlock
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM reusable_blocks WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    public function findByKey(string $blockKey): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM reusable_blocks WHERE block_key = :block_key LIMIT 1',
            ['block_key' => $blockKey]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', string $status = '', string $type = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(name LIKE :query OR block_key LIKE :query OR title LIKE :query OR body LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($type !== '') {
            $whereParts[] = 'block_type = :block_type';
            $params['block_type'] = $type;
        }

        $sql = sprintf(
            'SELECT * FROM reusable_blocks WHERE %s ORDER BY updated_at DESC, id DESC LIMIT %d OFFSET %d',
            implode(' AND ', $whereParts),
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', string $status = '', string $type = ''): int
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(name LIKE :query OR block_key LIKE :query OR title LIKE :query OR body LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($type !== '') {
            $whereParts[] = 'block_type = :block_type';
            $params['block_type'] = $type;
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM reusable_blocks WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    public function keyExists(string $blockKey, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM reusable_blocks WHERE block_key = :block_key';
        $params = ['block_key' => $blockKey];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    /**
     * @return array<int, string>
     */
    public function distinctTypes(): array
    {
        $rows = $this->database->query('SELECT DISTINCT block_type FROM reusable_blocks ORDER BY block_type ASC')->fetchAll();
        if (! is_array($rows)) {
            return [];
        }

        $types = [];
        foreach ($rows as $row) {
            if (isset($row['block_type']) && is_string($row['block_type']) && $row['block_type'] !== '') {
                $types[] = $row['block_type'];
            }
        }

        return $types;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO reusable_blocks (
                name, block_key, block_type, title, body, json_payload, status, created_at, updated_at
            ) VALUES (
                :name, :block_key, :block_type, :title, :body, :json_payload, :status, NOW(), NOW()
            )',
            [
                'name' => $data['name'] ?? '',
                'block_key' => $data['block_key'] ?? '',
                'block_type' => $data['block_type'] ?? '',
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'json_payload' => $data['json_payload'] ?? null,
                'status' => $data['status'] ?? 'draft',
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
            'UPDATE reusable_blocks SET
                name = :name,
                block_key = :block_key,
                block_type = :block_type,
                title = :title,
                body = :body,
                json_payload = :json_payload,
                status = :status,
                updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'name' => $data['name'] ?? '',
                'block_key' => $data['block_key'] ?? '',
                'block_type' => $data['block_type'] ?? '',
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'json_payload' => $data['json_payload'] ?? null,
                'status' => $data['status'] ?? 'draft',
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->database->query('DELETE FROM reusable_blocks WHERE id = :id', ['id' => $id]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublished(): array
    {
        $rows = $this->database->query('SELECT * FROM reusable_blocks WHERE status = "published" ORDER BY id ASC')->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
