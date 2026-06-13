<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;
use App\Models\Service;
use App\Repositories\GalleryRepository;
use App\Services\SeoService;

class ServiceController
{
    public function index(Request $request, Response $response): Response
    {
        $serviceModel = new Service(app_database());
        $items = $serviceModel->allPublished();
        $page = $this->page('services');
        $seo = $page !== null
            ? $this->seo()->forPage($page)
            : $this->seo()->forArchive([
                'title' => 'Services',
                'description' => 'Photography services, collections, and planning support across the published offering set.',
                'canonical_path' => '/services',
            ]);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('web/services/index', [
            'title' => (string) ($seo['meta_title'] ?? ($page['title'] ?? 'Services')),
            'items' => $items,
            'page' => $page,
            'seo' => $seo,
        ], 'layouts/main'));
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $serviceModel = new Service(app_database());
        $service = $serviceModel->findPublishedBySlug($slug);
        if (! is_array($service)) {
            throw new HttpException(404, 'Service not found.');
        }

        $seo = $this->seo()->forService($service);
        $relatedServices = $serviceModel->relatedPublished((int) ($service['id'] ?? 0), 3);
        $relatedGalleries = (new GalleryRepository(app_database()))->published(['featured' => 1], 3, 0);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('web/services/show', [
            'title' => (string) ($seo['meta_title'] ?? ($service['title'] ?? 'Service')),
            'service' => $service,
            'seo' => $seo,
            'relatedServices' => $relatedServices,
            'relatedGalleries' => $relatedGalleries,
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
