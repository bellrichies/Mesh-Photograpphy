<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use Database\Seeders\Support\PlaceholderMediaWriter;

class MediaSampleSeeder
{
    public function run(Database $database): void
    {
        $mediaRows = [
            [
                'uuid' => '11111111-1111-4111-8111-111111111111',
                'original_name' => 'editorial-wedding-cover.jpg',
                'stored_name' => 'editorial-wedding-cover-2026.jpg',
                'directory' => 'images/2026/03',
                'disk' => 'public',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 2456789,
                'width' => 3200,
                'height' => 2133,
                'duration_seconds' => null,
                'alt_text' => 'Bride and groom in soft editorial light.',
                'title' => 'Editorial Wedding Cover',
                'caption' => 'Signature frame from a downtown wedding session.',
                'description' => 'Used for homepage and portfolio hero sections.',
                'checksum' => hash('sha256', 'editorial-wedding-cover-2026.jpg'),
                'is_public' => 1,
                'status' => 'active',
            ],
            [
                'uuid' => '22222222-2222-4222-8222-222222222222',
                'original_name' => 'portrait-session-mood.jpg',
                'stored_name' => 'portrait-session-mood-2026.jpg',
                'directory' => 'images/2026/03',
                'disk' => 'public',
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_type' => 'image',
                'size_bytes' => 1894432,
                'width' => 2600,
                'height' => 3900,
                'duration_seconds' => null,
                'alt_text' => 'Studio portrait with warm cinematic styling.',
                'title' => 'Portrait Mood Frame',
                'caption' => 'Editorial portrait for services page.',
                'description' => 'Sample reusable asset for content modules.',
                'checksum' => hash('sha256', 'portrait-session-mood-2026.jpg'),
                'is_public' => 1,
                'status' => 'active',
            ],
        ];

        foreach ($mediaRows as $row) {
            $database->query(
                'INSERT INTO media (
                    uuid, original_name, stored_name, directory, disk, extension, mime_type, file_type,
                    size_bytes, width, height, duration_seconds, alt_text, title, caption, description,
                    checksum, is_public, status, uploaded_by, created_at, updated_at
                ) VALUES (
                    :uuid, :original_name, :stored_name, :directory, :disk, :extension, :mime_type, :file_type,
                    :size_bytes, :width, :height, :duration_seconds, :alt_text, :title, :caption, :description,
                    :checksum, :is_public, :status, NULL, NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    original_name = VALUES(original_name),
                    stored_name = VALUES(stored_name),
                    directory = VALUES(directory),
                    mime_type = VALUES(mime_type),
                    file_type = VALUES(file_type),
                    size_bytes = VALUES(size_bytes),
                    width = VALUES(width),
                    height = VALUES(height),
                    alt_text = VALUES(alt_text),
                    title = VALUES(title),
                    caption = VALUES(caption),
                    description = VALUES(description),
                    checksum = VALUES(checksum),
                    is_public = VALUES(is_public),
                    status = VALUES(status),
                    updated_at = NOW()',
                $row
            );
        }

        $mediaId = (int) $database->query('SELECT id FROM media WHERE uuid = :uuid LIMIT 1', [
            'uuid' => '11111111-1111-4111-8111-111111111111',
        ])->fetchColumn();

        if ($mediaId > 0) {
            $database->query(
                'INSERT INTO media_variants (
                    media_id, variant_key, stored_name, directory, disk, mime_type, extension, size_bytes, width, height, created_at, updated_at
                ) VALUES (
                    :media_id, "thumb", "editorial-wedding-cover-2026-thumb.jpg", "variants/2026/03", "public", "image/jpeg", "jpg", 245678, 640, 427, NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    stored_name = VALUES(stored_name),
                    directory = VALUES(directory),
                    size_bytes = VALUES(size_bytes),
                    width = VALUES(width),
                    height = VALUES(height),
                    updated_at = NOW()',
                ['media_id' => $mediaId]
            );
        }

        (new PlaceholderMediaWriter(dirname(__DIR__, 2)))->materialize($mediaRows);
    }
}
