<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\MediaFormatter;

class MediaController extends Controller
{
    private MediaFormatter $fmt;

    public function __construct()
    {
        $this->fmt = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $db      = app_database();
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 24)));
        $offset  = ($page - 1) * $perPage;

        $where  = ['deleted_at IS NULL'];
        $params = [];

        // Filter by MIME type category (image, document, video) using mime_type prefix
        if ($type = $request->query('type')) {
            $where[]  = 'mime_type LIKE ?';
            $params[] = $type . '/%';
        }

        if ($q = $request->query('q')) {
            $where[]  = '(original_name LIKE ? OR alt_text LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $whereStr = implode(' AND ', $where);
        $total    = (int) $db->query("SELECT COUNT(*) AS cnt FROM media WHERE {$whereStr}", $params)->fetch()['cnt'];
        $rows     = $db->query(
            "SELECT * FROM media WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        )->fetchAll();

        $items = array_map([$this, 'formatMedia'], $rows);
        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }

    public function upload(Request $request, Response $response): Response
    {
        $file = $request->file('file');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->error('No file uploaded or upload error.', 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $mime         = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowedMimes, true)) {
            return $this->error('File type not allowed.', 422);
        }

        $maxSize = 10 * 1024 * 1024; // 10 MB
        if ($file['size'] > $maxSize) {
            return $this->error('File exceeds maximum size of 10MB.', 422);
        }

        // Verify it is a real image
        if (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {
            if (!@getimagesize($file['tmp_name'])) {
                return $this->error('Uploaded file is not a valid image.', 422);
            }
        }

        $uuid     = bin2hex(random_bytes(16));
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $year     = date('Y');
        $month    = date('m');
        $dir      = dirname(__DIR__, 3) . "/public/uploads/{$year}/{$month}";
        $fileName = "{$uuid}.{$ext}";
        $path     = "{$year}/{$month}/{$fileName}";

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], "{$dir}/{$fileName}")) {
            return $this->error('Failed to save uploaded file.', 500);
        }

        $isImage  = str_starts_with($mime, 'image/');
        $fullPath = "{$dir}/{$fileName}";
        [$width, $height] = $isImage ? (@getimagesize($fullPath) ?: [null, null]) : [null, null];

        // WebP conversion and responsive variants for raster images
        $variants = [];
        if ($isImage && $mime !== 'image/svg+xml' && function_exists('imagecreatefromstring')) {
            $variants = $this->generateVariants($fullPath, $dir, $uuid, $mime);
        }

        $db = app_database();
        $db->query(
            'INSERT INTO media (uuid, path, file_name, original_name, mime_type, file_size, width, height, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [$uuid, $path, $fileName, $file['name'], $mime, $file['size'], $width, $height]
        );

        $id  = (int) $db->lastInsertId();
        $row = $db->query('SELECT * FROM media WHERE id=?', [$id])->fetch();

        $formatted           = $this->formatMedia($row);
        $formatted['srcset'] = $this->buildSrcset($path, $variants);

        return $this->created($formatted);
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $row  = $db->query('SELECT * FROM media WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Media not found.');

        $data = $request->json();
        $db->query(
            'UPDATE media SET alt_text=?, updated_at=NOW() WHERE id=?',
            [$data['alt_text'] ?? null, $id]
        );

        $row = $db->query('SELECT * FROM media WHERE id=?', [$id])->fetch();
        return $this->success($this->formatMedia($row));
    }

    public function archive(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        $db->query('UPDATE media SET deleted_at=NOW() WHERE id=? AND deleted_at IS NULL', [$id]);
        return $this->success(null, 'Archived.');
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $row = $db->query('SELECT * FROM media WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Media not found.');

        $db->query('UPDATE media SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function formatMedia(array $row): array
    {
        return $this->fmt->formatMedia(array_merge($row, ['sort_order' => 0]));
    }

    /**
     * Generate WebP variants at 320, 640, and 1280 px widths.
     *
     * @return array<int, string>
     */
    private function generateVariants(string $sourcePath, string $dir, string $uuid, string $mime): array
    {
        $imageData = @file_get_contents($sourcePath);
        if ($imageData === false) return [];

        $src = @imagecreatefromstring($imageData);
        if (!$src) return [];

        $origW = imagesx($src);
        $origH = imagesy($src);

        $variants = [];
        $widths   = [320, 640, 1280];

        foreach ($widths as $w) {
            if ($origW <= $w) continue;

            $h   = (int) round(($w / $origW) * $origH);
            $dst = imagecreatetruecolor($w, $h);

            if ($mime === 'image/png') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $origW, $origH);

            $fileName = "{$uuid}_{$w}.webp";
            $filePath = "{$dir}/{$fileName}";

            if (@imagewebp($dst, $filePath, 85)) {
                $variants[$w] = $fileName;
            }

            imagedestroy($dst);
        }

        imagedestroy($src);

        if ($mime !== 'image/webp') {
            $full = @imagecreatefromstring($imageData);
            if ($full) {
                $webpFile = "{$uuid}.webp";
                @imagewebp($full, "{$dir}/{$webpFile}", 88);
                imagedestroy($full);
                $variants[0] = $webpFile;
            }
        }

        return $variants;
    }

    /**
     * @param array<int, string> $variants
     */
    private function buildSrcset(string $basePath, array $variants): ?string
    {
        if (empty($variants)) return null;

        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/') . '/uploads/';
        $dir     = dirname($basePath);

        $entries = [];
        foreach ($variants as $w => $file) {
            if ($w === 0) continue;
            $entries[] = $baseUrl . $dir . '/' . $file . " {$w}w";
        }

        return $entries ? implode(', ', $entries) : null;
    }
}
