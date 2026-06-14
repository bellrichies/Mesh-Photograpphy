<?php

declare(strict_types=1);

use App\Core\Router;

return function (Router $router): void {
    // Health check
    $router->get('/api/v1/health', 'Api\HealthController@index');

    // Public settings
    $router->get('/api/v1/settings/public', 'Api\SettingController@public');

    // Galleries (public)
    $router->get('/api/v1/galleries',            'Api\GalleryController@index');
    $router->get('/api/v1/galleries/categories', 'Api\GalleryController@categories');
    $router->get('/api/v1/galleries/{slug}',     'Api\GalleryController@show');

    // Services (public)
    $router->get('/api/v1/services',       'Api\ServiceController@index');
    $router->get('/api/v1/services/{slug}','Api\ServiceController@show');

    // Testimonials (public)
    $router->get('/api/v1/testimonials', 'Api\TestimonialController@index');

    // Hero slides (public)
    $router->get('/api/v1/hero-slides', 'Api\HeroSlideController@index');

    // Blog (public)
    $router->get('/api/v1/blog/posts',               'Api\BlogController@index');
    $router->get('/api/v1/blog/posts/search',        'Api\BlogController@search');
    $router->get('/api/v1/blog/categories',          'Api\BlogController@categories');
    $router->get('/api/v1/blog/categories/{slug}',   'Api\BlogController@byCategory');
    $router->get('/api/v1/blog/tags',                'Api\BlogController@tags');
    $router->get('/api/v1/blog/tags/{slug}',         'Api\BlogController@byTag');
    $router->get('/api/v1/blog/posts/{slug}',        'Api\BlogController@show');

    // CMS pages (public)
    $router->get('/api/v1/pages/{slug}', 'Api\PageController@show');

    // Contact & Booking forms
    $router->post('/api/v1/contact', 'Api\ContactController@store');
    $router->post('/api/v1/booking', 'Api\BookingController@store');

    // Sitemap
    $router->get('/api/v1/sitemap', 'Api\SeoController@sitemap');

    // Blog RSS feed
    $router->get('/api/v1/blog/feed.xml', 'Api\RssFeedController@feed');

    // Blog archive (month/year groupings)
    $router->get('/api/v1/blog/archive', 'Api\BlogController@archive');
};
