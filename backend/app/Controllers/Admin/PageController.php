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
            'SELECT id, title, slug, template, is_published, created_at, updated_at
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

        $sections = $db->query(
            'SELECT id, section_type, title, content, settings, sort_order FROM page_sections
             WHERE page_id=? ORDER BY sort_order ASC',
            [$id]
        )->fetchAll();

        $data = $this->formatRow($row);
        $data['sections'] = array_map(fn($s) => [
            'id'           => (int)$s['id'],
            'section_type' => $s['section_type'],
            'title'        => $s['title'] ?? null,
            'content'      => $s['content'] ?? null,
            'settings'     => $s['settings'] ? json_decode($s['settings'], true) : [],
            'sort_order'   => (int)$s['sort_order'],
        ], $sections);
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
            'INSERT INTO pages (title, slug, template, is_published, seo_title, seo_description, created_at, updated_at)
             VALUES (?,?,?,?,?,?,NOW(),NOW())',
            [
                $data['title'],
                $data['slug'],
                $data['template']        ?? null,
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
            'UPDATE pages SET title=?,slug=?,template=?,is_published=?,seo_title=?,seo_description=?,updated_at=NOW() WHERE id=?',
            [
                $data['title'],
                $data['slug'],
                $data['template']        ?? null,
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
            'id'           => (int)$row['id'],
            'title'        => $row['title'],
            'slug'         => $row['slug'],
            'template'     => $row['template']     ?? null,
            'status'       => $row['is_published'] ? 'published' : 'draft',
            'created_at'   => $row['created_at'],
            'updated_at'   => $row['updated_at'],
        ];
    }
}
