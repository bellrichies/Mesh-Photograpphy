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

        $originalName = basename(str_replace('\\', '/', (string) $file['name']));
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($ext === '') {
            return $this->error('Uploaded file must have an extension.', 422);
        }

        $blockedExtensions = config('uploads.blocked_extensions', []);
        if (in_array($ext, $blockedExtensions, true)) {
            return $this->error('File type not allowed.', 422);
        }

        $mime = $this->detectMimeType($file['tmp_name']);
        if (!$mime) {
            return $this->error('Could not determine uploaded file type.', 422);
        }

        $fileType = $this->resolveAllowedFileType($mime, $ext);
        if ($fileType === null) {
            return $this->error('File type not allowed.', 422);
        }

        $maxFileSizeMb = max(1, (int) config('uploads.max_file_size_mb', 10));
        $maxSize       = $maxFileSizeMb * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return $this->error("File exceeds maximum size of {$maxFileSizeMb}MB.", 422);
        }

        $width  = null;
        $height = null;

        if ($fileType === 'image') {
            $imageSize = @getimagesize($file['tmp_name']);
            if ($imageSize === false) {
                return $this->error('Uploaded file is not a valid image.', 422);
            }

            $width          = (int) $imageSize[0];
            $height         = (int) $imageSize[1];
            $maxImageWidth  = (int) config('uploads.max_image_width', 6000);
            $maxImageHeight = (int) config('uploads.max_image_height', 6000);

            if (($maxImageWidth > 0 && $width > $maxImageWidth) ||
                ($maxImageHeight > 0 && $height > $maxImageHeight)) {
                return $this->error('Image dimensions exceed the allowed maximum.', 422);
            }
        }

        $uuid     = bin2hex(random_bytes(16));
        $year     = date('Y');
        $month    = date('m');
        $dir      = dirname(__DIR__, 3) . "/public/uploads/{$year}/{$month}";
        $fileName = "{$uuid}.{$ext}";
        $path     = "{$year}/{$month}/{$fileName}";

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return $this->error('Failed to prepare upload directory.', 500);
        }

        if (!move_uploaded_file($file['tmp_name'], "{$dir}/{$fileName}")) {
            return $this->error('Failed to save uploaded file.', 500);
        }

        $isImage  = $fileType === 'image';
        $fullPath = "{$dir}/{$fileName}";

        // WebP conversion and responsive variants for raster images
        $variants = [];
        if ($isImage && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $variants = $this->generateVariants($fullPath, $dir, $uuid, $mime);
        }

        $db = app_database();
        $db->query(
            'INSERT INTO media (uuid, path, file_name, original_name, mime_type, file_size, width, height, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [$uuid, $path, $fileName, $originalName, $mime, $file['size'], $width, $height]
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

    private function detectMimeType(string $path): ?string
    {
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($path);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }

        $mime = @mime_content_type($path);
        return is_string($mime) && $mime !== '' ? $mime : null;
    }

    private function resolveAllowedFileType(string $mime, string $extension): ?string
    {
        $allowedMimes      = config('uploads.allowed_mimes', []);
        $allowedExtensions = config('uploads.allowed_extensions', []);

        foreach ($allowedMimes as $type => $mimes) {
            $extensions = $allowedExtensions[$type] ?? [];
            if (!is_array($mimes) || !is_array($extensions)) {
                continue;
            }

            if (in_array($mime, $mimes, true) && in_array($extension, $extensions, true)) {
                return (string) $type;
            }
        }

        return null;
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
