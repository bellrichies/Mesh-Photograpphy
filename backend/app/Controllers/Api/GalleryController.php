<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Gallery;
use App\Services\GalleryService;

class GalleryController extends Controller
{
    private GalleryService $service;

    public function __construct()
    {
        $this->service = new GalleryService(new Gallery(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 12)));

        $filters = array_filter([
            'category' => $request->query('category'),
            'featured' => $request->query('featured'),
        ]);

        $result = $this->service->listPublished($filters, $page, $perPage);

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

    public function show(Request $request, Response $response): Response
    {
        $slug   = $request->param('slug');
        $detail = $this->service->getBySlug($slug);

        if (!$detail) {
            throw new HttpException(404, 'Gallery not found');
        }

        return $this->success($detail);
    }
}
