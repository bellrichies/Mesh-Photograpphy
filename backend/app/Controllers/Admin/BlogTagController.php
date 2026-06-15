<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class BlogTagController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT bt.id, bt.name, bt.slug, COUNT(bpt.post_id) AS post_count
             FROM blog_tags bt
             LEFT JOIN blog_post_tags bpt ON bt.id=bpt.tag_id
             GROUP BY bt.id, bt.name, bt.slug
             ORDER BY bt.name ASC'
        )->fetchAll();

        return $this->success(array_map(fn($r) => [
            'id'         => (int)$r['id'],
            'name'       => $r['name'],
            'slug'       => $r['slug'],
            'post_count' => (int)$r['post_count'],
        ], $rows));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, ['name' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        $db = app_database();
        if ($db->query('SELECT id FROM blog_tags WHERE slug=?', [$data['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug already exists.']]);
        }

        $db->query('INSERT INTO blog_tags (name, slug, created_at) VALUES (?,?,NOW())', [$data['name'], $data['slug']]);
        $id  = (int) $db->lastInsertId();
        $row = $db->query('SELECT * FROM blog_tags WHERE id=?', [$id])->fetch();

        return $this->created(['id' => (int)$row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'post_count' => 0]);
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $data = $request->json();
        $err  = $this->validate($data, ['name' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        if (!$db->query('SELECT id FROM blog_tags WHERE id=?', [$id])->fetch()) {
            throw new HttpException(404, 'Tag not found.');
        }

        $db->query('UPDATE blog_tags SET name=?, slug=? WHERE id=?', [$data['name'], $data['slug'], $id]);

        return $this->success(['id' => $id, 'name' => $data['name'], 'slug' => $data['slug']]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        if (!$db->query('SELECT id FROM blog_tags WHERE id=?', [$id])->fetch()) {
            throw new HttpException(404, 'Tag not found.');
        }
        // Hard delete — blog_post_tags FK is ON DELETE CASCADE so pivot rows are removed
        $db->query('DELETE FROM blog_tags WHERE id=?', [$id]);
        return $this->noContent();
    }
}
