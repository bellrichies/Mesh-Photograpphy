<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\PermissionMiddleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\MediaUploadController;
use App\Controllers\Admin\BlogCategoryController;
use App\Controllers\Admin\BlogPostController;
use App\Controllers\Admin\BlogTagController;
use App\Controllers\Admin\BookingController;
use App\Controllers\Admin\GalleryCategoryController;
use App\Controllers\Admin\GalleryController;
use App\Controllers\Admin\HeroSlideController;
use App\Controllers\Admin\InquiryController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\TestimonialController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\PageSectionController;
use App\Controllers\Admin\ReusableBlockController;
use App\Controllers\Admin\SettingsController;

return static function (Router $router): void {
    $adminPrefix = '/' . trim((string) config('app.admin_path', '/admin'), '/');

    $router->get($adminPrefix, static function (Request $request, Response $response) use ($adminPrefix): Response {
        if (app_auth()->check()) {
            return $response->redirect($adminPrefix . '/dashboard', 302);
        }

        return $response->redirect($adminPrefix . '/login', 302);
    });

    $router->get($adminPrefix . '/dashboard', static function (Request $request, Response $response): Response {
        $view = new View(dirname(__DIR__));
        $database = app_database();
        $count = static fn (string $sql): int => (int) $database->query($sql)->fetchColumn();

        $metrics = [
            'pages_total' => $count('SELECT COUNT(*) FROM pages WHERE deleted_at IS NULL'),
            'pages_published' => $count('SELECT COUNT(*) FROM pages WHERE deleted_at IS NULL AND status = "published"'),
            'galleries_total' => $count('SELECT COUNT(*) FROM galleries WHERE deleted_at IS NULL'),
            'galleries_published' => $count('SELECT COUNT(*) FROM galleries WHERE deleted_at IS NULL AND status = "published"'),
            'blog_posts_total' => $count('SELECT COUNT(*) FROM blog_posts WHERE deleted_at IS NULL'),
            'blog_posts_published' => $count('SELECT COUNT(*) FROM blog_posts WHERE deleted_at IS NULL AND status = "published"'),
            'media_total' => $count('SELECT COUNT(*) FROM media WHERE deleted_at IS NULL'),
            'services_total' => $count('SELECT COUNT(*) FROM services WHERE deleted_at IS NULL'),
            'services_published' => $count('SELECT COUNT(*) FROM services WHERE deleted_at IS NULL AND status = "published"'),
            'testimonials_total' => $count('SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL'),
            'testimonials_published' => $count('SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL AND status = "published"'),
            'hero_slides_total' => $count('SELECT COUNT(*) FROM hero_slides WHERE deleted_at IS NULL'),
            'hero_slides_published' => $count('SELECT COUNT(*) FROM hero_slides WHERE deleted_at IS NULL AND status = "published"'),
            'inquiries_total' => $count('SELECT COUNT(*) FROM inquiries'),
            'inquiries_new' => $count('SELECT COUNT(*) FROM inquiries WHERE status = "new"'),
            'inquiries_in_progress' => $count('SELECT COUNT(*) FROM inquiries WHERE status = "in_progress"'),
            'bookings_total' => $count('SELECT COUNT(*) FROM booking_requests'),
            'bookings_new' => $count('SELECT COUNT(*) FROM booking_requests WHERE status = "new"'),
            'bookings_quoted' => $count('SELECT COUNT(*) FROM booking_requests WHERE status = "quoted"'),
        ];

        return $response->html(
            $view->render('admin/dashboard', [
                'title' => 'Admin Dashboard',
                'breadcrumbs' => [
                    ['label' => 'Admin', 'href' => '#'],
                    ['label' => 'Dashboard', 'href' => '#'],
                ],
                'metrics' => $metrics,
            ], 'layouts/admin')
        );
    })->middleware(AuthMiddleware::class);

    $router->get($adminPrefix . '/content', static function (Request $request, Response $response): Response {
        $view = new View(dirname(__DIR__));
        return $response->html($view->render('admin/content', [
            'title' => 'Content Workspace',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Content', 'href' => '#'],
            ],
        ], 'layouts/admin'));
    })->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/pages', [PageController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/pages/create', [PageController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/store', [PageController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/pages/edit/{id}', [PageController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/pages/{pageId}/sections', [PageSectionController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/{pageId}/sections/store', [PageSectionController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/{pageId}/sections/update/{sectionId}', [PageSectionController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/{pageId}/sections/delete/{sectionId}', [PageSectionController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/{pageId}/sections/reorder/{sectionId}', [PageSectionController::class, 'reorder'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/{pageId}/sections/status/{sectionId}', [PageSectionController::class, 'toggleStatus'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/update/{id}', [PageController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/pages/delete/{id}', [PageController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/pages/slug', [PageController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/blocks', [ReusableBlockController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/blocks/create', [ReusableBlockController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/blocks/store', [ReusableBlockController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/blocks/edit/{id}', [ReusableBlockController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/blocks/update/{id}', [ReusableBlockController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/blocks/delete/{id}', [ReusableBlockController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/settings', [SettingsController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-settings')]);

    $router->post($adminPrefix . '/settings/update', [SettingsController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-settings')]);

    $router->get($adminPrefix . '/users', static function (Request $request, Response $response): Response {
        $view = new View(dirname(__DIR__));
        return $response->html($view->render('admin/users', [
            'title' => 'User Management',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Users', 'href' => '#'],
            ],
        ], 'layouts/admin'));
    })->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-users')]);

    $router->post($adminPrefix . '/media/upload', [MediaUploadController::class, 'upload'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->post($adminPrefix . '/media/archive', [MediaUploadController::class, 'archive'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->get($adminPrefix . '/media', [MediaController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->get($adminPrefix . '/media/search', [MediaController::class, 'search'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->post($adminPrefix . '/media/update', [MediaController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->post($adminPrefix . '/media/delete', [MediaController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->get($adminPrefix . '/media/picker', [MediaController::class, 'picker'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-media')]);

    $router->get($adminPrefix . '/galleries', [GalleryController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->get($adminPrefix . '/galleries/create', [GalleryController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/store', [GalleryController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->get($adminPrefix . '/galleries/edit/{id}', [GalleryController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/update/{id}', [GalleryController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/delete/{id}', [GalleryController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->get($adminPrefix . '/galleries/slug', [GalleryController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/media/attach', [GalleryController::class, 'attachMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/media/update', [GalleryController::class, 'updateMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/media/reorder', [GalleryController::class, 'reorderMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/galleries/media/remove', [GalleryController::class, 'removeMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->get($adminPrefix . '/gallery-categories', [GalleryCategoryController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/gallery-categories/store', [GalleryCategoryController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/gallery-categories/update/{id}', [GalleryCategoryController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->post($adminPrefix . '/gallery-categories/delete/{id}', [GalleryCategoryController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->get($adminPrefix . '/gallery-categories/slug', [GalleryCategoryController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-galleries')]);

    $router->get($adminPrefix . '/services', [ServiceController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->get($adminPrefix . '/services/create', [ServiceController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->post($adminPrefix . '/services/store', [ServiceController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->get($adminPrefix . '/services/edit/{id}', [ServiceController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->post($adminPrefix . '/services/update/{id}', [ServiceController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->post($adminPrefix . '/services/delete/{id}', [ServiceController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->get($adminPrefix . '/services/slug', [ServiceController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-services')]);

    $router->get($adminPrefix . '/testimonials', [TestimonialController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-testimonials')]);

    $router->get($adminPrefix . '/testimonials/create', [TestimonialController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-testimonials')]);

    $router->post($adminPrefix . '/testimonials/store', [TestimonialController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-testimonials')]);

    $router->get($adminPrefix . '/testimonials/edit/{id}', [TestimonialController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-testimonials')]);

    $router->post($adminPrefix . '/testimonials/update/{id}', [TestimonialController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-testimonials')]);

    $router->post($adminPrefix . '/testimonials/delete/{id}', [TestimonialController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-testimonials')]);

    $router->get($adminPrefix . '/hero-slides', [HeroSlideController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/hero-slides/create', [HeroSlideController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/hero-slides/store', [HeroSlideController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/hero-slides/edit/{id}', [HeroSlideController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/hero-slides/update/{id}', [HeroSlideController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->post($adminPrefix . '/hero-slides/delete/{id}', [HeroSlideController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-pages')]);

    $router->get($adminPrefix . '/inquiries', [InquiryController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->get($adminPrefix . '/inquiries/filter', [InquiryController::class, 'filter'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->get($adminPrefix . '/inquiries/view/{id}', [InquiryController::class, 'show'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->post($adminPrefix . '/inquiries/status/{id}', [InquiryController::class, 'updateStatus'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->post($adminPrefix . '/inquiries/notes/{id}', [InquiryController::class, 'addNote'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->get($adminPrefix . '/bookings', [BookingController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->get($adminPrefix . '/bookings/view/{id}', [BookingController::class, 'show'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->post($adminPrefix . '/bookings/status/{id}', [BookingController::class, 'updateStatus'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-inquiries')]);

    $router->get($adminPrefix . '/blog/posts', [BlogPostController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/posts/create', [BlogPostController::class, 'create'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/store', [BlogPostController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/posts/edit/{id}', [BlogPostController::class, 'edit'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/update/{id}', [BlogPostController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/delete/{id}', [BlogPostController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/posts/slug', [BlogPostController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/media/attach', [BlogPostController::class, 'attachMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/media/update', [BlogPostController::class, 'updateMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/media/reorder', [BlogPostController::class, 'reorderMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/media/remove', [BlogPostController::class, 'removeMedia'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/posts/autosave/{id}', [BlogPostController::class, 'autosave'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/categories', [BlogCategoryController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/categories/store', [BlogCategoryController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/categories/update/{id}', [BlogCategoryController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/categories/delete/{id}', [BlogCategoryController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/categories/slug', [BlogCategoryController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/tags', [BlogTagController::class, 'index'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/tags/store', [BlogTagController::class, 'store'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/tags/update/{id}', [BlogTagController::class, 'update'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->post($adminPrefix . '/blog/tags/delete/{id}', [BlogTagController::class, 'delete'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);

    $router->get($adminPrefix . '/blog/tags/slug', [BlogTagController::class, 'slug'])
        ->middleware([AuthMiddleware::class, PermissionMiddleware::for('manage-blog')]);
};
