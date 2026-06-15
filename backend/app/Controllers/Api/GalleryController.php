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

    public function photos(Request $request, Response $response): Response
    {
        $db      = app_database();
        $perPage = min(100, max(1, (int) $request->query('per_page', 16)));

        // Deduplicated individual photos from published galleries, ordered by
        // gallery sort then media sort so the homepage grid feels curated.
        $rows = $db->query(
            'SELECT m.id, m.uuid, m.path, m.alt_text, m.width, m.height, m.file_size,
                    m.mime_type, m.file_name, m.original_name,
                    MIN(gm.sort_order) AS sort_order,
                    MIN(gm.caption)    AS caption
             FROM gallery_media gm
             JOIN media     m ON gm.media_id   = m.id  AND m.deleted_at IS NULL
             JOIN galleries g ON gm.gallery_id = g.id
                              AND g.deleted_at  IS NULL
                              AND g.is_published = 1
             GROUP BY m.id, m.uuid, m.path, m.alt_text, m.width, m.height,
                      m.file_size, m.mime_type, m.file_name, m.original_name
             ORDER BY MIN(g.sort_order) ASC, MIN(gm.sort_order) ASC
             LIMIT ?',
            [$perPage]
        )->fetchAll();

        $fmt   = new \App\Services\MediaFormatter();
        $items = array_map([$fmt, 'formatMedia'], $rows);

        return $this->success($items);
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
