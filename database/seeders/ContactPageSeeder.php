<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;

class ContactPageSeeder
{
    private DemoContentFactory $factory;

    public function __construct(?DemoContentFactory $factory = null)
    {
        $this->factory = $factory ?? new DemoContentFactory();
    }

    public function run(Database $database): void
    {
        $page = $this->contactPageDefinition();
        if ($page !== null) {
            $mediaId = null;
            if (($page['featured_media_uuid'] ?? null) !== null) {
                $mediaId = $this->lookupId($database, 'media', 'uuid', (string) $page['featured_media_uuid']);
            }

            $database->query(
                'UPDATE pages
                 SET title = :title,
                     template = :template,
                     status = :status,
                     excerpt = :excerpt,
                     body = :body,
                     featured_media_id = :featured_media_id,
                     updated_at = NOW()
                 WHERE slug = :slug AND deleted_at IS NULL',
                [
                    'title' => $page['title'],
                    'template' => $page['template'],
                    'status' => $page['status'],
                    'excerpt' => $page['excerpt'] ?? null,
                    'body' => $page['body'] ?? null,
                    'featured_media_id' => $mediaId,
                    'slug' => $page['slug'],
                ]
            );
        }

        foreach ($this->factory->contactBlocks() as $block) {
            $database->query(
                'INSERT INTO reusable_blocks (
                    name, block_key, block_type, title, body, json_payload, status, created_at, updated_at
                ) VALUES (
                    :name, :block_key, :block_type, :title, :body, :json_payload, "published", NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    block_type = VALUES(block_type),
                    title = VALUES(title),
                    body = VALUES(body),
                    json_payload = VALUES(json_payload),
                    status = VALUES(status),
                    updated_at = NOW()',
                [
                    'name' => $block['name'],
                    'block_key' => $block['block_key'],
                    'block_type' => $block['block_type'],
                    'title' => $block['title'] ?? null,
                    'body' => $block['body'] ?? null,
                    'json_payload' => $this->jsonEncode($block['json_payload'] ?? null),
                ]
            );
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function contactPageDefinition(): ?array
    {
        foreach ($this->factory->pages() as $page) {
            if ((string) ($page['slug'] ?? '') === 'contact') {
                return $page;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>|null $value
     */
    private function jsonEncode(?array $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function lookupId(Database $database, string $table, string $column, string $value): int
    {
        $id = $database->query(
            sprintf('SELECT id FROM %s WHERE %s = :value LIMIT 1', $table, $column),
            ['value' => $value]
        )->fetchColumn();

        return (int) $id;
    }
}
