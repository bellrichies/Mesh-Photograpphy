<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Models\Page;
use App\Models\Service;
use App\Repositories\BlogRepository;
use App\Repositories\GalleryRepository;

class SeoController
{
    public function sitemap(Request $request, Response $response): Response
    {
        $urls = [];
        $seen = [];
        $now = date('c');

        $append = static function (array &$urls, array &$seen, array $item): void {
            $loc = (string) ($item['loc'] ?? '');
            if ($loc === '' || isset($seen[$loc])) {
                return;
            }

            $seen[$loc] = true;
            $urls[] = $item;
        };

        $append($urls, $seen, ['loc' => base_url(), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0']);
        $append($urls, $seen, ['loc' => base_url('portfolio'), 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.9']);
        $append($urls, $seen, ['loc' => base_url('services'), 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.9']);
        $append($urls, $seen, ['loc' => base_url('testimonials'), 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.6']);
        $append($urls, $seen, ['loc' => base_url('contact'), 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.7']);
        $append($urls, $seen, ['loc' => base_url('booking'), 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.7']);
        $append($urls, $seen, ['loc' => base_url('blog'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.9']);

        foreach ((new Page(app_database()))->allPublished() as $page) {
            $slug = trim((string) ($page['slug'] ?? ''));
            if ($slug === '' || $slug === 'home') {
                continue;
            }

            $append($urls, $seen, [
                'loc' => base_url($slug),
                'lastmod' => $this->xmlDate((string) ($page['updated_at'] ?? '')),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);
        }

        foreach ((new GalleryRepository(app_database()))->sitemapItems() as $gallery) {
            $append($urls, $seen, [
                'loc' => base_url('portfolio/' . (string) ($gallery['slug'] ?? '')),
                'lastmod' => $this->xmlDate((string) (($gallery['updated_at'] ?? '') !== '' ? ($gallery['updated_at'] ?? '') : ($gallery['published_at'] ?? ''))),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ]);
        }

        foreach ((new Service(app_database()))->allPublished(500) as $service) {
            $slug = trim((string) ($service['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $append($urls, $seen, [
                'loc' => base_url('services/' . $slug),
                'lastmod' => $this->xmlDate((string) ($service['updated_at'] ?? '')),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ]);
        }

        $blogRepository = new BlogRepository(app_database());
        foreach ($blogRepository->sitemapPosts() as $post) {
            $append($urls, $seen, [
                'loc' => base_url('blog/' . (string) ($post['slug'] ?? '')),
                'lastmod' => $this->xmlDate((string) (($post['updated_at'] ?? '') !== '' ? ($post['updated_at'] ?? '') : ($post['published_at'] ?? ''))),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ]);
        }

        foreach ($blogRepository->categoryCounts() as $category) {
            if ((int) ($category['post_count'] ?? 0) <= 0) {
                continue;
            }

            $append($urls, $seen, [
                'loc' => base_url('blog/category/' . (string) ($category['slug'] ?? '')),
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]);
        }

        foreach ($blogRepository->tagCounts() as $tag) {
            if ((int) ($tag['post_count'] ?? 0) <= 0) {
                continue;
            }

            $append($urls, $seen, [
                'loc' => base_url('blog/tag/' . (string) ($tag['slug'] ?? '')),
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ]);
        }

        $body = $this->renderSitemap($urls);

        return $response->raw($body, 'application/xml; charset=utf-8');
    }

    public function robots(Request $request, Response $response): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /storage',
            'Disallow: /vendor',
            'Disallow: /bootstrap',
            'Disallow: /database',
            'Disallow: /cli',
            '',
            'Sitemap: ' . base_url('sitemap.xml'),
        ];

        return $response->raw(implode("\n", $lines) . "\n", 'text/plain; charset=utf-8');
    }

    /**
     * @param array<int, array<string, string>> $urls
     */
    private function renderSitemap(array $urls): string
    {
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($urls as $url) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars((string) ($url['loc'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xml[] = '    <lastmod>' . htmlspecialchars((string) ($url['lastmod'] ?? date('c')), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</lastmod>';
            $xml[] = '    <changefreq>' . htmlspecialchars((string) ($url['changefreq'] ?? 'weekly'), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</changefreq>';
            $xml[] = '    <priority>' . htmlspecialchars((string) ($url['priority'] ?? '0.5'), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml);
    }

    private function xmlDate(string $value): string
    {
        $timestamp = strtotime($value);
        return $timestamp === false ? date('c') : date('c', $timestamp);
    }
}