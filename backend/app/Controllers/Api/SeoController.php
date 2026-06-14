<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Gallery;
use App\Models\BlogPost;
use App\Models\Service;
use App\Models\Page;

class SeoController extends Controller
{
    public function sitemap(Request $request, Response $response): Response
    {
        $db       = app_database();
        $appUrl   = rtrim($_ENV['APP_URL'] ?? '', '/');

        $galleries = $db->query(
            'SELECT slug, updated_at FROM galleries WHERE deleted_at IS NULL AND is_published = 1'
        )->fetchAll();

        $posts = $db->query(
            'SELECT slug, updated_at FROM blog_posts WHERE deleted_at IS NULL AND is_published = 1'
        )->fetchAll();

        $services = $db->query(
            'SELECT slug, updated_at FROM services WHERE deleted_at IS NULL AND is_published = 1'
        )->fetchAll();

        $pages = $db->query(
            'SELECT slug, updated_at FROM pages WHERE deleted_at IS NULL AND is_published = 1'
        )->fetchAll();

        $urls = [
            ['loc' => $appUrl . '/',           'changefreq' => 'weekly',  'priority' => '1.0'],
            ['loc' => $appUrl . '/portfolio',  'changefreq' => 'weekly',  'priority' => '0.9'],
            ['loc' => $appUrl . '/services',   'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $appUrl . '/blog',       'changefreq' => 'daily',   'priority' => '0.8'],
            ['loc' => $appUrl . '/contact',    'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $appUrl . '/booking',    'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $appUrl . '/about',      'changefreq' => 'monthly', 'priority' => '0.6'],
        ];

        foreach ($galleries as $g) {
            $urls[] = [
                'loc'        => $appUrl . '/portfolio/' . $g['slug'],
                'lastmod'    => date('Y-m-d', strtotime($g['updated_at'])),
                'changefreq' => 'monthly',
                'priority'   => '0.8',
            ];
        }

        foreach ($posts as $p) {
            $urls[] = [
                'loc'        => $appUrl . '/blog/' . $p['slug'],
                'lastmod'    => date('Y-m-d', strtotime($p['updated_at'])),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        foreach ($services as $s) {
            $urls[] = [
                'loc'        => $appUrl . '/services/' . $s['slug'],
                'lastmod'    => date('Y-m-d', strtotime($s['updated_at'])),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        foreach ($pages as $pg) {
            $urls[] = [
                'loc'        => $appUrl . '/' . $pg['slug'],
                'lastmod'    => date('Y-m-d', strtotime($pg['updated_at'])),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ];
        }

        return $this->success(['urls' => $urls]);
    }
}
