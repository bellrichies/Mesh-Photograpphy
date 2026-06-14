<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class BlogRevisionController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $db     = app_database();
        $postId = (int) $request->param('id');

        $this->requirePost($db, $postId);

        $rows = $db->query(
            'SELECT r.id, r.title, r.created_at,
                    u.first_name, u.last_name
             FROM blog_post_revisions r
             LEFT JOIN users u ON r.saved_by = u.id
             WHERE r.post_id = ?
             ORDER BY r.created_at DESC
             LIMIT 50',
            [$postId]
        )->fetchAll();

        $items = array_map(fn(array $r) => [
            'id'         => (int) $r['id'],
            'title'      => $r['title'],
            'saved_by'   => ($r['first_name'] ?? null)
                ? trim($r['first_name'] . ' ' . $r['last_name'])
                : null,
            'created_at' => $r['created_at'],
        ], $rows);

        return $this->success($items);
    }

    public function show(Request $request, Response $response): Response
    {
        $db         = app_database();
        $postId     = (int) $request->param('id');
        $revisionId = (int) $request->param('revisionId');

        $this->requirePost($db, $postId);

        $row = $db->query(
            'SELECT * FROM blog_post_revisions WHERE id = ? AND post_id = ? LIMIT 1',
            [$revisionId, $postId]
        )->fetch();

        if (!$row) {
            throw new HttpException(404, 'Revision not found.');
        }

        return $this->success([
            'id'         => (int) $row['id'],
            'title'      => $row['title'],
            'body'       => $row['body'] ?? '',
            'created_at' => $row['created_at'],
        ]);
    }

    public function restore(Request $request, Response $response): Response
    {
        $db         = app_database();
        $postId     = (int) $request->param('id');
        $revisionId = (int) $request->param('revisionId');

        $this->requirePost($db, $postId);

        $revision = $db->query(
            'SELECT * FROM blog_post_revisions WHERE id = ? AND post_id = ? LIMIT 1',
            [$revisionId, $postId]
        )->fetch();

        if (!$revision) {
            throw new HttpException(404, 'Revision not found.');
        }

        $payload = $request->authPayload();
        $userId  = $payload['user_id'] ?? $payload['sub'] ?? null;

        // Save current state as a new revision before restoring
        $current = $db->query(
            'SELECT title, body FROM blog_posts WHERE id = ? LIMIT 1',
            [$postId]
        )->fetch();

        if ($current) {
            $db->query(
                'INSERT INTO blog_post_revisions (post_id, title, body, saved_by, created_at) VALUES (?,?,?,?,NOW())',
                [$postId, $current['title'], $current['body'], $userId]
            );
        }

        $db->query(
            'UPDATE blog_posts SET title = ?, body = ?, updated_at = NOW() WHERE id = ?',
            [$revision['title'], $revision['body'], $postId]
        );

        return $this->success(null, 'Revision restored.');
    }

    private function requirePost(\App\Core\Database $db, int $postId): void
    {
        $row = $db->query(
            'SELECT id FROM blog_posts WHERE id = ? AND deleted_at IS NULL LIMIT 1',
            [$postId]
        )->fetch();

        if (!$row) {
            throw new HttpException(404, 'Blog post not found.');
        }
    }
}
