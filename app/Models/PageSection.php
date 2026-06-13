<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class PageSection
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT * FROM page_sections WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byPageId(int $pageId): array
    {
        $rows = $this->database->query(
            'SELECT * FROM page_sections WHERE page_id = :page_id ORDER BY sort_order ASC, id ASC',
            ['page_id' => $pageId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function keyExists(int $pageId, string $sectionKey, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM page_sections WHERE page_id = :page_id AND section_key = :section_key';
        $params = [
            'page_id' => $pageId,
            'section_key' => $sectionKey,
        ];

        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        return (int) $this->database->query($sql, $params)->fetchColumn() > 0;
    }

    public function nextSortOrder(int $pageId): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM page_sections WHERE page_id = :page_id',
            ['page_id' => $pageId]
        )->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO page_sections (
                page_id, section_key, section_type, title, subtitle, body, cta_label, cta_url,
                media_id, json_payload, sort_order, status, created_at, updated_at
            ) VALUES (
                :page_id, :section_key, :section_type, :title, :subtitle, :body, :cta_label, :cta_url,
                :media_id, :json_payload, :sort_order, :status, NOW(), NOW()
            )',
            [
                'page_id' => $data['page_id'] ?? 0,
                'section_key' => $data['section_key'] ?? '',
                'section_type' => $data['section_type'] ?? '',
                'title' => $data['title'] ?? null,
                'subtitle' => $data['subtitle'] ?? null,
                'body' => $data['body'] ?? null,
                'cta_label' => $data['cta_label'] ?? null,
                'cta_url' => $data['cta_url'] ?? null,
                'media_id' => $data['media_id'] ?? null,
                'json_payload' => $data['json_payload'] ?? null,
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
            'UPDATE page_sections SET
                section_key = :section_key,
                section_type = :section_type,
                title = :title,
                subtitle = :subtitle,
                body = :body,
                cta_label = :cta_label,
                cta_url = :cta_url,
                media_id = :media_id,
                json_payload = :json_payload,
                sort_order = :sort_order,
                status = :status,
                updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'section_key' => $data['section_key'] ?? '',
                'section_type' => $data['section_type'] ?? '',
                'title' => $data['title'] ?? null,
                'subtitle' => $data['subtitle'] ?? null,
                'body' => $data['body'] ?? null,
                'cta_label' => $data['cta_label'] ?? null,
                'cta_url' => $data['cta_url'] ?? null,
                'media_id' => $data['media_id'] ?? null,
                'json_payload' => $data['json_payload'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'status' => $data['status'] ?? 'draft',
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->database->query('DELETE FROM page_sections WHERE id = :id', ['id' => $id]);
    }

    public function setStatus(int $id, string $status): void
    {
        $this->database->query(
            'UPDATE page_sections SET status = :status, updated_at = NOW() WHERE id = :id',
            ['id' => $id, 'status' => $status]
        );
    }

    public function setSortOrder(int $id, int $sortOrder): void
    {
        $this->database->query(
            'UPDATE page_sections SET sort_order = :sort_order, updated_at = NOW() WHERE id = :id',
            ['id' => $id, 'sort_order' => $sortOrder]
        );
    }
}
