<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class HeroSlide
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT hs.*, m.directory AS image_directory, m.stored_name AS image_stored_name,
                    m.alt_text AS media_alt_text, m.title AS media_title
             FROM hero_slides hs
             LEFT JOIN media m ON m.id = hs.image_media_id AND m.deleted_at IS NULL
             WHERE hs.id = :id AND hs.deleted_at IS NULL
             LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', string $status = '', int $limit = 20, int $offset = 0): array
    {
        $whereParts = ['hs.deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(hs.title LIKE :query OR hs.subtitle LIKE :query OR hs.description LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'hs.status = :status';
            $params['status'] = $status;
        }

        $sql = sprintf(
            'SELECT hs.*, m.directory AS image_directory, m.stored_name AS image_stored_name,
                    m.alt_text AS media_alt_text, m.title AS media_title
             FROM hero_slides hs
             LEFT JOIN media m ON m.id = hs.image_media_id AND m.deleted_at IS NULL
             WHERE %s
             ORDER BY hs.sort_order ASC, hs.updated_at DESC, hs.id DESC
             LIMIT %d OFFSET %d',
            implode(' AND ', $whereParts),
            max(1, $limit),
            max(0, $offset)
        );

        $rows = $this->database->query($sql, $params)->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function count(string $query = '', string $status = ''): int
    {
        $whereParts = ['deleted_at IS NULL'];
        $params = [];

        if ($query !== '') {
            $whereParts[] = '(title LIKE :query OR subtitle LIKE :query OR description LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $whereParts[] = 'status = :status';
            $params['status'] = $status;
        }

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM hero_slides WHERE ' . implode(' AND ', $whereParts),
            $params
        )->fetchColumn();
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM hero_slides WHERE deleted_at IS NULL'
        )->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO hero_slides (
                title, subtitle, description, image_media_id, image_alt_text,
                primary_cta_label, primary_cta_url, secondary_cta_label, secondary_cta_url,
                sort_order, status, created_at, updated_at
            ) VALUES (
                :title, :subtitle, :description, :image_media_id, :image_alt_text,
                :primary_cta_label, :primary_cta_url, :secondary_cta_label, :secondary_cta_url,
                :sort_order, :status, NOW(), NOW()
            )',
            [
                'title' => $data['title'] ?? '',
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'] ?? null,
                'image_media_id' => $data['image_media_id'] ?? null,
                'image_alt_text' => $data['image_alt_text'] ?? null,
                'primary_cta_label' => $data['primary_cta_label'] ?? null,
                'primary_cta_url' => $data['primary_cta_url'] ?? null,
                'secondary_cta_label' => $data['secondary_cta_label'] ?? null,
                'secondary_cta_url' => $data['secondary_cta_url'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
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
            'UPDATE hero_slides SET
                title = :title,
                subtitle = :subtitle,
                description = :description,
                image_media_id = :image_media_id,
                image_alt_text = :image_alt_text,
                primary_cta_label = :primary_cta_label,
                primary_cta_url = :primary_cta_url,
                secondary_cta_label = :secondary_cta_label,
                secondary_cta_url = :secondary_cta_url,
                sort_order = :sort_order,
                status = :status,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'title' => $data['title'] ?? '',
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'] ?? null,
                'image_media_id' => $data['image_media_id'] ?? null,
                'image_alt_text' => $data['image_alt_text'] ?? null,
                'primary_cta_label' => $data['primary_cta_label'] ?? null,
                'primary_cta_url' => $data['primary_cta_url'] ?? null,
                'secondary_cta_label' => $data['secondary_cta_label'] ?? null,
                'secondary_cta_url' => $data['secondary_cta_url'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'status' => $data['status'] ?? 'draft',
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE hero_slides SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublished(): array
    {
        $rows = $this->database->query(
            'SELECT hs.*, m.directory AS image_directory, m.stored_name AS image_stored_name,
                    m.alt_text AS media_alt_text, m.title AS media_title
             FROM hero_slides hs
             LEFT JOIN media m ON m.id = hs.image_media_id AND m.deleted_at IS NULL
             WHERE hs.status = "published" AND hs.deleted_at IS NULL
             ORDER BY hs.sort_order ASC, hs.id ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}
