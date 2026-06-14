<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Gallery;
use App\Services\GalleryService;
use Tests\TestCase;

class GalleryServiceTest extends TestCase
{
    private Gallery $model;
    private GalleryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['APP_URL'] = 'https://meshphoto.com';
        $this->model     = $this->createMock(Gallery::class);
        $this->service   = new GalleryService($this->model);
    }

    public function test_list_published_returns_paginated_structure(): void
    {
        $rows = [
            $this->makeGalleryRow(1, 'autumn-light', 'Autumn Light'),
            $this->makeGalleryRow(2, 'winter-grace', 'Winter Grace'),
        ];

        $this->model->method('findPublished')->willReturn($rows);
        $this->model->method('countPublished')->willReturn(2);

        $result = $this->service->listPublished([], 1, 20);

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(2, $result['items']);
        $this->assertSame(2, $result['total']);
    }

    public function test_list_published_paginates_correctly(): void
    {
        $rows = array_map(fn ($i) => $this->makeGalleryRow($i, "slug-$i", "Gallery $i"), range(1, 5));

        $this->model->method('findPublished')->willReturn($rows);
        $this->model->method('countPublished')->willReturn(5);

        $result = $this->service->listPublished([], 2, 2);

        $this->assertCount(2, $result['items']);
        $this->assertSame(5, $result['total']);
    }

    public function test_get_by_slug_returns_null_when_not_found(): void
    {
        $this->model->method('findBySlug')->willReturn(null);

        $result = $this->service->getBySlug('nonexistent-slug');

        $this->assertNull($result);
    }

    public function test_get_by_slug_returns_full_detail(): void
    {
        $row = $this->makeGalleryRow(1, 'autumn-light', 'Autumn Light');
        $row['description'] = 'A golden autumn series';

        $this->model->method('findBySlug')->willReturn($row);
        $this->model->method('getMedia')->willReturn([]);
        $this->model->method('countMedia')->willReturn(0);
        $this->model->method('getPrev')->willReturn(null);
        $this->model->method('getNext')->willReturn(null);

        $result = $this->service->getBySlug('autumn-light');

        $this->assertNotNull($result);
        $this->assertSame('autumn-light', $result['slug']);
        $this->assertArrayHasKey('seo', $result);
        $this->assertArrayHasKey('media', $result);
        $this->assertStringContainsString('autumn-light', $result['seo']['canonical_url']);
    }

    public function test_get_categories_formats_correctly(): void
    {
        $this->model->method('getCategories')->willReturn([
            ['name' => 'wedding', 'slug' => 'wedding', 'gallery_count' => 3],
            ['name' => 'portrait', 'slug' => 'portrait', 'gallery_count' => 7],
        ]);

        $result = $this->service->getCategories();

        $this->assertCount(2, $result);
        $this->assertSame('Wedding', $result[0]['name']); // ucfirst applied
        $this->assertSame(3, $result[0]['gallery_count']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeGalleryRow(int $id, string $slug, string $title): array
    {
        return [
            'id'          => $id,
            'title'       => $title,
            'slug'        => $slug,
            'description' => null,
            'is_featured' => 0,
            'is_published' => 1,
            'category'    => null,
            'sort_order'  => $id,
            'cover_path'  => null,
            'cover_alt'   => null,
            'created_at'  => '2026-01-01 00:00:00',
            'updated_at'  => '2026-01-01 00:00:00',
        ];
    }
}
