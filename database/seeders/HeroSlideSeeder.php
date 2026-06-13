<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use RuntimeException;

class HeroSlideSeeder
{
    private DemoContentFactory $factory;

    public function __construct(?DemoContentFactory $factory = null)
    {
        $this->factory = $factory ?? new DemoContentFactory();
    }

    public function run(Database $database): void
    {
        $slides = $this->factory->heroSlides();
        if ($slides === []) {
            return;
        }

        $titles = array_values(array_map(static fn (array $slide): string => (string) $slide['title'], $slides));
        $placeholders = implode(', ', array_fill(0, count($titles), '?'));
        $database->query('DELETE FROM hero_slides WHERE title IN (' . $placeholders . ')', $titles);

        foreach ($slides as $slide) {
            $mediaId = $this->lookupMediaId($database, (string) $slide['image_media_uuid']);
            if ($mediaId <= 0) {
                throw new RuntimeException('Hero slide seed media not found for UUID: ' . (string) $slide['image_media_uuid']);
            }

            $database->query(
                'INSERT INTO hero_slides (
                    title, subtitle, description, image_media_id, image_alt_text,
                    primary_cta_label, primary_cta_url, secondary_cta_label, secondary_cta_url,
                    sort_order, status, created_at, updated_at, deleted_at
                ) VALUES (
                    :title, :subtitle, :description, :image_media_id, :image_alt_text,
                    :primary_cta_label, :primary_cta_url, :secondary_cta_label, :secondary_cta_url,
                    :sort_order, :status, NOW(), NOW(), NULL
                )',
                [
                    'title' => $slide['title'],
                    'subtitle' => $slide['subtitle'] ?? null,
                    'description' => $slide['description'] ?? null,
                    'image_media_id' => $mediaId,
                    'image_alt_text' => $slide['image_alt_text'] ?? null,
                    'primary_cta_label' => $slide['primary_cta_label'] ?? null,
                    'primary_cta_url' => $slide['primary_cta_url'] ?? null,
                    'secondary_cta_label' => $slide['secondary_cta_label'] ?? null,
                    'secondary_cta_url' => $slide['secondary_cta_url'] ?? null,
                    'sort_order' => $slide['sort_order'] ?? 0,
                    'status' => $slide['status'] ?? 'published',
                ]
            );
        }
    }

    private function lookupMediaId(Database $database, string $uuid): int
    {
        return (int) $database->query(
            'SELECT id FROM media WHERE uuid = :uuid AND deleted_at IS NULL LIMIT 1',
            ['uuid' => $uuid]
        )->fetchColumn();
    }
}
