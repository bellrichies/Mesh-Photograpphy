<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class PageController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT id, title, slug, is_published, created_at, updated_at
             FROM pages WHERE deleted_at IS NULL ORDER BY title ASC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function show(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $row = $db->query('SELECT * FROM pages WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Page not found.');

        $data = $this->formatRow($row);
        $data['sections'] = [
            [
                'id'           => 1,
                'section_type' => 'rich_text',
                'title'        => null,
                'content'      => $row['body'] ?? null,
                'settings'     => (object) [],
                'sort_order'   => 0,
            ],
        ];
        $data['seo_title']       = $row['seo_title']       ?? null;
        $data['seo_description'] = $row['seo_description'] ?? null;

        return $this->success($data);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, ['title' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        $db = app_database();
        if ($db->query('SELECT id FROM pages WHERE slug=? AND deleted_at IS NULL', [$data['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug already in use.']]);
        }

        $db->query(
            'INSERT INTO pages (title, slug, body, is_published, seo_title, seo_description, created_at, updated_at)
             VALUES (?,?,?,?,?,?,NOW(),NOW())',
            [
                $data['title'],
                $data['slug'],
                $data['body'] ?? null,
                !empty($data['is_published']) ? 1 : 0,
                $data['seo_title']       ?? null,
                $data['seo_description'] ?? null,
            ]
        );

        $id  = (int) $db->lastInsertId();
        $row = $db->query('SELECT * FROM pages WHERE id=?', [$id])->fetch();
        return $this->created($this->formatRow($row));
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $data = $request->json();
        $err  = $this->validate($data, ['title' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
        if ($err) return $err;

        if (!$db->query('SELECT id FROM pages WHERE id=? AND deleted_at IS NULL', [$id])->fetch()) {
            throw new HttpException(404, 'Page not found.');
        }

        if ($db->query('SELECT id FROM pages WHERE slug=? AND id!=? AND deleted_at IS NULL', [$data['slug'], $id])->fetch()) {
            return $this->validationError(['slug' => ['Slug already in use.']]);
        }

        $db->query(
            'UPDATE pages SET title=?,slug=?,body=?,is_published=?,seo_title=?,seo_description=?,updated_at=NOW() WHERE id=?',
            [
                $data['title'],
                $data['slug'],
                $data['body'] ?? (!empty($data['sections'][0]['content']) ? $data['sections'][0]['content'] : null),
                !empty($data['is_published']) ? 1 : 0,
                $data['seo_title']       ?? null,
                $data['seo_description'] ?? null,
                $id,
            ]
        );

        $row = $db->query('SELECT * FROM pages WHERE id=?', [$id])->fetch();
        return $this->success($this->formatRow($row));
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        if (!$db->query('SELECT id FROM pages WHERE id=? AND deleted_at IS NULL', [$id])->fetch()) {
            throw new HttpException(404, 'Page not found.');
        }
        $db->query('UPDATE pages SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function formatRow(array $row): array
    {
        return [
            'id'         => (int)$row['id'],
            'title'      => $row['title'],
            'slug'       => $row['slug'],
            'template'   => null,
            'status'     => $row['is_published'] ? 'published' : 'draft',
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }
}
