<?php

declare(strict_types=1);

/**
 * Gallery Boost Seeder
 *
 * Extends the demo dataset with 8 additional photo tiles (photo-13 → photo-20),
 * 4 extra gallery categories, and richer gallery_media assignments — bringing the
 * total to 20 gallery images across 10 galleries.
 *
 * Usage:
 *   php database/console.php seed:gallery-boost
 *
 * Safe to re-run: every step is idempotent.
 * Requires the DemoSeeder to have been run first (relies on existing galleries).
 */
class GalleryBoostSeeder
{
    private PDO    $pdo;
    private string $uploadsDir;
    private array  $mediaIds = [];   // label => id

    public function run(PDO $pdo): void
    {
        $this->pdo        = $pdo;
        $this->uploadsDir = dirname(__DIR__, 2) . '/public/uploads/demo';

        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0775, true);
        }

        echo "\n── Gallery Boost Seeder ──────────────────────────────────────────\n";

        // Pull every previously-seeded demo media ID into the lookup map so
        // that `mid()` resolves labels seeded by DemoSeeder as well.
        $this->loadExistingMediaIds();

        // Make all existing galleries featured so they all appear in the
        // homepage dense grid.
        $this->pdo->exec(
            "UPDATE galleries SET is_featured = 1 WHERE is_featured = 0 AND deleted_at IS NULL"
        );
        echo "\n  [pre-flight]\n   [ok]   all galleries marked as featured\n";

        $this->step('Additional photo media',  fn() => $this->seedPhotos());
        $this->step('Additional galleries',    fn() => $this->seedGalleries());
        $this->step('Gallery media links',     fn() => $this->seedGalleryMedia());

        echo "\n── Done ──────────────────────────────────────────────────────────\n\n";
    }

    // ── Load existing demo media into the lookup map ──────────────────────────

    private function loadExistingMediaIds(): void
    {
        $rows = $this->pdo->query(
            "SELECT original_name, id FROM media
             WHERE original_name LIKE 'demo-%' AND deleted_at IS NULL"
        )->fetchAll();

        foreach ($rows as $row) {
            // "demo-photo-01.jpg" → "photo-01"
            $label = preg_replace('/^demo-/', '', preg_replace('/\.jpg$/', '', $row['original_name']));
            $this->mediaIds[$label] = (int) $row['id'];
        }

        echo '   [info] loaded ' . count($this->mediaIds) . " existing demo media records\n";
    }

    // ── New photo media ────────────────────────────────────────────────────────

    private function seedPhotos(): void
    {
        // 4 gallery covers (landscape) + 8 portrait-orientation photo tiles.
        // Portrait 480×640 matches the homepage aspect-[3/4] grid perfectly.
        $images = [
            // New gallery covers
            'gallery-maternity-cover' => [800, 600, [60, 48, 58],  'Maternity Gallery'],
            'gallery-newborn-cover'   => [800, 600, [58, 52, 42],  'Newborn Gallery'],
            'gallery-fineart-cover'   => [800, 600, [38, 38, 48],  'Fine Art Gallery'],
            'gallery-fashion-cover'   => [800, 600, [42, 36, 38],  'Fashion Gallery'],

            // Portrait photo tiles (photo-13 → photo-20)
            'photo-13' => [480, 640, [82, 62, 50], 'Photo 13'],
            'photo-14' => [480, 640, [52, 78, 82], 'Photo 14'],
            'photo-15' => [480, 640, [72, 58, 78], 'Photo 15'],
            'photo-16' => [480, 640, [58, 72, 62], 'Photo 16'],
            'photo-17' => [480, 640, [78, 68, 44], 'Photo 17'],
            'photo-18' => [480, 640, [44, 62, 78], 'Photo 18'],
            'photo-19' => [480, 640, [68, 44, 68], 'Photo 19'],
            'photo-20' => [480, 640, [62, 74, 56], 'Photo 20'],
        ];

        $gdAvailable = function_exists('imagecreatetruecolor');

        foreach ($images as $label => [$w, $h, $rgb, $text]) {
            if (isset($this->mediaIds[$label])) {
                echo "   [skip] media:{$label}\n";
                continue;
            }

            $existing = $this->pdo->prepare(
                'SELECT id FROM media WHERE original_name = ? AND deleted_at IS NULL LIMIT 1'
            );
            $existing->execute(["demo-{$label}.jpg"]);
            if ($row = $existing->fetch()) {
                $this->mediaIds[$label] = (int) $row['id'];
                echo "   [skip] media:{$label}\n";
                continue;
            }

            $uuid     = bin2hex(random_bytes(8));
            $fileName = "{$uuid}.jpg";
            $filePath = $this->uploadsDir . '/' . $fileName;
            $path     = "demo/{$fileName}";

            if ($gdAvailable) {
                $this->createPlaceholderJpeg($filePath, $w, $h, $rgb, $text);
                $size = file_exists($filePath) ? filesize($filePath) : 1024;
            } else {
                file_put_contents($filePath, base64_decode(
                    '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U' .
                    'HRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAARCAABAAEDASIA' .
                    'AhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/' .
                    'EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIR' .
                    'AxEAPwCwABmX/9k='
                ));
                $size = filesize($filePath) ?: 1024;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO media (uuid, path, file_name, original_name, mime_type, file_size, width, height, alt_text, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([$uuid, $path, $fileName, "demo-{$label}.jpg", 'image/jpeg', $size, $w, $h, $text]);

            $this->mediaIds[$label] = (int) $this->pdo->lastInsertId();
            echo "   [ok]   media:{$label}\n";
        }
    }

    private function createPlaceholderJpeg(string $path, int $w, int $h, array $rgb, string $label): void
    {
        $img = imagecreatetruecolor($w, $h);
        $bg  = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($img, 0, 0, $bg);

        // Subtle grain
        for ($i = 0; $i < ($w * $h) / 8; $i++) {
            $v = rand(-18, 18);
            $c = imagecolorallocatealpha($img,
                max(0, min(255, $rgb[0] + $v)),
                max(0, min(255, $rgb[1] + $v)),
                max(0, min(255, $rgb[2] + $v)),
                110
            );
            imagesetpixel($img, rand(0, $w - 1), rand(0, $h - 1), $c);
        }

        // Centered label
        $tc   = imagecolorallocate($img, 255, 255, 255);
        $fs   = max(1, (int) ($w / 40));
        $tw   = imagefontwidth($fs) * strlen($label);
        $tHt  = imagefontheight($fs);
        imagestring($img, $fs, (int)(($w - $tw) / 2), (int)(($h - $tHt) / 2), $label, $tc);

        imagejpeg($img, $path, 82);
        // imagedestroy() is deprecated in PHP 8.5; GDImage is freed by GC.
    }

    private function mid(string $label): ?int
    {
        return $this->mediaIds[$label] ?? null;
    }

    // ── Additional galleries ──────────────────────────────────────────────────

    private function seedGalleries(): void
    {
        $galleries = [
            [
                'title'       => 'Maternity',
                'slug'        => 'maternity',
                'description' => 'Intimate maternity portraits celebrating the beauty and anticipation of new life.',
                'category'    => 'maternity',
                'cover'       => 'gallery-maternity-cover',
                'featured'    => 1,
                'sort_order'  => 6,
            ],
            [
                'title'       => 'Newborn',
                'slug'        => 'newborn',
                'description' => 'Gentle newborn sessions capturing the first precious days with your little one.',
                'category'    => 'newborn',
                'cover'       => 'gallery-newborn-cover',
                'featured'    => 1,
                'sort_order'  => 7,
            ],
            [
                'title'       => 'Fine Art',
                'slug'        => 'fine-art',
                'description' => 'A collection of fine art portraits created with intention, light, and a painterly eye.',
                'category'    => 'fine-art',
                'cover'       => 'gallery-fineart-cover',
                'featured'    => 1,
                'sort_order'  => 8,
            ],
            [
                'title'       => 'Fashion',
                'slug'        => 'fashion',
                'description' => 'Editorial fashion photography for designers, brands, and creative campaigns.',
                'category'    => 'fashion',
                'cover'       => 'gallery-fashion-cover',
                'featured'    => 1,
                'sort_order'  => 9,
            ],
        ];

        foreach ($galleries as $g) {
            $exists = $this->pdo->prepare(
                'SELECT id FROM galleries WHERE slug = ? AND deleted_at IS NULL LIMIT 1'
            );
            $exists->execute([$g['slug']]);
            if ($exists->fetch()) {
                echo "   [skip] gallery: {$g['title']}\n";
                continue;
            }

            $this->pdo->prepare(
                'INSERT INTO galleries (title, slug, description, cover_image_id, category, is_featured, is_published, published_at, sort_order, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,1,NOW(),?,NOW(),NOW())'
            )->execute([
                $g['title'], $g['slug'], $g['description'],
                $this->mid($g['cover']), $g['category'],
                $g['featured'], $g['sort_order'],
            ]);

            echo "   [ok]   gallery: {$g['title']}\n";
        }
    }

    // ── Gallery media links ───────────────────────────────────────────────────

    private function seedGalleryMedia(): void
    {
        // Assign photos to galleries.
        // New photos (photo-13→20) are distributed across all 10 galleries so
        // every gallery detail page has rich photo content for beta testing.
        $assignments = [
            // New galleries — primary photos
            'maternity'   => ['photo-13', 'photo-15', 'photo-17', 'photo-19'],
            'newborn'     => ['photo-14', 'photo-16', 'photo-18', 'photo-20'],
            'fine-art'    => ['photo-13', 'photo-16', 'photo-19', 'photo-20'],
            'fashion'     => ['photo-14', 'photo-15', 'photo-17', 'photo-18'],

            // Enrich existing galleries with additional photos
            'weddings'    => ['photo-13', 'photo-15', 'photo-17', 'photo-19'],
            'portraits'   => ['photo-14', 'photo-16', 'photo-18', 'photo-20'],
            'engagements' => ['photo-13', 'photo-14', 'photo-19', 'photo-20'],
            'family'      => ['photo-15', 'photo-16', 'photo-17', 'photo-18'],
            'commercial'  => ['photo-13', 'photo-17', 'photo-18', 'photo-20'],
            'events'      => ['photo-14', 'photo-15', 'photo-16', 'photo-19'],
        ];

        foreach ($assignments as $gallerySlug => $photoLabels) {
            $row = $this->pdo->prepare('SELECT id FROM galleries WHERE slug = ? AND deleted_at IS NULL LIMIT 1');
            $row->execute([$gallerySlug]);
            $galleryId = $row->fetchColumn();

            if (!$galleryId) {
                echo "   [warn] gallery not found: {$gallerySlug}\n";
                continue;
            }

            // Determine the current max sort_order for this gallery so new
            // photos are appended after any already-linked ones.
            $maxStmt = $this->pdo->prepare(
                'SELECT COALESCE(MAX(sort_order), -1) FROM gallery_media WHERE gallery_id = ?'
            );
            $maxStmt->execute([(int) $galleryId]);
            $maxSort = (int) $maxStmt->fetchColumn();

            $inserted = 0;
            foreach ($photoLabels as $i => $label) {
                $mediaId = $this->mid($label);
                if (!$mediaId) {
                    echo "   [warn] media not found: {$label}\n";
                    continue;
                }

                $ex = $this->pdo->prepare(
                    'SELECT 1 FROM gallery_media WHERE gallery_id = ? AND media_id = ? LIMIT 1'
                );
                $ex->execute([(int)$galleryId, $mediaId]);
                if ($ex->fetch()) continue;

                $this->pdo->prepare(
                    'INSERT INTO gallery_media (gallery_id, media_id, sort_order) VALUES (?,?,?)'
                )->execute([(int)$galleryId, $mediaId, $maxSort + 1 + $i]);

                $inserted++;
            }

            $status = $inserted > 0 ? "[ok]   +{$inserted} photos" : '[skip] already linked';
            echo "   {$status} → {$gallerySlug}\n";
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function step(string $label, callable $fn): void
    {
        echo "\n  [{$label}]\n";
        $fn();
    }
}
