<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\MediaUsageMap;
use App\Repositories\MediaRepository;
use RuntimeException;

class MediaController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('admin/media/index', [
            'title' => 'Media Library',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Media Library', 'href' => '#'],
            ],
            'adminPath' => admin_url(),
        ], 'layouts/admin'));
    }

    public function search(Request $request, Response $response): Response
    {
        $query = trim((string) $request->query('q', ''));
        $fileType = trim((string) $request->query('type', ''));
        $sort = trim((string) $request->query('sort', 'newest'));
        $page = max(1, (int) $request->query('page', 1));
        $limit = min(60, max(6, (int) $request->query('limit', 18)));
        $offset = ($page - 1) * $limit;

        $repo = new MediaRepository(app_database());
        $rows = $repo->search($query, $fileType !== '' ? $fileType : null, $sort, $limit, $offset);
        $total = $repo->count($query, $fileType !== '' ? $fileType : null);

        $usageMap = new MediaUsageMap(app_database());
        $items = [];
        foreach ($rows as $row) {
            $mediaId = (int) ($row['id'] ?? 0);
            $usageRows = $usageMap->forMedia($mediaId);

            $items[] = [
                'id' => $mediaId,
                'uuid' => (string) ($row['uuid'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'original_name' => (string) ($row['original_name'] ?? ''),
                'stored_name' => (string) ($row['stored_name'] ?? ''),
                'directory' => (string) ($row['directory'] ?? ''),
                'mime_type' => (string) ($row['mime_type'] ?? ''),
                'file_type' => (string) ($row['file_type'] ?? ''),
                'size_bytes' => (int) ($row['size_bytes'] ?? 0),
                'width' => isset($row['width']) ? (int) $row['width'] : null,
                'height' => isset($row['height']) ? (int) $row['height'] : null,
                'alt_text' => (string) ($row['alt_text'] ?? ''),
                'caption' => (string) ($row['caption'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'usage_count' => count($usageRows),
                'usage_preview' => array_slice($usageRows, 0, 3),
                'url' => app_media_url((string) ($row['directory'] ?? ''), (string) ($row['stored_name'] ?? '')),
            ];
        }

        return $this->jsonSuccess($response, 'Media search results loaded.', [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / max(1, $limit))),
            ],
        ]);
    }

    public function update(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);

            $mediaId = (int) $request->post('media_id', 0);
            if ($mediaId <= 0) {
                throw new RuntimeException('Invalid media identifier.');
            }

            $repo = new MediaRepository(app_database());
            $existing = $repo->findById($mediaId);
            if (! is_array($existing)) {
                throw new RuntimeException('Media record not found.');
            }

            $repo->updateMetadata($mediaId, [
                'title' => trim((string) $request->post('title', '')),
                'alt_text' => trim((string) $request->post('alt_text', '')),
                'caption' => trim((string) $request->post('caption', '')),
                'description' => trim((string) $request->post('description', '')),
            ]);

            app_security_logger()->log('media.updated', $request, 'media', $mediaId, 'Updated media metadata.', [
                'stored_name' => (string) ($existing['stored_name'] ?? ''),
                'original_name' => (string) ($existing['original_name'] ?? ''),
            ]);

            return $this->jsonSuccess($response, 'Media metadata updated successfully.');
        } catch (RuntimeException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function delete(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);

            $mediaId = (int) $request->post('media_id', 0);
            if ($mediaId <= 0) {
                throw new RuntimeException('Invalid media identifier.');
            }

            $repo = new MediaRepository(app_database());
            $existing = $repo->findById($mediaId);
            $repo->softDelete($mediaId);

            app_security_logger()->log('media.deleted', $request, 'media', $mediaId, 'Archived media item.', [
                'stored_name' => is_array($existing) ? (string) ($existing['stored_name'] ?? '') : '',
                'original_name' => is_array($existing) ? (string) ($existing['original_name'] ?? '') : '',
            ]);

            return $this->jsonSuccess($response, 'Media deleted successfully.');
        } catch (RuntimeException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function picker(Request $request, Response $response): Response
    {
        $query = trim((string) $request->query('q', ''));
        $fileType = trim((string) $request->query('type', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = min(24, max(6, (int) $request->query('limit', 12)));
        $offset = ($page - 1) * $limit;

        $repo = new MediaRepository(app_database());
        $rows = $repo->search($query, $fileType !== '' ? $fileType : null, 'newest', $limit, $offset);
        $total = $repo->count($query, $fileType !== '' ? $fileType : null);

        $view = new View(dirname(__DIR__, 3));
        $html = $view->partial('components/media-picker-results', ['items' => $rows]);

        return $this->jsonSuccess($response, 'Media picker results loaded.', [
            'html' => $html,
            'count' => count($rows),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / max(1, $limit))),
            ],
        ]);
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token.');
        }
    }
}
