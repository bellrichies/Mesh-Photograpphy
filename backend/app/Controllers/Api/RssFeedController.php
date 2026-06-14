<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class RssFeedController extends Controller
{
    public function feed(Request $request, Response $response): Response
    {
        $db       = app_database();
        $appUrl   = rtrim($_ENV['APP_URL'] ?? '', '/');
        $siteName = $_ENV['APP_NAME'] ?? 'Mesh Photography';

        $rows = $db->query(
            'SELECT bp.title, bp.slug, bp.excerpt, bp.published_at,
                    u.first_name, u.last_name
             FROM blog_posts bp
             LEFT JOIN users u ON bp.author_id = u.id
             WHERE bp.deleted_at IS NULL
               AND (bp.is_published = 1 OR (bp.published_at IS NOT NULL AND bp.published_at <= NOW()))
             ORDER BY bp.published_at DESC
             LIMIT 50'
        )->fetchAll();

        $items = '';
        foreach ($rows as $row) {
            $title    = htmlspecialchars($row['title'], ENT_XML1);
            $link     = $appUrl . '/blog/' . htmlspecialchars($row['slug'], ENT_XML1);
            $pubDate  = $row['published_at']
                ? date(DATE_RSS, strtotime($row['published_at']))
                : '';
            $desc     = htmlspecialchars($row['excerpt'] ?? '', ENT_XML1);
            $author   = ($row['first_name'] ?? '')
                ? htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name']), ENT_XML1)
                : '';

            $items .= "<item>\n"
                . "  <title>{$title}</title>\n"
                . "  <link>{$link}</link>\n"
                . "  <guid isPermaLink=\"true\">{$link}</guid>\n"
                . ($pubDate ? "  <pubDate>{$pubDate}</pubDate>\n" : '')
                . ($desc    ? "  <description>{$desc}</description>\n" : '')
                . ($author  ? "  <author>{$author}</author>\n" : '')
                . "</item>\n";
        }

        $feedUrl  = $appUrl . '/api/v1/blog/feed.xml';
        $blogUrl  = $appUrl . '/blog';
        $now      = date(DATE_RSS);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n"
            . "<channel>\n"
            . "  <title>" . htmlspecialchars($siteName, ENT_XML1) . " Journal</title>\n"
            . "  <link>{$blogUrl}</link>\n"
            . "  <atom:link href=\"{$feedUrl}\" rel=\"self\" type=\"application/rss+xml\" />\n"
            . "  <description>Latest journal posts from " . htmlspecialchars($siteName, ENT_XML1) . "</description>\n"
            . "  <language>en-us</language>\n"
            . "  <lastBuildDate>{$now}</lastBuildDate>\n"
            . $items
            . "</channel>\n"
            . "</rss>\n";

        if (!headers_sent()) {
            header('Content-Type: application/rss+xml; charset=UTF-8');
            header('Cache-Control: public, max-age=3600');
        }

        echo $xml;
        exit;
    }
}
