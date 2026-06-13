<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;
use App\Services\PageSectionService;
use App\Services\SeoService;

class PageController
{
    public function home(Request $request, Response $response): Response
    {
        return $this->renderPageBySlug('home', $response);
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        return $this->renderPageBySlug($slug, $response);
    }

    private function renderPageBySlug(string $slug, Response $response): Response
    {
        $pageModel = new Page(app_database());
        $page = $pageModel->findBySlug($slug);

        if (! is_array($page) || (string) ($page['status'] ?? 'draft') !== 'published') {
            throw new HttpException(404, 'Page not found.');
        }

        $sectionService = new PageSectionService(new \App\Models\PageSection(app_database()));
        $sections = array_values(array_filter($pageModel->sections((int) $page['id']), static function (array $section): bool {
            return (string) ($section['status'] ?? 'draft') === 'published';
        }));

        $seo = $this->seo()->forPage($page);
        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('web/page', [
            'title' => (string) ($seo['meta_title'] ?? ($page['title'] ?? config('app.name', 'Mesh Photography'))),
            'page' => $page,
            'sections' => $sections,
            'sectionService' => $sectionService,
            'seo' => $seo,
        ], 'layouts/main'));
    }

    private function seo(): SeoService
    {
        return new SeoService(app_database());
    }
}