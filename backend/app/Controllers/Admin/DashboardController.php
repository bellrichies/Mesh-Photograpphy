<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $db = app_database();

        $galleries    = $db->query('SELECT COUNT(*) AS total, SUM(is_published) AS published FROM galleries WHERE deleted_at IS NULL')->fetch();
        $posts        = $db->query('SELECT COUNT(*) AS total, SUM(is_published) AS published FROM blog_posts WHERE deleted_at IS NULL')->fetch();
        $media        = $db->query('SELECT COUNT(*) AS total FROM media WHERE deleted_at IS NULL')->fetch();
        $services     = $db->query('SELECT COUNT(*) AS total, SUM(is_published) AS published FROM services WHERE deleted_at IS NULL')->fetch();
        $testimonials = $db->query('SELECT COUNT(*) AS total, SUM(is_published) AS published FROM testimonials WHERE deleted_at IS NULL')->fetch();
        $heroSlides   = $db->query('SELECT COUNT(*) AS total, SUM(is_published) AS published FROM hero_slides WHERE deleted_at IS NULL')->fetch();
        $pages        = $db->query('SELECT COUNT(*) AS total, SUM(is_published) AS published FROM pages WHERE deleted_at IS NULL')->fetch();
        // inquiries has no status column — use is_read/replied_at instead
        $inquiries    = $db->query('SELECT COUNT(*) AS total, SUM(is_read = 0) AS new_count, SUM(replied_at IS NOT NULL) AS in_progress FROM inquiries WHERE deleted_at IS NULL')->fetch();
        // booking_requests status enum: new, contacted, booked, declined, cancelled
        $bookings     = $db->query('SELECT COUNT(*) AS total, SUM(status = \'new\') AS new_count, SUM(status = \'contacted\') AS quoted FROM booking_requests WHERE deleted_at IS NULL')->fetch();

        return $this->success([
            'pages'        => ['total' => (int)($pages['total'] ?? 0),        'published' => (int)($pages['published'] ?? 0)],
            'galleries'    => ['total' => (int)($galleries['total'] ?? 0),     'published' => (int)($galleries['published'] ?? 0)],
            'blog_posts'   => ['total' => (int)($posts['total'] ?? 0),         'published' => (int)($posts['published'] ?? 0)],
            'media'        => ['total' => (int)($media['total'] ?? 0)],
            'services'     => ['total' => (int)($services['total'] ?? 0),      'published' => (int)($services['published'] ?? 0)],
            'testimonials' => ['total' => (int)($testimonials['total'] ?? 0),  'published' => (int)($testimonials['published'] ?? 0)],
            'hero_slides'  => ['total' => (int)($heroSlides['total'] ?? 0),    'published' => (int)($heroSlides['published'] ?? 0)],
            'inquiries'    => ['total' => (int)($inquiries['total'] ?? 0),     'new' => (int)($inquiries['new_count'] ?? 0), 'in_progress' => (int)($inquiries['in_progress'] ?? 0)],
            'bookings'     => ['total' => (int)($bookings['total'] ?? 0),      'new' => (int)($bookings['new_count'] ?? 0), 'quoted' => (int)($bookings['quoted'] ?? 0)],
        ]);
    }
}
