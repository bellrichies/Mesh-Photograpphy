<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\Middleware\JwtMiddleware;
use App\Core\Middleware\PermissionMiddleware;

return function (Router $router): void {
    $jwt  = [JwtMiddleware::class];
    $perm = fn(string $p): array => [...$jwt, PermissionMiddleware::for($p)];

    // Dashboard
    $router->get('/api/v1/admin/dashboard', 'Admin\DashboardController@index')
           ->middleware($jwt);

    // Galleries
    $router->get('/api/v1/admin/galleries',         'Admin\GalleryController@index')
           ->middleware($perm('manage-galleries'));
    $router->post('/api/v1/admin/galleries',        'Admin\GalleryController@store')
           ->middleware($perm('manage-galleries'));
    $router->get('/api/v1/admin/galleries/{id}',    'Admin\GalleryController@show')
           ->middleware($perm('manage-galleries'));
    $router->put('/api/v1/admin/galleries/{id}',    'Admin\GalleryController@update')
           ->middleware($perm('manage-galleries'));
    $router->delete('/api/v1/admin/galleries/{id}', 'Admin\GalleryController@destroy')
           ->middleware($perm('manage-galleries'));

    // Gallery media
    $router->post('/api/v1/admin/galleries/{id}/media',                    'Admin\GalleryController@attachMedia')
           ->middleware($perm('manage-galleries'));
    $router->put('/api/v1/admin/galleries/{id}/media/{mediaId}',           'Admin\GalleryController@updateMedia')
           ->middleware($perm('manage-galleries'));
    $router->delete('/api/v1/admin/galleries/{id}/media/{mediaId}',        'Admin\GalleryController@removeMedia')
           ->middleware($perm('manage-galleries'));
    $router->post('/api/v1/admin/galleries/{id}/media/reorder',            'Admin\GalleryController@reorderMedia')
           ->middleware($perm('manage-galleries'));

    // Media
    $router->get('/api/v1/admin/media',              'Admin\MediaController@index')
           ->middleware($perm('manage-media'));
    $router->post('/api/v1/admin/media/upload',      'Admin\MediaController@upload')
           ->middleware($perm('manage-media'));
    $router->put('/api/v1/admin/media/{id}',         'Admin\MediaController@update')
           ->middleware($perm('manage-media'));
    $router->delete('/api/v1/admin/media/{id}',      'Admin\MediaController@destroy')
           ->middleware($perm('manage-media'));
    $router->post('/api/v1/admin/media/{id}/archive','Admin\MediaController@archive')
           ->middleware($perm('manage-media'));

    // Blog posts
    $router->get('/api/v1/admin/blog/posts',              'Admin\BlogPostController@index')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/posts',             'Admin\BlogPostController@store')
           ->middleware($perm('manage-blog'));
    $router->get('/api/v1/admin/blog/posts/{id}',         'Admin\BlogPostController@show')
           ->middleware($perm('manage-blog'));
    $router->put('/api/v1/admin/blog/posts/{id}',         'Admin\BlogPostController@update')
           ->middleware($perm('manage-blog'));
    $router->delete('/api/v1/admin/blog/posts/{id}',      'Admin\BlogPostController@destroy')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/posts/{id}/autosave','Admin\BlogPostController@autosave')
           ->middleware($perm('manage-blog'));

    // Blog categories
    $router->get('/api/v1/admin/blog/categories',        'Admin\BlogCategoryController@index')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/categories',       'Admin\BlogCategoryController@store')
           ->middleware($perm('manage-blog'));
    $router->put('/api/v1/admin/blog/categories/{id}',   'Admin\BlogCategoryController@update')
           ->middleware($perm('manage-blog'));
    $router->delete('/api/v1/admin/blog/categories/{id}','Admin\BlogCategoryController@destroy')
           ->middleware($perm('manage-blog'));

    // Blog tags
    $router->get('/api/v1/admin/blog/tags',        'Admin\BlogTagController@index')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/tags',       'Admin\BlogTagController@store')
           ->middleware($perm('manage-blog'));
    $router->put('/api/v1/admin/blog/tags/{id}',   'Admin\BlogTagController@update')
           ->middleware($perm('manage-blog'));
    $router->delete('/api/v1/admin/blog/tags/{id}','Admin\BlogTagController@destroy')
           ->middleware($perm('manage-blog'));

    // Inquiries
    $router->get('/api/v1/admin/inquiries',                    'Admin\InquiryController@index')
           ->middleware($perm('manage-inquiries'));
    $router->get('/api/v1/admin/inquiries/{id}',               'Admin\InquiryController@show')
           ->middleware($perm('manage-inquiries'));
    $router->patch('/api/v1/admin/inquiries/{id}/status',      'Admin\InquiryController@updateStatus')
           ->middleware($perm('manage-inquiries'));
    $router->post('/api/v1/admin/inquiries/{id}/notes',        'Admin\InquiryController@addNote')
           ->middleware($perm('manage-inquiries'));

    // Bookings
    $router->get('/api/v1/admin/bookings',                'Admin\BookingController@index')
           ->middleware($perm('manage-inquiries'));
    $router->get('/api/v1/admin/bookings/{id}',           'Admin\BookingController@show')
           ->middleware($perm('manage-inquiries'));
    $router->patch('/api/v1/admin/bookings/{id}/status',  'Admin\BookingController@updateStatus')
           ->middleware($perm('manage-inquiries'));

    // Settings
    $router->get('/api/v1/admin/settings', 'Admin\SettingsController@index')
           ->middleware($perm('manage-settings'));
    $router->put('/api/v1/admin/settings', 'Admin\SettingsController@update')
           ->middleware($perm('manage-settings'));

    // Services
    $router->get('/api/v1/admin/services',         'Admin\ServiceController@index')
           ->middleware($perm('manage-services'));
    $router->post('/api/v1/admin/services',        'Admin\ServiceController@store')
           ->middleware($perm('manage-services'));
    $router->get('/api/v1/admin/services/{id}',    'Admin\ServiceController@show')
           ->middleware($perm('manage-services'));
    $router->put('/api/v1/admin/services/{id}',    'Admin\ServiceController@update')
           ->middleware($perm('manage-services'));
    $router->delete('/api/v1/admin/services/{id}', 'Admin\ServiceController@destroy')
           ->middleware($perm('manage-services'));

    // Testimonials
    $router->get('/api/v1/admin/testimonials',         'Admin\TestimonialController@index')
           ->middleware($perm('manage-testimonials'));
    $router->post('/api/v1/admin/testimonials',        'Admin\TestimonialController@store')
           ->middleware($perm('manage-testimonials'));
    $router->put('/api/v1/admin/testimonials/{id}',    'Admin\TestimonialController@update')
           ->middleware($perm('manage-testimonials'));
    $router->delete('/api/v1/admin/testimonials/{id}', 'Admin\TestimonialController@destroy')
           ->middleware($perm('manage-testimonials'));

    // Hero slides
    $router->get('/api/v1/admin/hero-slides',         'Admin\HeroSlideController@index')
           ->middleware($perm('manage-pages'));
    $router->post('/api/v1/admin/hero-slides',        'Admin\HeroSlideController@store')
           ->middleware($perm('manage-pages'));
    $router->put('/api/v1/admin/hero-slides/{id}',    'Admin\HeroSlideController@update')
           ->middleware($perm('manage-pages'));
    $router->delete('/api/v1/admin/hero-slides/{id}', 'Admin\HeroSlideController@destroy')
           ->middleware($perm('manage-pages'));

    // Pages
    $router->get('/api/v1/admin/pages',         'Admin\PageController@index')
           ->middleware($perm('manage-pages'));
    $router->post('/api/v1/admin/pages',        'Admin\PageController@store')
           ->middleware($perm('manage-pages'));
    $router->get('/api/v1/admin/pages/{id}',    'Admin\PageController@show')
           ->middleware($perm('manage-pages'));
    $router->put('/api/v1/admin/pages/{id}',    'Admin\PageController@update')
           ->middleware($perm('manage-pages'));
    $router->delete('/api/v1/admin/pages/{id}', 'Admin\PageController@destroy')
           ->middleware($perm('manage-pages'));

    // Users
    $router->get('/api/v1/admin/users',         'Admin\UserController@index')
           ->middleware($perm('manage-users'));
    $router->post('/api/v1/admin/users',        'Admin\UserController@store')
           ->middleware($perm('manage-users'));
    $router->put('/api/v1/admin/users/{id}',    'Admin\UserController@update')
           ->middleware($perm('manage-users'));
    $router->delete('/api/v1/admin/users/{id}', 'Admin\UserController@destroy')
           ->middleware($perm('manage-users'));

    // Slug check utility
    $router->get('/api/v1/admin/slug-check', 'Admin\SlugController@check')
           ->middleware($jwt);

    // Blog post revisions
    $router->get('/api/v1/admin/blog/posts/{id}/revisions',                      'Admin\BlogRevisionController@index')
           ->middleware($perm('manage-blog'));
    $router->get('/api/v1/admin/blog/posts/{id}/revisions/{revisionId}',         'Admin\BlogRevisionController@show')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/posts/{id}/revisions/{revisionId}/restore','Admin\BlogRevisionController@restore')
           ->middleware($perm('manage-blog'));

    // Activity log
    $router->get('/api/v1/admin/activity-logs', 'Admin\ActivityLogController@index')
           ->middleware($jwt);

    // Inquiry CSV export
    $router->get('/api/v1/admin/inquiries/export', 'Admin\InquiryController@export')
           ->middleware($perm('manage-inquiries'));
};
