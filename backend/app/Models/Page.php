<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Page
{
    public function __construct(private readonly Database $db) {}

    public function findBySlug(string $slug): ?array
    {
        return $this->db->query(
            'SELECT p.*,
                    m.id AS og_image_id, m.uuid AS og_image_uuid, m.path AS og_image_path,
                    m.alt_text AS og_image_alt, m.original_name AS og_image_original,
                    m.file_name AS og_image_file, m.mime_type AS og_image_mime,
                    m.file_size AS og_image_size, m.width AS og_image_width,
                    m.height AS og_image_height
             FROM pages p
             LEFT JOIN media m ON p.og_image_id = m.id AND m.deleted_at IS NULL
             WHERE p.slug = ? AND p.deleted_at IS NULL AND p.is_published = 1
             LIMIT 1',
            [$slug]
        )->fetch() ?: null;
    }

    public function findAll(): array
    {
        return $this->db->query(
            'SELECT id, title, slug, created_at, updated_at
             FROM pages WHERE deleted_at IS NULL AND is_published = 1
             ORDER BY title ASC'
        )->fetchAll();
    }

    public function findSectionsByPageId(int $pageId): array
    {
        return $this->db->query(
            'SELECT ps.id, ps.section_key, ps.section_type, ps.label, ps.content,
                    ps.media_id, ps.settings_json, ps.sort_order,
                    m.uuid AS media_uuid, m.path AS media_path, m.alt_text AS media_alt,
                    m.original_name AS media_original, m.file_name AS media_file,
                    m.mime_type AS media_mime, m.file_size AS media_size,
                    m.width AS media_width, m.height AS media_height
             FROM page_sections ps
             LEFT JOIN media m ON ps.media_id = m.id AND m.deleted_at IS NULL
             WHERE ps.page_id = ?
             ORDER BY ps.sort_order ASC, ps.id ASC',
            [$pageId]
        )->fetchAll();
    }

    public function insertSection(
        int $pageId,
        string $sectionKey,
        string $sectionType,
        ?string $label,
        ?string $content,
        ?int $mediaId,
        ?string $settingsJson,
        int $sortOrder
    ): void
    {
        $this->db->query(
            'INSERT INTO page_sections
                (page_id, section_key, section_type, label, content, media_id, settings_json, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [$pageId, $sectionKey, $sectionType, $label, $content, $mediaId, $settingsJson, $sortOrder]
        );
    }

    public function deleteSectionsForPage(int $pageId): void
    {
        $this->db->query('DELETE FROM page_sections WHERE page_id = ?', [$pageId]);
    }
}
