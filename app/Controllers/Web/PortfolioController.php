<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryMedia;
use App\Models\Page;
use App\Repositories\GalleryRepository;
use App\Services\SeoService;

class PortfolioController
{
    public function index(Request $request, Response $response): Response
    {
        return $this->renderIndex($request, $response, null);
    }

    public function category(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $category = (new GalleryCategory(app_database()))->findPublishedBySlug($slug);
        if (! is_array($category)) {
            throw new HttpException(404, 'Portfolio category not found.');
        }

        return $this->renderIndex($request, $response, $category);
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $repository = new GalleryRepository(app_database());
        $gallery = $repository->findPublishedBySlug($slug);

        if (! is_array($gallery)) {
            throw new HttpException(404, 'Gallery not found.');
        }

        $galleryId = (int) ($gallery['id'] ?? 0);
        $page = max(1, (int) $request->query('page', 1));
        $mediaPerPage = 18;
        $mediaOffset = ($page - 1) * $mediaPerPage;
        $galleryModel = new Gallery(app_database());
        $categories = $galleryModel->categories($galleryId);
        $galleryMediaModel = new GalleryMedia(app_database());
        $media = $galleryMediaModel->detailedByGalleryId($galleryId, $mediaPerPage, $mediaOffset);
        $mediaTotal = $galleryMediaModel->countForGallery($galleryId);

        foreach ($media as &$item) {
            $item['url'] = app_media_url((string) ($item['directory'] ?? ''), (string) ($item['stored_name'] ?? ''));
            $item['preview_url'] = app_media_preferred_url($item);
        }
        unset($item);

        if ((string) ($gallery['cover_directory'] ?? '') === '' && isset($media[0])) {
            $gallery['cover_directory'] = $media[0]['directory'] ?? '';
            $gallery['cover_stored_name'] = $media[0]['stored_name'] ?? '';
            $gallery['cover_alt_text'] = $media[0]['alt_text'] ?? '';
        }

        $related = [];
        if (isset($gallery['category_primary_id']) && (int) $gallery['category_primary_id'] > 0) {
            $related = $repository->relatedByCategory($galleryId, (int) $gallery['category_primary_id'], 3);
            foreach ($related as &$item) {
                $item['cover_url'] = app_media_url((string) ($item['cover_directory'] ?? ''), (string) ($item['cover_stored_name'] ?? ''));
                $item['cover_card_url'] = app_media_preferred_url($item, 'cover');
            }
            unset($item);
        }

        $view = new View(dirname(__DIR__, 3));
        $seo = $this->seo()->forGallery($gallery);
        return $response->html($view->render('web/portfolio/show', [
            'title' => (string) ($seo['meta_title'] ?? ($gallery['title'] ?? config('app.name', 'Mesh Photography'))),
            'gallery' => $gallery,
            'categories' => $categories,
            'media' => $media,
            'related' => $related,
            'coverUrl' => app_media_url((string) ($gallery['cover_directory'] ?? ''), (string) ($gallery['cover_stored_name'] ?? '')),
            'mediaPagination' => [
                'page' => $page,
                'limit' => $mediaPerPage,
                'total' => $mediaTotal,
                'total_pages' => (int) max(1, ceil($mediaTotal / $mediaPerPage)),
            ],
            'seo' => $seo,
        ], 'layouts/main'));
    }

    private function renderIndex(Request $request, Response $response, ?array $activeCategory): Response
    {
        $query = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 9;
        $offset = ($page - 1) * $limit;
        $contentPage = $this->page('portfolio');

        $filters = ['query' => $query];
        if ($activeCategory !== null) {
            $filters['category_primary_id'] = (int) ($activeCategory['id'] ?? 0);
        }

        $repository = new GalleryRepository(app_database());
        $items = $repository->published($filters, $limit, $offset);
        foreach ($items as &$item) {
            $item['cover_url'] = app_media_url((string) ($item['cover_directory'] ?? ''), (string) ($item['cover_stored_name'] ?? ''));
            $item['cover_card_url'] = app_media_preferred_url($item, 'cover');
        }
        unset($item);

        $total = $repository->countPublished($filters);
        $categories = (new GalleryCategory(app_database()))->publishedWithGalleryCounts();
        $seo = $activeCategory === null && $query === '' && $contentPage !== null
            ? $this->seo()->forPage($contentPage)
            : $this->seo()->forArchive([
                'title' => $activeCategory !== null ? (string) ($activeCategory['name'] ?? 'Portfolio') . ' Portfolio' : (string) ($contentPage['title'] ?? 'Portfolio'),
                'description' => $activeCategory !== null
                    ? (string) (($activeCategory['description'] ?? '') !== '' ? ($activeCategory['description'] ?? '') : 'Published portfolio work curated around this category.')
                    : (string) (($contentPage['excerpt'] ?? '') !== '' ? ($contentPage['excerpt'] ?? '') : 'Published portfolio work, signature sessions, and editorial gallery stories.'),
                'canonical_path' => $activeCategory !== null
                    ? '/portfolio/category/' . trim((string) ($activeCategory['slug'] ?? ''), '/')
                    : '/portfolio',
                'canonical_query' => $query !== '' ? ['q' => $query] : [],
                'robots_index' => $query === '',
                'robots_follow' => true,
            ]);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('web/portfolio/index', [
            'title' => (string) ($seo['meta_title'] ?? ($activeCategory !== null ? (string) ($activeCategory['name'] ?? 'Portfolio') . ' Portfolio' : 'Portfolio')),
            'items' => $items,
            'query' => $query,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'page' => $contentPage,
            'seo' => $seo,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
        ], 'layouts/main'));
    }

    private function seo(): SeoService
    {
        return new SeoService(app_database());
    }

    private function page(string $slug): ?array
    {
        return (new Page(app_database()))->findPublishedBySlug($slug);
    }
}
