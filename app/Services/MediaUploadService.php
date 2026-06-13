<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\UploadPathHelper;
use RuntimeException;

class MediaUploadService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        private readonly Database $database,
        private readonly string $basePath
    ) {
    }

    private const PUBLIC_UPLOAD_PREFIX = 'public/uploads/';

    /**
     * @param array<string, mixed> $file
     * @return array<string, mixed>
     */
    public function upload(array $file, ?int $uploadedBy = null): array
    {
        $this->guardUploadArray($file);

        $originalName = (string) ($file['name'] ?? '');
        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $sizeBytes = (int) ($file['size'] ?? 0);
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_OK);

        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed with error code ' . $errorCode . '.');
        }

        if (! is_uploaded_file($tmpPath)) {
            throw new RuntimeException('Uploaded file could not be verified.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Unable to inspect uploaded file type.');
        }

        $mimeType = (string) finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileType = $this->resolveFileType($mimeType, $extension);

        $this->validateByType($fileType, $mimeType, $extension, $sizeBytes, $tmpPath);

        $checksum = hash_file('sha256', $tmpPath) ?: null;

        $datePath = UploadPathHelper::currentDatePath();
        $targetDirectory = $this->targetDirectoryByType($fileType, $datePath);
        $absoluteDirectory = $this->basePath . DIRECTORY_SEPARATOR . UploadPathHelper::normalize($targetDirectory);

        if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0775, true) && ! is_dir($absoluteDirectory)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        $this->ensureDirectoryProtection($absoluteDirectory);

        $storedName = $this->generateStoredName($extension);
        $absoluteTargetPath = $absoluteDirectory . DIRECTORY_SEPARATOR . $storedName;

        if (! @move_uploaded_file($tmpPath, $absoluteTargetPath)) {
            throw new RuntimeException('Failed to securely move uploaded file.');
        }

        $meta = $this->imageMeta($absoluteTargetPath, $fileType);
        $uuid = $this->uuidV4();

        $this->database->query(
            'INSERT INTO media (
                uuid, original_name, stored_name, directory, disk, extension, mime_type, file_type,
                size_bytes, width, height, duration_seconds, alt_text, title, caption, description,
                checksum, is_public, status, uploaded_by, created_at, updated_at
            ) VALUES (
                :uuid, :original_name, :stored_name, :directory, :disk, :extension, :mime_type, :file_type,
                :size_bytes, :width, :height, :duration_seconds, NULL, NULL, NULL, NULL,
                :checksum, 1, "active", :uploaded_by, NOW(), NOW()
            )',
            [
                'uuid' => $uuid,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'directory' => $this->publicDirectoryForStorage($targetDirectory),
                'disk' => 'public',
                'extension' => $extension,
                'mime_type' => $mimeType,
                'file_type' => $fileType,
                'size_bytes' => $sizeBytes,
                'width' => $meta['width'],
                'height' => $meta['height'],
                'duration_seconds' => null,
                'checksum' => $checksum,
                'uploaded_by' => $uploadedBy,
            ]
        );

        $mediaId = (int) $this->database->query('SELECT LAST_INSERT_ID()')->fetchColumn();

        if ($fileType === 'image') {
            $this->createImageVariant($mediaId, $absoluteTargetPath, $extension, $targetDirectory, $storedName);
        }

        return [
            'id' => $mediaId,
            'uuid' => $uuid,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'directory' => $this->publicDirectoryForStorage($targetDirectory),
            'mime_type' => $mimeType,
            'file_type' => $fileType,
            'size_bytes' => $sizeBytes,
            'checksum' => $checksum,
        ];
    }

    /**
     * @param array<string, mixed> $file
     */
    private function guardUploadArray(array $file): void
    {
        foreach (['name', 'tmp_name', 'size', 'error'] as $required) {
            if (! array_key_exists($required, $file)) {
                throw new RuntimeException('Invalid uploaded file payload.');
            }
        }
    }

    private function resolveFileType(string $mimeType, string $extension): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (in_array($extension, ['pdf', 'doc', 'docx', 'txt'], true)) {
            return 'document';
        }

        return 'other';
    }

    private function validateByType(string $fileType, string $mimeType, string $extension, int $sizeBytes, string $tmpPath): void
    {
        $maxFileSizeMb = (int) config('uploads.max_file_size_mb', 10);
        if ($sizeBytes > ($maxFileSizeMb * 1024 * 1024)) {
            throw new RuntimeException('File exceeds maximum allowed size of ' . $maxFileSizeMb . ' MB.');
        }

        $allowed = (array) config('uploads.allowed', []);
        $allowedMimes = match ($fileType) {
            'image' => (array) ($allowed['images'] ?? []),
            'video' => (array) ($allowed['videos'] ?? []),
            'document' => (array) ($allowed['documents'] ?? []),
            default => [],
        };

        if ($allowedMimes === [] || ! in_array($mimeType, $allowedMimes, true)) {
            throw new RuntimeException('The uploaded file type is not allowed.');
        }

        if ($fileType === 'image') {
            if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                throw new RuntimeException('Image extension is not allowed.');
            }

            $dimensions = @getimagesize($tmpPath);
            if (! is_array($dimensions)) {
                throw new RuntimeException('Invalid image file content.');
            }

            $maxWidth = (int) config('uploads.max_image_width', 6000);
            $maxHeight = (int) config('uploads.max_image_height', 6000);

            if ((int) $dimensions[0] > $maxWidth || (int) $dimensions[1] > $maxHeight) {
                throw new RuntimeException('Image dimensions exceed allowed limits.');
            }
        }

        if ($fileType === 'other') {
            throw new RuntimeException('Unsupported file category.');
        }

        if (in_array($extension, ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'ps1'], true)) {
            throw new RuntimeException('Blocked file extension.');
        }
    }

    private function targetDirectoryByType(string $fileType, string $datePath): string
    {
        $base = match ($fileType) {
            'image' => (string) config('uploads.paths.images', 'public/uploads/images'),
            'video' => (string) config('uploads.paths.videos', 'public/uploads/videos'),
            'document' => (string) config('uploads.paths.documents', 'public/uploads/documents'),
            default => (string) config('uploads.paths.base', 'public/uploads'),
        };

        return UploadPathHelper::join($base, $datePath);
    }

    private function generateStoredName(string $extension): string
    {
        $safeExtension = preg_replace('/[^a-z0-9]/', '', strtolower($extension)) ?: 'bin';
        return date('YmdHis') . '-' . bin2hex(random_bytes(10)) . '.' . $safeExtension;
    }

    /**
     * @return array{width: int|null, height: int|null}
     */
    private function imageMeta(string $absolutePath, string $fileType): array
    {
        if ($fileType !== 'image') {
            return ['width' => null, 'height' => null];
        }

        $imageSize = @getimagesize($absolutePath);
        if (! is_array($imageSize)) {
            return ['width' => null, 'height' => null];
        }

        return [
            'width' => isset($imageSize[0]) ? (int) $imageSize[0] : null,
            'height' => isset($imageSize[1]) ? (int) $imageSize[1] : null,
        ];
    }

    private function createImageVariant(int $mediaId, string $absoluteOriginalPath, string $extension, string $targetDirectory, string $storedName): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return;
        }

        $raw = @file_get_contents($absoluteOriginalPath);
        if ($raw === false) {
            return;
        }

        $source = @imagecreatefromstring($raw);
        if ($source === false) {
            return;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $thumbWidth = 640;
        $thumbHeight = (int) max(1, floor(($sourceHeight / max(1, $sourceWidth)) * $thumbWidth));

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        $variantDirectory = UploadPathHelper::join((string) config('uploads.paths.variants', 'public/uploads/variants'), date('Y'), date('m'));
        $absoluteVariantDir = $this->basePath . DIRECTORY_SEPARATOR . UploadPathHelper::normalize($variantDirectory);

        if (! is_dir($absoluteVariantDir) && ! mkdir($absoluteVariantDir, 0775, true) && ! is_dir($absoluteVariantDir)) {
            imagedestroy($thumb);
            imagedestroy($source);
            return;
        }

        $this->ensureDirectoryProtection($absoluteVariantDir);

        $variantName = pathinfo($storedName, PATHINFO_FILENAME) . '-thumb.jpg';
        $absoluteVariantPath = $absoluteVariantDir . DIRECTORY_SEPARATOR . $variantName;

        @imagejpeg($thumb, $absoluteVariantPath, 82);

        imagedestroy($thumb);
        imagedestroy($source);

        $variantSize = is_file($absoluteVariantPath) ? (int) filesize($absoluteVariantPath) : 0;

        if ($variantSize <= 0) {
            return;
        }

        $this->database->query(
            'INSERT INTO media_variants (
                media_id, variant_key, stored_name, directory, disk, mime_type, extension, size_bytes, width, height, created_at, updated_at
            ) VALUES (
                :media_id, "thumb", :stored_name, :directory, "public", "image/jpeg", "jpg", :size_bytes, :width, :height, NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                stored_name = VALUES(stored_name),
                directory = VALUES(directory),
                size_bytes = VALUES(size_bytes),
                width = VALUES(width),
                height = VALUES(height),
                updated_at = NOW()',
            [
                'media_id' => $mediaId,
                'stored_name' => $variantName,
                'directory' => $this->publicDirectoryForStorage($variantDirectory),
                'size_bytes' => $variantSize,
                'width' => $thumbWidth,
                'height' => $thumbHeight,
            ]
        );
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function ensureDirectoryProtection(string $absoluteDirectory): void
    {
        $htaccessPath = rtrim($absoluteDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';
        if (is_file($htaccessPath)) {
            return;
        }

        $rules = implode("\n", [
            '# Auto-generated upload directory hardening',
            'Options -Indexes',
            '<FilesMatch "\\.(php|phtml|phar|pl|py|cgi|asp|aspx|sh|bat|cmd|exe|dll|com)$">',
            '    Require all denied',
            '</FilesMatch>',
            'RemoveHandler .php .phtml .phar .pl .py .cgi .asp .aspx .sh .bat .cmd .exe .dll .com',
            'RemoveType .php .phtml .phar .pl .py .cgi .asp .aspx .sh .bat .cmd .exe .dll .com',
            '',
        ]);

        @file_put_contents($htaccessPath, $rules);
    }

    private function publicDirectoryForStorage(string $directory): string
    {
        $normalized = str_replace('\\', '/', trim($directory, '/\\'));

        if (str_starts_with($normalized, self::PUBLIC_UPLOAD_PREFIX)) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'uploads/')) {
            return 'public/' . $normalized;
        }

        if (str_starts_with($normalized, 'public/')) {
            return $normalized;
        }

        return UploadPathHelper::toPublicPath($normalized);
    }
}
