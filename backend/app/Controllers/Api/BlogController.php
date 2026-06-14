<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\BlogPost;
use App\Services\BlogService;

class BlogController extends Controller
{
    private BlogService $service;

    public function __construct()
    {
        $this->service = new BlogService(new BlogPost(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $filters = array_filter([
            'category' => $request->query('category'),
            'tag'      => $request->query('tag'),
            'q'        => $request->query('q'),
        ]);

        $result = $this->service->listPublished($filters, $page, $perPage);

        return $this->success(
            $result['items'],
            'Success',
            200,
            $this->paginate($result['items'], $result['total'], $page, $perPage)
        );
    }

    public function show(Request $request, Response $response): Response
    {
        $slug   = $request->param('slug');
        $detail = $this->service->getBySlug($slug);

        if (!$detail) {
            throw new HttpException(404, 'Post not found');
        }

        return $this->success($detail);
    }

    public function search(Request $request, Response $response): Response
    {
        $q       = trim((string) $request->query('q', ''));
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(1, (int) $request->query('per_page', 10)));

        $result = $this->service->listPublished(['q' => $q], $page, $perPage);

        return $this->success(
            $result['items'],
            'Success',
            200,
            $this->paginate($result['items'], $result['total'], $page, $perPage)
        );
    }

    public function categories(Request $request, Response $response): Response
    {
        return $this->success($this->service->getCategories());
    }

    public function byCategory(Request $request, Response $response): Response
    {
        $slug     = $request->param('slug');
        $category = $this->service->getCategoryBySlug($slug);

        if (!$category) {
            throw new HttpException(404, 'Category not found');
        }

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));
        $result  = $this->service->listPublished(['category' => $slug], $page, $perPage);

        return $this->success(
            $result['items'],
            'Success',
            200,
            $this->paginate($result['items'], $result['total'], $page, $perPage)
        );
    }

    public function tags(Request $request, Response $response): Response
    {
        return $this->success($this->service->getAllTags());
    }

    public function byTag(Request $request, Response $response): Response
    {
        $slug = $request->param('slug');
        $tag  = $this->service->getTagBySlug($slug);

        if (!$tag) {
            throw new HttpException(404, 'Tag not found');
        }

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));
        $result  = $this->service->listPublished(['tag' => $slug], $page, $perPage);

        return $this->success(
            $result['items'],
            'Success',
            200,
            $this->paginate($result['items'], $result['total'], $page, $perPage)
        );
    }

    public function archive(Request $request, Response $response): Response
    {
        $db   = app_database();
        $rows = $db->query(
            'SELECT YEAR(published_at) AS year, MONTH(published_at) AS month,
                    COUNT(*) AS post_count
             FROM blog_posts
             WHERE deleted_at IS NULL
               AND (is_published = 1 OR (published_at IS NOT NULL AND published_at <= NOW()))
             GROUP BY YEAR(published_at), MONTH(published_at)
             ORDER BY year DESC, month DESC'
        )->fetchAll();

        $archive = array_map(fn(array $r) => [
            'year'       => (int) $r['year'],
            'month'      => (int) $r['month'],
            'post_count' => (int) $r['post_count'],
            'label'      => date('F Y', mktime(0, 0, 0, (int) $r['month'], 1, (int) $r['year'])),
        ], $rows);

        return $this->success($archive);
    }
}
