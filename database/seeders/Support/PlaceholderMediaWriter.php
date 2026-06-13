<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

class PlaceholderMediaWriter
{
    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $mediaRows
     */
    public function materialize(array $mediaRows): void
    {
        foreach ($mediaRows as $media) {
            $this->writeAsset(
                (string) ($media['directory'] ?? ''),
                (string) ($media['stored_name'] ?? ''),
                (string) ($media['extension'] ?? '')
            );

            foreach ((array) ($media['variants'] ?? []) as $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $this->writeAsset(
                    (string) ($variant['directory'] ?? ''),
                    (string) ($variant['stored_name'] ?? ''),
                    (string) ($variant['extension'] ?? '')
                );
            }
        }
    }

    private function writeAsset(string $directory, string $storedName, string $extension): void
    {
        $storedName = trim($storedName);
        if ($storedName === '') {
            return;
        }

        $absolutePath = $this->absoluteUploadPath($directory, $storedName);
        if (is_file($absolutePath)) {
            return;
        }

        $targetDirectory = dirname($absolutePath);
        if (! is_dir($targetDirectory) && ! @mkdir($targetDirectory, 0775, true) && ! is_dir($targetDirectory)) {
            return;
        }

        $binary = $this->placeholderBinary($extension);
        if ($binary === null) {
            return;
        }

        @file_put_contents($absolutePath, $binary, LOCK_EX);
    }

    private function absoluteUploadPath(string $directory, string $storedName): string
    {
        $relativeDirectory = $this->normalizeDirectory($directory);
        $parts = [$this->basePath, 'public', 'uploads'];

        if ($relativeDirectory !== '') {
            $parts[] = $relativeDirectory;
        }

        $parts[] = $storedName;

        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    private function normalizeDirectory(string $directory): string
    {
        $normalized = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $directory), DIRECTORY_SEPARATOR);
        $normalized = str_replace('\\', '/', $normalized);

        foreach (['public/uploads/', 'uploads/', 'public/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = ltrim(substr($normalized, strlen($prefix)), '/');
                break;
            }
        }

        return str_replace('/', DIRECTORY_SEPARATOR, trim($normalized, '/'));
    }

    private function placeholderBinary(string $extension): ?string
    {
        $normalizedExtension = strtolower(trim($extension));

        return match ($normalizedExtension) {
            'jpg', 'jpeg' => $this->jpegPlaceholder(),
            'png' => $this->pngPlaceholder(),
            'gif' => $this->gifPlaceholder(),
            'webp' => $this->webpPlaceholder(),
            default => null,
        };
    }

    private function jpegPlaceholder(): string
    {
        if (function_exists('imagecreatetruecolor') && function_exists('imagejpeg')) {
            $image = imagecreatetruecolor(16, 16);
            if ($image !== false) {
                $background = imagecolorallocate($image, 232, 224, 213);
                imagefill($image, 0, 0, $background);
                $accent = imagecolorallocate($image, 138, 106, 74);
                imageline($image, 0, 15, 15, 0, $accent);

                ob_start();
                imagejpeg($image, null, 82);
                $binary = (string) ob_get_clean();
                imagedestroy($image);

                if ($binary !== '') {
                    return $binary;
                }
            }
        }

        return (string) base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBUQEBAVFhUVFRUVFRUVFRUVFRUVFRUWFhUVFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGi0fHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAXAAADAQAAAAAAAAAAAAAAAAAAAQID/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAAB6A//xAAWEAEBAQAAAAAAAAAAAAAAAAABEBH/2gAIAQEAAT8Aq//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8Af//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8Af//Z', true);
    }

    private function pngPlaceholder(): string
    {
        if (function_exists('imagecreatetruecolor') && function_exists('imagepng')) {
            $image = imagecreatetruecolor(16, 16);
            if ($image !== false) {
                imagesavealpha($image, true);
                $background = imagecolorallocatealpha($image, 247, 243, 238, 0);
                imagefill($image, 0, 0, $background);
                $accent = imagecolorallocate($image, 138, 106, 74);
                imagefilledrectangle($image, 4, 4, 11, 11, $accent);

                ob_start();
                imagepng($image);
                $binary = (string) ob_get_clean();
                imagedestroy($image);

                if ($binary !== '') {
                    return $binary;
                }
            }
        }

        return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7Z0X8AAAAASUVORK5CYII=', true);
    }

    private function gifPlaceholder(): string
    {
        return (string) base64_decode('R0lGODdhAQABAIAAAP///////ywAAAAAAQABAAACAkQBADs=', true);
    }

    private function webpPlaceholder(): string
    {
        if (function_exists('imagecreatetruecolor') && function_exists('imagewebp')) {
            $image = imagecreatetruecolor(16, 16);
            if ($image !== false) {
                $background = imagecolorallocate($image, 247, 243, 238);
                imagefill($image, 0, 0, $background);
                $accent = imagecolorallocate($image, 138, 106, 74);
                imagefilledellipse($image, 8, 8, 10, 10, $accent);

                ob_start();
                imagewebp($image, null, 80);
                $binary = (string) ob_get_clean();
                imagedestroy($image);

                if ($binary !== '') {
                    return $binary;
                }
            }
        }

        return $this->jpegPlaceholder();
    }
}
