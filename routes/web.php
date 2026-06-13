<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Controllers\Web\BlogController;
use App\Controllers\Web\BookingController;
use App\Controllers\Web\ContactController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\PageController;
use App\Controllers\Web\PortfolioController;
use App\Controllers\Web\SeoController;
use App\Controllers\Web\ServiceController;
use App\Controllers\Web\TestimonialController;

return static function (Router $router): void {
    $router->get('/sitemap.xml', [SeoController::class, 'sitemap']);
    $router->get('/robots.txt', [SeoController::class, 'robots']);
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/portfolio', [PortfolioController::class, 'index']);
    $router->get('/portfolio/category/{slug}', [PortfolioController::class, 'category']);
    $router->get('/portfolio/{slug}', [PortfolioController::class, 'show']);
    $router->get('/services', [ServiceController::class, 'index']);
    $router->get('/services/{slug}', [ServiceController::class, 'show']);
    $router->get('/testimonials', [TestimonialController::class, 'index']);
    $router->get('/contact', [ContactController::class, 'show']);
    $router->post('/contact', [ContactController::class, 'store']);
    $router->get('/booking', [BookingController::class, 'show']);
    $router->post('/booking', [BookingController::class, 'store']);
    $router->get('/blog', [BlogController::class, 'index']);
    $router->get('/blog/search', [BlogController::class, 'search']);
    $router->get('/blog/category/{slug}', [BlogController::class, 'category']);
    $router->get('/blog/tag/{slug}', [BlogController::class, 'tag']);
    $router->get('/blog/{slug}', [BlogController::class, 'show']);
    $router->get('/terms-of-service', static function (Request $request, Response $response): Response {
        return $response->redirect('/terms', 301);
    });
    $router->get('/{slug}', [PageController::class, 'show']);
};
