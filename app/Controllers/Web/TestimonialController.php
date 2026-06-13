<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;
use App\Models\Testimonial;
use App\Services\SeoService;

class TestimonialController
{
    public function index(Request $request, Response $response): Response
    {
        $items = (new Testimonial(app_database()))->allPublished(100);
        $page = $this->page('testimonials');
        $seo = $page !== null
            ? $this->seo()->forPage($page)
            : $this->seo()->forArchive([
                'title' => 'Testimonials',
                'description' => 'Published client testimonials and qualitative proof points from recent work.',
                'canonical_path' => '/testimonials',
            ]);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('web/testimonials/index', [
            'title' => (string) ($seo['meta_title'] ?? ($page['title'] ?? 'Testimonials')),
            'items' => $items,
            'page' => $page,
            'seo' => $seo,
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
