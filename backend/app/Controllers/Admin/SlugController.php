<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class SlugController extends Controller
{
    private const TABLES = [
        'gallery'     => 'galleries',
        'blog_post'   => 'blog_posts',
        'service'     => 'services',
        'page'        => 'pages',
    ];

    public function check(Request $request, Response $response): Response
    {
        $slug   = trim($request->query('slug', ''));
        $type   = $request->query('type', 'gallery');
        $except = (int) $request->query('except', 0);

        if (!$slug) {
            return $this->error('slug is required.', 400);
        }

        $table = self::TABLES[$type] ?? null;
        if (!$table) {
            return $this->error("Unknown type '{$type}'.", 400);
        }

        $db  = app_database();
        $sql = "SELECT id FROM {$table} WHERE slug=? AND deleted_at IS NULL";
        $p   = [$slug];

        if ($except > 0) {
            $sql .= ' AND id != ?';
            $p[]  = $except;
        }

        $exists = (bool) $db->query($sql, $p)->fetch();

        return $this->success(['slug' => $slug, 'available' => !$exists]);
    }
}
