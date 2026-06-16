<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\BlogPost;
use App\Services\BlogService;
use Tests\TestCase;

class BlogServiceTest extends TestCase
{
    private BlogPost $model;
    private BlogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['APP_URL'] = 'https://meshphoto.com';
        $this->model     = $this->createMock(BlogPost::class);
        $this->service   = new BlogService($this->model);
    }

    public function test_list_published_returns_correct_structure(): void
    {
        $rows = [
            $this->makePostRow(1, 'post-one', 'Post One'),
            $this->makePostRow(2, 'post-two', 'Post Two'),
        ];

        $this->model->method('findPublished')->willReturn($rows);
        $this->model->method('countPublished')->willReturn(2);

        $result = $this->service->listPublished([], 1, 10);

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(2, $result['items']);
        $this->assertSame(2, $result['total']);
    }

    public function test_get_by_slug_returns_null_for_missing_post(): void
    {
        $this->model->method('findBySlug')->willReturn(null);

        $this->assertNull($this->service->getBySlug('does-not-exist'));
    }

    public function test_get_by_slug_includes_seo_and_related(): void
    {
        $row = $this->makePostRow(1, 'my-post', 'My Post');
        $row['body']        = '<p>Content</p>';
        $row['author_first'] = 'Jane';
        $row['author_last']  = 'Doe';
        $row['author_id']    = 5;
        $row['cat_id']       = 2;

        $this->model->method('findBySlug')->willReturn($row);
        $this->model->method('getTagsForPost')->willReturn([]);
        $this->model->method('getRelated')->willReturn([]);
        $this->model->method('getPrev')->willReturn(null);
        $this->model->method('getNext')->willReturn(null);

        $result = $this->service->getBySlug('my-post');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('seo', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertSame('Jane Doe', $result['author']['name']);
        $this->assertStringContainsString('/blog/my-post', $result['seo']['canonical_url']);
    }

    public function test_get_by_slug_formats_related_posts_without_category_fields(): void
    {
        $row = $this->makePostRow(1, 'my-post', 'My Post');
        $row['body'] = '<p>Content</p>';
        $row['cat_id'] = 2;

        $related = $this->makePostRow(2, 'related-post', 'Related Post');
        unset($related['cat_id'], $related['cat_name'], $related['cat_slug']);

        $this->model->method('findBySlug')->willReturn($row);
        $this->model->method('getTagsForPost')->willReturn([]);
        $this->model->method('getRelated')->willReturn([$related]);
        $this->model->method('getPrev')->willReturn(null);
        $this->model->method('getNext')->willReturn(null);

        $result = $this->service->getBySlug('my-post');

        $this->assertNotNull($result);
        $this->assertCount(1, $result['related_posts']);
        $this->assertSame([], $result['related_posts'][0]['categories']);
    }

    public function test_get_categories_formats_post_count(): void
    {
        $this->model->method('getCategories')->willReturn([
            ['id' => 1, 'name' => 'Weddings', 'slug' => 'weddings', 'post_count' => '5'],
        ]);

        $result = $this->service->getCategories();

        $this->assertCount(1, $result);
        $this->assertSame(5, $result[0]['post_count']); // cast to int
    }

    public function test_get_all_tags_returns_formatted_list(): void
    {
        $this->model->method('getTags')->willReturn([
            ['id' => 1, 'name' => 'Nature', 'slug' => 'nature', 'post_count' => '3'],
            ['id' => 2, 'name' => 'Travel', 'slug' => 'travel', 'post_count' => '8'],
        ]);

        $result = $this->service->getAllTags();

        $this->assertCount(2, $result);
        $this->assertSame(3, $result[0]['post_count']);
    }

    public function test_list_published_status_is_published(): void
    {
        $this->model->method('findPublished')->willReturn([
            $this->makePostRow(1, 'slug', 'Title'),
        ]);
        $this->model->method('countPublished')->willReturn(1);

        $result = $this->service->listPublished([], 1, 10);

        $this->assertSame('published', $result['items'][0]['status']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePostRow(int $id, string $slug, string $title): array
    {
        return [
            'id'           => $id,
            'title'        => $title,
            'slug'         => $slug,
            'excerpt'      => null,
            'cover_path'   => null,
            'cover_alt'    => null,
            'is_published' => 1,
            'published_at' => '2026-01-01 00:00:00',
            'cat_id'       => null,
            'cat_name'     => null,
            'cat_slug'     => null,
            'author_first' => null,
            'author_last'  => null,
            'author_id'    => null,
            'seo_title'    => null,
            'seo_description' => null,
            'created_at'   => '2026-01-01 00:00:00',
            'updated_at'   => '2026-01-01 00:00:00',
        ];
    }
}
