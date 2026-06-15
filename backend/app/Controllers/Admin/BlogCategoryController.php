<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class BlogCategoryController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT bc.id, bc.name, bc.slug, COUNT(bp.id) AS post_count
             FROM blog_categories bc
             LEFT JOIN blog_posts bp ON bp.category_id=bc.id AND bp.deleted_at IS NULL
             GROUP BY bc.id, bc.name, bc.slug
             ORDER BY bc.name ASC'
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
        if ($db->query('SELECT id FROM blog_categories WHERE slug=?', [$data['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug already exists.']]);
        }

        $db->query('INSERT INTO blog_categories (name, slug, created_at, updated_at) VALUES (?,?,NOW(),NOW())', [$data['name'], $data['slug']]);
        $id  = (int) $db->lastInsertId();
        $row = $db->query('SELECT * FROM blog_categories WHERE id=?', [$id])->fetch();

        return $this->created(['id' => (int)$row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'post_count' => 0]);
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $data = $request->json();
        $err  = $this->validate($data, ['name' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        if (!$db->query('SELECT id FROM blog_categories WHERE id=?', [$id])->fetch()) {
            throw new HttpException(404, 'Category not found.');
        }

        $db->query('UPDATE blog_categories SET name=?, slug=?, updated_at=NOW() WHERE id=?', [$data['name'], $data['slug'], $id]);

        return $this->success(['id' => $id, 'name' => $data['name'], 'slug' => $data['slug']]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        if (!$db->query('SELECT id FROM blog_categories WHERE id=?', [$id])->fetch()) {
            throw new HttpException(404, 'Category not found.');
        }
        // Hard delete — blog_posts FK is ON DELETE SET NULL so posts are unaffected
        $db->query('DELETE FROM blog_categories WHERE id=?', [$id]);
        return $this->noContent();
    }
}
