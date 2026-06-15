<?php

declare(strict_types=1);

/**
 * Demo Seeder — populates all tables with realistic sample data for testing.
 *
 * Usage:
 *   php database/console.php seed:demo
 *
 * Safe to re-run: skips records that already exist via slug/email checks.
 * Creates placeholder JPEG images in public/uploads/demo/ using GD.
 */
class DemoSeeder
{
    private PDO    $pdo;
    private string $uploadsDir;
    private array  $mediaIds = [];  // label => id

    public function run(PDO $pdo): void
    {
        $this->pdo        = $pdo;
        $this->uploadsDir = dirname(__DIR__, 2) . '/public/uploads/demo';

        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0775, true);
        }

        echo "\n── Mesh Photography Demo Seeder ─────────────────────────────────\n";

        $this->step('Placeholder images',   fn() => $this->seedMedia());
        $this->step('Hero slides',          fn() => $this->seedHeroSlides());
        $this->step('Services',             fn() => $this->seedServices());
        $this->step('Service packages',     fn() => $this->seedServicePackages());
        $this->step('Galleries',            fn() => $this->seedGalleries());
        $this->step('Gallery media',        fn() => $this->seedGalleryMedia());
        $this->step('Blog categories',      fn() => $this->seedBlogCategories());
        $this->step('Blog tags',            fn() => $this->seedBlogTags());
        $this->step('Blog posts',           fn() => $this->seedBlogPosts());
        $this->step('Testimonials',         fn() => $this->seedTestimonials());
        $this->step('CMS pages',            fn() => $this->seedPages());
        $this->step('Inquiries',            fn() => $this->seedInquiries());
        $this->step('Booking requests',     fn() => $this->seedBookingRequests());

        echo "\n── Done ──────────────────────────────────────────────────────────\n\n";
    }

    // ── Placeholder images ────────────────────────────────────────────────────

    private function seedMedia(): void
    {
        // label => [width, height, bg_rgb, label_text]
        $images = [
            // Hero slides
            'hero-wedding'    => [1200, 675, [30, 20, 20],   'Hero — Timeless Weddings'],
            'hero-portrait'   => [1200, 675, [20, 30, 35],   'Hero — Portrait Sessions'],
            'hero-editorial'  => [1200, 675, [25, 22, 18],   'Hero — Editorial & Commercial'],

            // Gallery covers + photos
            'gallery-wedding-cover' => [800, 600, [60, 40, 40],  'Weddings Gallery'],
            'gallery-portrait-cover'=> [800, 600, [40, 55, 60],  'Portraits Gallery'],
            'gallery-engage-cover'  => [800, 600, [55, 45, 35],  'Engagements Gallery'],
            'gallery-family-cover'  => [800, 600, [40, 50, 40],  'Family Gallery'],
            'gallery-commercial-cover'=> [800, 600, [35, 35, 55],'Commercial Gallery'],
            'gallery-event-cover'   => [800, 600, [50, 35, 50],  'Events Gallery'],

            // Gallery photo tiles (shared across galleries)
            'photo-01' => [800, 600, [70, 50, 50], 'Photo 01'],
            'photo-02' => [800, 600, [50, 65, 70], 'Photo 02'],
            'photo-03' => [800, 600, [65, 60, 45], 'Photo 03'],
            'photo-04' => [800, 600, [45, 55, 65], 'Photo 04'],
            'photo-05' => [800, 600, [60, 45, 60], 'Photo 05'],
            'photo-06' => [800, 600, [50, 60, 50], 'Photo 06'],
            'photo-07' => [800, 600, [75, 55, 40], 'Photo 07'],
            'photo-08' => [800, 600, [40, 60, 75], 'Photo 08'],
            'photo-09' => [800, 600, [55, 70, 55], 'Photo 09'],
            'photo-10' => [800, 600, [70, 40, 55], 'Photo 10'],
            'photo-11' => [800, 600, [45, 65, 55], 'Photo 11'],
            'photo-12' => [800, 600, [60, 60, 40], 'Photo 12'],

            // Service covers
            'service-wedding'    => [800, 600, [80, 55, 55], 'Wedding Photography'],
            'service-portrait'   => [800, 600, [55, 75, 80], 'Portrait Photography'],
            'service-commercial' => [800, 600, [55, 55, 80], 'Commercial Photography'],
            'service-event'      => [800, 600, [75, 55, 75], 'Event Photography'],

            // Blog covers
            'blog-01' => [800, 450, [65, 50, 45], 'Blog Post 01'],
            'blog-02' => [800, 450, [45, 60, 65], 'Blog Post 02'],
            'blog-03' => [800, 450, [60, 65, 45], 'Blog Post 03'],
            'blog-04' => [800, 450, [50, 45, 65], 'Blog Post 04'],

            // Testimonial avatars
            'avatar-01' => [200, 200, [180, 140, 120], 'Client 1'],
            'avatar-02' => [200, 200, [140, 160, 170], 'Client 2'],
            'avatar-03' => [200, 200, [170, 155, 130], 'Client 3'],
            'avatar-04' => [200, 200, [150, 170, 150], 'Client 4'],
            'avatar-05' => [200, 200, [165, 145, 165], 'Client 5'],
            'avatar-06' => [200, 200, [155, 165, 145], 'Client 6'],
        ];

        $gdAvailable = function_exists('imagecreatetruecolor');

        foreach ($images as $label => [$w, $h, $rgb, $text]) {
            // Skip if already seeded (check by original_name)
            $existing = $this->pdo->prepare('SELECT id FROM media WHERE original_name = ? AND deleted_at IS NULL LIMIT 1');
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
                [$width, $height] = [$w, $h];
                $size = file_exists($filePath) ? filesize($filePath) : 1024;
            } else {
                // Fallback: create a minimal JPEG stub (1x1 white pixel)
                file_put_contents($filePath, base64_decode(
                    '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U' .
                    'HRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgN' .
                    'DRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy' .
                    'MjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAA' .
                    'AAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA' .
                    '/9oADAMBAAIRAxEAPwCwABmX/9k='
                ));
                [$width, $height] = [$w, $h];
                $size = filesize($filePath) ?: 1024;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO media (uuid, path, file_name, original_name, mime_type, file_size, width, height, alt_text, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([$uuid, $path, $fileName, "demo-{$label}.jpg", 'image/jpeg', $size, $width, $height, $text]);

            $this->mediaIds[$label] = (int) $this->pdo->lastInsertId();
            echo "   [ok]   media:{$label}\n";
        }
    }

    private function createPlaceholderJpeg(string $path, int $w, int $h, array $rgb, string $label): void
    {
        $img = imagecreatetruecolor($w, $h);
        $bg  = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($img, 0, 0, $bg);

        // Subtle grain overlay
        for ($i = 0; $i < ($w * $h) / 8; $i++) {
            $v = rand(-15, 15);
            $c = imagecolorallocatealpha($img,
                max(0, min(255, $rgb[0] + $v)),
                max(0, min(255, $rgb[1] + $v)),
                max(0, min(255, $rgb[2] + $v)),
                120
            );
            imagesetpixel($img, rand(0, $w - 1), rand(0, $h - 1), $c);
        }

        // Center text
        $textColor = imagecolorallocate($img, 255, 255, 255);
        $fontSize  = max(1, (int) ($w / 40));
        $textW     = imagefontwidth($fontSize) * strlen($label);
        $textH     = imagefontheight($fontSize);
        imagestring($img, $fontSize, (int)(($w - $textW) / 2), (int)(($h - $textH) / 2), $label, $textColor);

        imagejpeg($img, $path, 80);
        imagedestroy($img);
    }

    private function mid(string $label): ?int
    {
        return $this->mediaIds[$label] ?? null;
    }

    // ── Hero slides ───────────────────────────────────────────────────────────

    private function seedHeroSlides(): void
    {
        $slides = [
            [
                'heading'    => 'Timeless Moments, Beautifully Captured',
                'subheading' => 'Award-winning wedding & portrait photography',
                'image'      => 'hero-wedding',
                'cta_label'  => 'View Portfolio',
                'cta_url'    => '/portfolio',
                'sort_order' => 0,
            ],
            [
                'heading'    => 'Every Portrait Tells a Story',
                'subheading' => 'Authentic expressions, extraordinary light',
                'image'      => 'hero-portrait',
                'cta_label'  => 'Book a Session',
                'cta_url'    => '/contact',
                'sort_order' => 1,
            ],
            [
                'heading'    => 'Visual Storytelling for Brands',
                'subheading' => 'Commercial photography that elevates your brand',
                'image'      => 'hero-editorial',
                'cta_label'  => 'Our Services',
                'cta_url'    => '/services',
                'sort_order' => 2,
            ],
        ];

        foreach ($slides as $s) {
            $exists = $this->pdo->prepare('SELECT id FROM hero_slides WHERE heading = ? AND deleted_at IS NULL LIMIT 1');
            $exists->execute([$s['heading']]);
            if ($exists->fetch()) { echo "   [skip] hero: {$s['heading']}\n"; continue; }

            $this->pdo->prepare(
                'INSERT INTO hero_slides (heading, subheading, image_id, cta_label, cta_url, sort_order, is_published, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,1,NOW(),NOW())'
            )->execute([$s['heading'], $s['subheading'], $this->mid($s['image']), $s['cta_label'], $s['cta_url'], $s['sort_order']]);

            echo "   [ok]   hero: {$s['heading']}\n";
        }
    }

    // ── Services ──────────────────────────────────────────────────────────────

    private function seedServices(): void
    {
        $services = [
            [
                'title'     => 'Wedding Photography',
                'slug'      => 'wedding-photography',
                'short_desc'=> 'Full-day coverage of your most cherished celebration.',
                'description'=> '<p>Our wedding photography service is a complete documentary of your special day — from the quiet moments of preparation to the joyful chaos of the dance floor. We blend photojournalism with fine-art portraiture to create a collection that feels both timeless and deeply personal.</p><p>Every wedding is unique. We take time to understand your vision, your story, and the details that matter most. The result is a gallery that you will return to for generations.</p>',
                'price_label'=> 'From £2,500',
                'image'     => 'service-wedding',
                'sort_order'=> 0,
            ],
            [
                'title'     => 'Portrait Sessions',
                'slug'      => 'portrait-sessions',
                'short_desc'=> 'Individual, couple, and family portraits with natural light.',
                'description'=> '<p>Portrait sessions are intimate collaborations. Whether you need a professional headshot, a family portrait, or a personal branding session, we create images that reveal authentic character and warmth.</p><p>Sessions take place in-studio or on location at a setting meaningful to you. Every portrait is carefully lit and composed to showcase you at your best.</p>',
                'price_label'=> 'From £350',
                'image'     => 'service-portrait',
                'sort_order'=> 1,
            ],
            [
                'title'     => 'Commercial Photography',
                'slug'      => 'commercial-photography',
                'short_desc'=> 'Brand imagery that converts browsers into customers.',
                'description'=> '<p>In a visual economy, compelling imagery is your most powerful marketing asset. Our commercial photography service produces polished, on-brand images for websites, social media, advertising, and print — designed to communicate your value instantly.</p><p>We work closely with your creative team (or ours) to plan, style, and deliver images that align with your brand guidelines and campaign objectives.</p>',
                'price_label'=> 'From £800',
                'image'     => 'service-commercial',
                'sort_order'=> 2,
            ],
            [
                'title'     => 'Event Photography',
                'slug'      => 'event-photography',
                'short_desc'=> 'Corporate events, galas, launches, and private celebrations.',
                'description'=> '<p>Events are fleeting — the energy, the connections, the moments of recognition. Our event photography captures all of it unobtrusively, giving you a rich visual record of every keynote, toast, and handshake.</p><p>Fast turnaround available for press and social media needs. We are experienced with corporate events, charity galas, product launches, and private celebrations of all sizes.</p>',
                'price_label'=> 'From £600',
                'image'     => 'service-event',
                'sort_order'=> 3,
            ],
        ];

        foreach ($services as $s) {
            $exists = $this->pdo->prepare('SELECT id FROM services WHERE slug = ? AND deleted_at IS NULL LIMIT 1');
            $exists->execute([$s['slug']]);
            if ($exists->fetch()) { echo "   [skip] service: {$s['title']}\n"; continue; }

            $this->pdo->prepare(
                'INSERT INTO services (title, slug, short_desc, description, price_label, cover_image_id, is_featured, is_published, sort_order, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,1,1,?,NOW(),NOW())'
            )->execute([$s['title'], $s['slug'], $s['short_desc'], $s['description'], $s['price_label'], $this->mid($s['image']), $s['sort_order']]);

            echo "   [ok]   service: {$s['title']}\n";
        }
    }

    // ── Service packages ──────────────────────────────────────────────────────

    private function seedServicePackages(): void
    {
        $packages = [
            'wedding-photography' => [
                ['name' => 'Essential',   'price' => 2500.00, 'features' => ['8 hours coverage', '2 photographers', 'Online gallery', '500+ edited images', 'Print release']],
                ['name' => 'Classic',     'price' => 3500.00, 'features' => ['10 hours coverage', '2 photographers', 'Engagement session', 'Online gallery', '800+ edited images', 'Premium album (30 pages)']],
                ['name' => 'Signature',   'price' => 5000.00, 'features' => ['Full day coverage', '2 photographers', 'Engagement session', 'Rehearsal dinner', 'Online gallery', '1000+ edited images', 'Luxury album (50 pages)', 'Fine art prints']],
            ],
            'portrait-sessions' => [
                ['name' => 'Mini Session',  'price' => 350.00,  'features' => ['45 minutes', '1 location', '20 edited images', 'Online gallery']],
                ['name' => 'Full Session',  'price' => 650.00,  'features' => ['2 hours', '2 locations', '50 edited images', 'Online gallery', '5 prints included']],
                ['name' => 'Family Day',    'price' => 950.00,  'features' => ['Half day', '3 locations', '100+ edited images', 'Online gallery', 'Framed family portrait']],
            ],
            'commercial-photography' => [
                ['name' => 'Starter',   'price' => 800.00,  'features' => ['Half day shoot', '1 location', '30 edited images', 'Commercial licence']],
                ['name' => 'Business',  'price' => 1600.00, 'features' => ['Full day shoot', '2 locations', '80 edited images', 'Commercial licence', 'Art direction included']],
            ],
            'event-photography' => [
                ['name' => 'Essentials', 'price' => 600.00,  'features' => ['3 hours', '1 photographer', '150+ edited images', '48hr delivery']],
                ['name' => 'Full Event', 'price' => 1100.00, 'features' => ['6 hours', '1 photographer', '300+ edited images', '24hr delivery', 'Social media exports']],
            ],
        ];

        foreach ($packages as $slug => $pkgs) {
            $svc = $this->pdo->prepare('SELECT id FROM services WHERE slug = ? LIMIT 1');
            $svc->execute([$slug]);
            $serviceId = $svc->fetchColumn();
            if (!$serviceId) { echo "   [skip] packages for {$slug} (service not found)\n"; continue; }

            foreach ($pkgs as $i => $pkg) {
                $ex = $this->pdo->prepare('SELECT id FROM service_packages WHERE service_id = ? AND name = ? LIMIT 1');
                $ex->execute([(int)$serviceId, $pkg['name']]);
                if ($ex->fetch()) { echo "   [skip] package: {$pkg['name']}\n"; continue; }

                $this->pdo->prepare(
                    'INSERT INTO service_packages (service_id, name, price, features, sort_order, created_at, updated_at)
                     VALUES (?,?,?,?,?,NOW(),NOW())'
                )->execute([(int)$serviceId, $pkg['name'], $pkg['price'], json_encode($pkg['features']), $i]);

                echo "   [ok]   package: {$pkg['name']}\n";
            }
        }
    }

    // ── Galleries ─────────────────────────────────────────────────────────────

    private function seedGalleries(): void
    {
        $galleries = [
            ['title' => 'Weddings',     'slug' => 'weddings',    'description' => 'A curated collection of wedding moments — candid, romantic, and full of emotion.', 'category' => 'weddings',    'cover' => 'gallery-wedding-cover',    'featured' => 1],
            ['title' => 'Portraits',    'slug' => 'portraits',   'description' => 'Natural light portraits that capture authentic character and personality.',           'category' => 'portraits',   'cover' => 'gallery-portrait-cover',   'featured' => 1],
            ['title' => 'Engagements',  'slug' => 'engagements', 'description' => 'Intimate engagement sessions in meaningful locations.',                              'category' => 'engagements', 'cover' => 'gallery-engage-cover',     'featured' => 1],
            ['title' => 'Family',       'slug' => 'family',      'description' => 'Joyful family sessions that celebrate connection and togetherness.',                  'category' => 'family',      'cover' => 'gallery-family-cover',     'featured' => 1],
            ['title' => 'Commercial',   'slug' => 'commercial',  'description' => 'Clean, compelling commercial imagery for leading brands.',                            'category' => 'commercial',  'cover' => 'gallery-commercial-cover', 'featured' => 0],
            ['title' => 'Events',       'slug' => 'events',      'description' => 'Corporate and private event coverage with a documentary eye.',                       'category' => 'events',      'cover' => 'gallery-event-cover',      'featured' => 0],
        ];

        foreach ($galleries as $g) {
            $exists = $this->pdo->prepare('SELECT id FROM galleries WHERE slug = ? AND deleted_at IS NULL LIMIT 1');
            $exists->execute([$g['slug']]);
            if ($exists->fetch()) { echo "   [skip] gallery: {$g['title']}\n"; continue; }

            $this->pdo->prepare(
                'INSERT INTO galleries (title, slug, description, cover_image_id, category, is_featured, is_published, published_at, sort_order, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,1,NOW(),?,NOW(),NOW())'
            )->execute([$g['title'], $g['slug'], $g['description'], $this->mid($g['cover']), $g['category'], $g['featured'], 0]);

            echo "   [ok]   gallery: {$g['title']}\n";
        }
    }

    // ── Gallery media ─────────────────────────────────────────────────────────

    private function seedGalleryMedia(): void
    {
        // Assign photo tiles to galleries
        $assignments = [
            'weddings'    => ['photo-01', 'photo-02', 'photo-03', 'photo-04', 'photo-07', 'photo-08'],
            'portraits'   => ['photo-05', 'photo-06', 'photo-09', 'photo-10'],
            'engagements' => ['photo-03', 'photo-04', 'photo-11', 'photo-12'],
            'family'      => ['photo-01', 'photo-05', 'photo-09', 'photo-11'],
            'commercial'  => ['photo-02', 'photo-06', 'photo-08', 'photo-12'],
            'events'      => ['photo-07', 'photo-10', 'photo-11', 'photo-12'],
        ];

        foreach ($assignments as $gallerySlug => $photoLabels) {
            $row = $this->pdo->prepare('SELECT id FROM galleries WHERE slug = ? LIMIT 1');
            $row->execute([$gallerySlug]);
            $galleryId = $row->fetchColumn();
            if (!$galleryId) continue;

            foreach ($photoLabels as $i => $label) {
                $mediaId = $this->mid($label);
                if (!$mediaId) continue;

                $ex = $this->pdo->prepare('SELECT 1 FROM gallery_media WHERE gallery_id = ? AND media_id = ? LIMIT 1');
                $ex->execute([(int)$galleryId, $mediaId]);
                if ($ex->fetch()) continue;

                $this->pdo->prepare(
                    'INSERT INTO gallery_media (gallery_id, media_id, sort_order) VALUES (?,?,?)'
                )->execute([(int)$galleryId, $mediaId, $i]);
            }

            echo "   [ok]   gallery_media: {$gallerySlug} ← " . count($photoLabels) . " photos\n";
        }
    }

    // ── Blog categories ───────────────────────────────────────────────────────

    private function seedBlogCategories(): void
    {
        $categories = [
            ['name' => 'Behind the Lens',    'slug' => 'behind-the-lens'],
            ['name' => 'Wedding Inspiration', 'slug' => 'wedding-inspiration'],
            ['name' => 'Photography Tips',    'slug' => 'photography-tips'],
            ['name' => 'Studio News',         'slug' => 'studio-news'],
        ];

        foreach ($categories as $cat) {
            $ex = $this->pdo->prepare('SELECT id FROM blog_categories WHERE slug = ? LIMIT 1');
            $ex->execute([$cat['slug']]);
            if ($ex->fetch()) { echo "   [skip] category: {$cat['name']}\n"; continue; }

            $this->pdo->prepare(
                'INSERT INTO blog_categories (name, slug, created_at, updated_at) VALUES (?,?,NOW(),NOW())'
            )->execute([$cat['name'], $cat['slug']]);

            echo "   [ok]   category: {$cat['name']}\n";
        }
    }

    // ── Blog tags ─────────────────────────────────────────────────────────────

    private function seedBlogTags(): void
    {
        $tags = [
            ['name' => 'Wedding',      'slug' => 'wedding'],
            ['name' => 'Lighting',     'slug' => 'lighting'],
            ['name' => 'Golden Hour',  'slug' => 'golden-hour'],
            ['name' => 'Posing',       'slug' => 'posing'],
            ['name' => 'Gear',         'slug' => 'gear'],
            ['name' => 'Natural Light','slug' => 'natural-light'],
            ['name' => 'Editing',      'slug' => 'editing'],
            ['name' => 'Black & White','slug' => 'black-and-white'],
        ];

        foreach ($tags as $tag) {
            $ex = $this->pdo->prepare('SELECT id FROM blog_tags WHERE slug = ? LIMIT 1');
            $ex->execute([$tag['slug']]);
            if ($ex->fetch()) { echo "   [skip] tag: {$tag['name']}\n"; continue; }

            $this->pdo->prepare(
                'INSERT INTO blog_tags (name, slug, created_at) VALUES (?,?,NOW())'
            )->execute([$tag['name'], $tag['slug']]);

            echo "   [ok]   tag: {$tag['name']}\n";
        }
    }

    // ── Blog posts ────────────────────────────────────────────────────────────

    private function seedBlogPosts(): void
    {
        $authorId = $this->pdo->query('SELECT id FROM users WHERE deleted_at IS NULL LIMIT 1')->fetchColumn();

        $catId = fn(string $slug) => $this->pdo->prepare('SELECT id FROM blog_categories WHERE slug = ? LIMIT 1')
            ->execute([$slug]) ? $this->pdo->query("SELECT id FROM blog_categories WHERE slug = '{$slug}' LIMIT 1")->fetchColumn() : null;

        $tagId = fn(string $slug) => $this->pdo->query("SELECT id FROM blog_tags WHERE slug = '{$slug}' LIMIT 1")->fetchColumn();

        $posts = [
            [
                'title'       => 'How We Captured Emma & James\'s Rainy-Day Wedding',
                'slug'        => 'emma-james-rainy-day-wedding',
                'excerpt'     => 'Rain on your wedding day? We say embrace it. Here\'s how a stormy afternoon turned into the most romantic gallery of the year.',
                'body'        => '<p>When Emma texted us the morning of her wedding — "It\'s raining. I\'m not crying, you\'re crying" — we knew we were in for something special.</p><p>Rain creates a kind of magic that sunshine simply cannot. The reflections in the puddles, the muted palette of grey and ivory, the way the light diffuses through cloud cover to produce the most flattering portraits imaginable. We\'ve photographed weddings in glorious sunshine and in October downpours, and honestly? Some of our most compelling galleries have come from the wet ones.</p><h2>The Technical Approach</h2><p>On rainy days we carry extra weather-sealed bodies, keep lenses dry with microfibre cloths tucked in every pocket, and embrace umbrella compositions. Emma\'s cobalt-blue umbrella became the colour accent that tied the entire gallery together.</p><p>We shot wide open (f/1.8 to f/2.8) throughout the ceremony to let in maximum light, then dialled in for the reception where the venue\'s warm Edison bulbs did the heavy lifting.</p><h2>The Result</h2><p>Emma and James now call their wedding album their most prized possession. The rain they dreaded became the defining visual motif of their story.</p>',
                'category'    => 'wedding-inspiration',
                'cover'       => 'blog-01',
                'tags'        => ['wedding', 'natural-light', 'lighting'],
                'mins'        => 5,
                'published_at'=> date('Y-m-d H:i:s', strtotime('-14 days')),
            ],
            [
                'title'       => '5 Lighting Mistakes Photographers Make (and How to Fix Them)',
                'slug'        => '5-lighting-mistakes-photographers',
                'excerpt'     => 'Lighting is the single biggest lever in photography. Here are the five most common mistakes we see — and exactly how to correct them.',
                'body'        => '<p>In photography, light is everything. The subject, the moment, the composition — all of it is secondary to the quality, direction, and colour of light. Over ten years of shooting professionally, we have seen the same lighting mistakes appear again and again.</p><h2>1. Relying on On-Camera Flash as a Primary Source</h2><p>Direct on-camera flash produces flat, unflattering light that eliminates shadow depth and creates red-eye. Use it as fill only. Your primary source should be window light, a strobe off-camera, or a reflector.</p><h2>2. Shooting in Harsh Midday Sun</h2><p>Midday sun creates deep, unflattering shadows under the eyes, nose, and chin. If you must shoot midday, find open shade or use a large diffuser. Save your outdoor portraits for the golden hour — the hour after sunrise and before sunset.</p><h2>3. Ignoring Background Light</h2><p>Your subject can be perfectly lit while the background is blown out or completely black. Always expose for the background first, then add light to your subject until the two are balanced.</p><h2>4. Using a Colour Temperature that Clashes with Ambient Light</h2><p>Mixed colour temperatures — tungsten ambient with a daylight-balanced strobe, for instance — produce unflattering colour casts. Match your flash or continuous light to the ambient colour temperature, or use gels to correct it.</p><h2>5. Not Embracing Direction</h2><p>Light direction determines mood. Side-lighting creates drama and depth. Back-lighting creates a romantic glow. Front-lighting is safe but flat. Learn to see and sculpt with direction, and your images will immediately jump to a new level.</p>',
                'category'    => 'photography-tips',
                'cover'       => 'blog-02',
                'tags'        => ['lighting', 'natural-light', 'gear'],
                'mins'        => 7,
                'published_at'=> date('Y-m-d H:i:s', strtotime('-28 days')),
            ],
            [
                'title'       => 'Why We Switched to Prime Lenses for Portrait Work',
                'slug'        => 'switched-to-prime-lenses-portrait',
                'excerpt'     => 'Two years ago we sold our 24-70mm zoom and bought three primes. Here\'s what changed — for better and for worse.',
                'body'        => '<p>The 24-70mm f/2.8 zoom lens is the Swiss Army knife of photography. It is what most professional photographers reach for first, and for good reason: it covers the most useful focal lengths, it is fast enough for most situations, and it is a single lens where you might otherwise carry three.</p><p>So why did we sell ours two years ago and replace it with a 35mm, a 50mm, and an 85mm prime?</p><h2>Image Quality at the Pixel Level</h2><p>Prime lenses, without exception, out-resolve zoom lenses of comparable cost at their equivalent focal lengths. The 85mm f/1.4 produces a rendering — bokeh quality, micro-contrast, three-dimensional "pop" — that no zoom can match.</p><h2>Maximum Aperture</h2><p>Our primes go to f/1.2 and f/1.4. The zoom we replaced was f/2.8. In low light wedding reception situations, that is a full stop and a third — the difference between ISO 1600 and ISO 4000. That matters.</p><h2>The Trade-Off: Versatility</h2><p>We will not pretend there is no trade-off. Prime lenses require you to zoom with your feet. Crowded venues where you cannot move freely make primes frustrating. For event coverage, we now carry a 24-70 as our second body lens for exactly those situations.</p>',
                'category'    => 'photography-tips',
                'cover'       => 'blog-03',
                'tags'        => ['gear', 'posing', 'editing'],
                'mins'        => 6,
                'published_at'=> date('Y-m-d H:i:s', strtotime('-42 days')),
            ],
            [
                'title'       => 'What to Wear for Your Portrait Session',
                'slug'        => 'what-to-wear-portrait-session',
                'excerpt'     => 'Clothing choices can make or break a portrait session. Here is our definitive guide to dressing confidently for the camera.',
                'body'        => '<p>We send a wardrobe guide to every portrait client. And yet, "I don\'t know what to wear" is still the number one concern we hear on session day. So here is the definitive version — everything we recommend in one place.</p><h2>Stick to a Palette, Not a Colour Match</h2><p>If you are photographing as a couple or a family, do not all wear the same colour. Instead, choose two or three complementary tones and dress within that palette. Navy, cream, and soft terracotta work beautifully together. Black and white with a single colour accent is classic for a reason.</p><h2>Avoid Logos and Large Patterns</h2><p>Logos date your photos faster than anything. Bold patterns — stripes, checks, busy florals — draw the eye away from faces and can create visual vibration in high-resolution images. Solid colours and subtle textures photograph best.</p><h2>Fit and Comfort Matter</h2><p>You will be posing, moving, sitting, laughing. Wear clothes that fit well and that you feel genuinely comfortable in. If you spend the session tugging at your hem or pulling your collar, it will show.</p><h2>Layer for Variety</h2><p>A jacket or blazer gives you an instant wardrobe change. Start a session with layers and remove them as the shoot progresses — it creates visual variety and a natural arc of warmth and informality.</p>',
                'category'    => 'behind-the-lens',
                'cover'       => 'blog-04',
                'tags'        => ['posing', 'natural-light'],
                'mins'        => 4,
                'published_at'=> date('Y-m-d H:i:s', strtotime('-60 days')),
            ],
            [
                'title'       => 'The Golden Hour — Our Favourite Time to Shoot',
                'slug'        => 'golden-hour-our-favourite-time',
                'excerpt'     => 'That magical hour before sunset transforms ordinary locations into extraordinary ones. Here\'s how we make the most of it.',
                'body'        => '<p>If you have looked through our portfolio and noticed that most of our outdoor portraits seem to glow with a warm, directional light — that is golden hour at work. The hour before sunset (and after sunrise) produces light of a quality that cannot be replicated artificially at a proportionate cost.</p><p>The sun\'s position at a low angle means its light travels through more of the Earth\'s atmosphere, scattering the blue wavelengths and leaving the warm reds, oranges, and yellows. The result is a rich, flattering directional light that is soft enough to be forgiving while still having enough direction to create depth and dimension.</p><h2>Planning Golden Hour Shoots</h2><p>We use the Photopills app to predict exactly where the sun will set at any location on any date. This lets us scout positions in advance and arrive with a plan. Time is short — golden hour is typically only 20-40 minutes of truly ideal light — so preparation is everything.</p><h2>Exposing Correctly</h2><p>Golden hour light is powerful. We typically expose for the highlights and fill the shadows slightly with a reflector or small fill flash. This prevents blown highlights while maintaining the warm, dimensional quality of the light.</p>',
                'category'    => 'photography-tips',
                'cover'       => 'blog-01',
                'tags'        => ['golden-hour', 'natural-light', 'lighting'],
                'mins'        => 5,
                'published_at'=> date('Y-m-d H:i:s', strtotime('-75 days')),
            ],
            [
                'title'       => 'We\'re Expanding: New Studio Space Opening This Autumn',
                'slug'        => 'new-studio-space-opening-autumn',
                'excerpt'     => 'Exciting news from Mesh Photography — we\'re opening a dedicated studio space this autumn with a north-light wall and in-house printing lab.',
                'body'        => '<p>We have been in our current space for five years and it has served us incredibly well. But as the business has grown — more portrait clients, more commercial shoots, more complex productions — we have outgrown it.</p><p>This autumn we are moving into a purpose-built studio with 2,400 square feet of floor space, a north-facing skylight wall that floods the space with consistent, colour-accurate natural light all day, and a dedicated printing and framing lab so we can produce fine art prints in-house for the first time.</p><h2>What This Means for Clients</h2><p>Portrait sessions will now be available entirely in-studio with full lighting control — ideal for professional headshots, corporate portraits, and product photography. We will also be hosting monthly open studio days where you can come and see the space, meet the team, and view our print work in person.</p><h2>Opening Date</h2><p>We open our doors on 15th October. We will be hosting a launch event with complimentary mini portrait sessions — follow us on Instagram for ticket information.</p>',
                'category'    => 'studio-news',
                'cover'       => 'blog-02',
                'tags'        => ['editing', 'black-and-white'],
                'mins'        => 3,
                'published_at'=> date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
        ];

        foreach ($posts as $p) {
            $exists = $this->pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? AND deleted_at IS NULL LIMIT 1');
            $exists->execute([$p['slug']]);
            if ($exists->fetch()) { echo "   [skip] post: {$p['title']}\n"; continue; }

            $catSlug   = $p['category'];
            $catResult = $this->pdo->query("SELECT id FROM blog_categories WHERE slug = '{$catSlug}' LIMIT 1");
            $catIdVal  = $catResult ? $catResult->fetchColumn() : null;

            $this->pdo->prepare(
                'INSERT INTO blog_posts (title, slug, excerpt, body, cover_image_id, author_id, category_id, is_published, published_at, reading_time_mins, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,1,?,?,NOW(),NOW())'
            )->execute([
                $p['title'], $p['slug'], $p['excerpt'], $p['body'],
                $this->mid($p['cover']), $authorId, $catIdVal ?: null,
                $p['published_at'], $p['mins'],
            ]);

            $postId = (int) $this->pdo->lastInsertId();

            foreach ($p['tags'] as $tagSlug) {
                $tagResult = $this->pdo->query("SELECT id FROM blog_tags WHERE slug = '{$tagSlug}' LIMIT 1");
                $tagIdVal  = $tagResult ? $tagResult->fetchColumn() : null;
                if ($tagIdVal) {
                    $this->pdo->prepare('INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?,?)')
                        ->execute([$postId, (int)$tagIdVal]);
                }
            }

            echo "   [ok]   post: {$p['title']}\n";
        }
    }

    // ── Testimonials ──────────────────────────────────────────────────────────

    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'client_name'  => 'Sophie & David Hartley',
                'client_title' => 'Wedding — June 2025',
                'quote'        => 'We cannot overstate how incredible our experience with Mesh Photography was. From the engagement shoot to the final gallery delivery, every interaction was thoughtful, professional, and joyful. Our wedding album brings us to tears every time we open it.',
                'rating'       => 5,
                'avatar'       => 'avatar-01',
                'sort_order'   => 0,
            ],
            [
                'client_name'  => 'Marcus Chen',
                'client_title' => 'Corporate Headshots',
                'quote'        => 'I needed updated professional headshots for a speaker profile and had been putting it off for a year because I find having my photograph taken deeply uncomfortable. The team made me feel completely at ease, and the results were genuinely stunning — I actually look like myself, which is rare.',
                'rating'       => 5,
                'avatar'       => 'avatar-02',
                'sort_order'   => 1,
            ],
            [
                'client_name'  => 'The Whitmore Family',
                'client_title' => 'Family Portrait Session',
                'quote'        => 'We have three children under seven. Wrangling them for photos always felt like herding cats. Somehow the Mesh Photography team made it magical. The images are full of genuine laughter and energy — exactly who our family actually is.',
                'rating'       => 5,
                'avatar'       => 'avatar-03',
                'sort_order'   => 2,
            ],
            [
                'client_name'  => 'Priya Nair',
                'client_title' => 'Brand Photography',
                'quote'        => 'As a small business owner, I was nervous to invest in professional brand photography. It transformed our website and social media completely. We have received so many compliments from clients who say the images communicate exactly who we are before they even speak to us.',
                'rating'       => 5,
                'avatar'       => 'avatar-04',
                'sort_order'   => 3,
            ],
            [
                'client_name'  => 'James & Olivia Pemberton',
                'client_title' => 'Wedding — September 2024',
                'quote'        => 'The team at Mesh Photography have an extraordinary ability to be everywhere and nowhere at the same time. Our guests barely noticed them, yet they captured every meaningful moment. The gallery is a masterpiece.',
                'rating'       => 5,
                'avatar'       => 'avatar-05',
                'sort_order'   => 4,
            ],
            [
                'client_name'  => 'Aisha Okonkwo',
                'client_title' => 'Engagement Session',
                'quote'        => 'We booked an engagement session to get comfortable in front of the camera before our wedding, and it absolutely worked. The images are beautiful and timeless, and we have already ordered a large print for our home.',
                'rating'       => 5,
                'avatar'       => 'avatar-06',
                'sort_order'   => 5,
            ],
        ];

        foreach ($testimonials as $t) {
            $exists = $this->pdo->prepare('SELECT id FROM testimonials WHERE client_name = ? AND deleted_at IS NULL LIMIT 1');
            $exists->execute([$t['client_name']]);
            if ($exists->fetch()) { echo "   [skip] testimonial: {$t['client_name']}\n"; continue; }

            $this->pdo->prepare(
                'INSERT INTO testimonials (client_name, client_title, quote, rating, avatar_id, is_featured, is_published, sort_order, created_at, updated_at)
                 VALUES (?,?,?,?,?,1,1,?,NOW(),NOW())'
            )->execute([$t['client_name'], $t['client_title'], $t['quote'], $t['rating'], $this->mid($t['avatar']), $t['sort_order']]);

            echo "   [ok]   testimonial: {$t['client_name']}\n";
        }
    }

    // ── CMS pages ─────────────────────────────────────────────────────────────

    private function seedPages(): void
    {
        $pages = [
            [
                'title'  => 'About',
                'slug'   => 'about',
                'body'   => '<h1>About Mesh Photography</h1><p>We are a boutique photography studio specialising in weddings, portraits, and commercial photography. Founded in 2015, we have spent a decade building a practice grounded in one simple belief: great photography is about connection.</p><p>When people feel seen, they relax. When they relax, they are authentic. And authenticity is where the most powerful images live — not in perfectly arranged posing, but in the unguarded moment, the spontaneous laugh, the quiet glance between two people who love each other.</p><h2>The Team</h2><p>Our lead photographer has over ten years of professional experience and has been published in <em>Vogue</em>, <em>Tatler</em>, and <em>The Sunday Times Style</em>. We are supported by a small, trusted team of second photographers, editors, and studio coordinators.</p><h2>Our Philosophy</h2><p>We are storytellers. Every shoot begins with a conversation — about you, your story, the moments you most want to preserve. We listen before we lift a camera. The resulting images are a reflection of who you actually are, not a performance of who you think you should be.</p><p>We work with a limited number of clients each year so that every project receives the attention it deserves. If you are considering booking, please reach out early — our diary fills quickly, especially for summer and autumn dates.</p>',
            ],
            [
                'title'  => 'Privacy Policy',
                'slug'   => 'privacy-policy',
                'body'   => '<h1>Privacy Policy</h1><p><strong>Last updated: January 2026</strong></p><p>Mesh Photography ("we", "us", "our") is committed to protecting your personal information. This policy explains what data we collect, how we use it, and your rights regarding that data.</p><h2>Data We Collect</h2><p>We collect information you provide directly when you submit an enquiry or booking request: your name, email address, phone number, and the details of your event. We also collect standard server logs including IP addresses and browser information.</p><h2>How We Use Your Data</h2><p>We use your information exclusively to respond to your enquiry, manage your booking, and deliver your final gallery. We do not sell or share your personal data with third parties for marketing purposes.</p><h2>Data Retention</h2><p>Enquiry data is retained for 24 months. Client galleries are retained for 12 months post-delivery unless a longer archive service has been purchased.</p><h2>Your Rights</h2><p>Under GDPR, you have the right to access, correct, or delete your personal data at any time. Please contact us at hello@meshphoto.com to exercise these rights.</p>',
            ],
            [
                'title'  => 'Terms of Service',
                'slug'   => 'terms-of-service',
                'body'   => '<h1>Terms of Service</h1><p><strong>Last updated: January 2026</strong></p><h2>Booking and Deposit</h2><p>All bookings are confirmed upon receipt of a signed contract and a non-refundable deposit of 25% of the total fee. The date is not held until both the contract and deposit have been received.</p><h2>Payment</h2><p>The remaining balance is due 14 days before the event date. For portrait sessions, full payment is required at the time of booking.</p><h2>Cancellation Policy</h2><p>Cancellations made more than 90 days before the event will receive a full refund of monies paid beyond the deposit. Cancellations within 90 days forfeit all payments made. We strongly recommend event insurance.</p><h2>Image Delivery</h2><p>Edited galleries are delivered within 6 weeks of the event date for weddings, and within 2 weeks for portrait sessions. Rush delivery may be arranged at additional cost.</p><h2>Copyright</h2><p>Mesh Photography retains copyright of all images. You are granted a personal use licence for printing and sharing. Commercial use of images requires a separate commercial licence.</p>',
            ],
        ];

        foreach ($pages as $page) {
            $exists = $this->pdo->prepare('SELECT id FROM pages WHERE slug = ? AND deleted_at IS NULL LIMIT 1');
            $exists->execute([$page['slug']]);
            $row = $exists->fetch();

            if ($row) {
                // Update body content even if page exists (idempotent)
                $this->pdo->prepare('UPDATE pages SET body=?, is_published=1, updated_at=NOW() WHERE id=?')
                    ->execute([$page['body'], (int)$row['id']]);
                echo "   [upd]  page: {$page['title']}\n";
            } else {
                $this->pdo->prepare(
                    'INSERT INTO pages (title, slug, body, is_published, created_at, updated_at) VALUES (?,?,?,1,NOW(),NOW())'
                )->execute([$page['title'], $page['slug'], $page['body']]);
                echo "   [ok]   page: {$page['title']}\n";
            }
        }
    }

    // ── Inquiries ─────────────────────────────────────────────────────────────

    private function seedInquiries(): void
    {
        $count = (int) $this->pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
        if ($count > 0) { echo "   [skip] inquiries (already seeded)\n"; return; }

        $serviceId = $this->pdo->query("SELECT id FROM services WHERE slug = 'wedding-photography' LIMIT 1")->fetchColumn();

        $inquiries = [
            ['name' => 'Charlotte & Ben Williams', 'email' => 'charlotte.williams@example.com', 'subject' => 'Wedding Photography — August 2026', 'message' => 'Hi, we are getting married on 22nd August 2026 at Broughton Hall in Yorkshire. We are looking for a photographer who can cover from bridal preparations through to the first hour of the reception — approximately 10 hours. Could you please let us know your availability and pricing?', 'service_id' => $serviceId],
            ['name' => 'Thomas Adeyemi', 'email' => 'thomas.adeyemi@example.com', 'subject' => 'Corporate Headshots for 12 people', 'message' => 'We need to update the headshots of our 12-person leadership team for our new website. We would ideally like to do this in one day at our London office. Could you provide a quote and some availability options?', 'service_id' => null],
            ['name' => 'Natasha & Igor Petrov', 'email' => 'natasha.petrov@example.com', 'subject' => 'Engagement shoot enquiry', 'message' => 'We got engaged last month and would love to book an engagement session. We were thinking somewhere in the countryside — perhaps the Peak District or Cotswolds. We are completely flexible on dates. What would you recommend?', 'service_id' => null],
            ['name' => 'Bloom & Co Interiors', 'email' => 'studio@bloomandco.example.com', 'subject' => 'Brand photography for interior design firm', 'message' => 'We are relaunching our website in April and need high-quality images of our recently completed projects plus team portraits. We have four residential projects we would like photographed across two days. Could we arrange a call to discuss?', 'service_id' => null],
            ['name' => 'Rebecca Osei', 'email' => 'rebecca.osei@example.com', 'subject' => 'Family portrait session', 'message' => 'I would love to book a family portrait session for my husband, myself, and our two daughters (ages 5 and 8). We are flexible on dates and could either come to your studio or meet somewhere outdoors. We are particularly drawn to the natural light style in your portfolio.', 'service_id' => null],
        ];

        foreach ($inquiries as $inq) {
            $this->pdo->prepare(
                'INSERT INTO inquiries (name, email, subject, message, service_id, is_read, created_at, updated_at) VALUES (?,?,?,?,?,0,NOW(),NOW())'
            )->execute([$inq['name'], $inq['email'], $inq['subject'], $inq['message'], $inq['service_id']]);
        }

        echo '   [ok]   ' . count($inquiries) . " inquiries seeded\n";
    }

    // ── Booking requests ──────────────────────────────────────────────────────

    private function seedBookingRequests(): void
    {
        $count = (int) $this->pdo->query('SELECT COUNT(*) FROM booking_requests')->fetchColumn();
        if ($count > 0) { echo "   [skip] booking_requests (already seeded)\n"; return; }

        $weddingId   = $this->pdo->query("SELECT id FROM services WHERE slug = 'wedding-photography' LIMIT 1")->fetchColumn();
        $portraitId  = $this->pdo->query("SELECT id FROM services WHERE slug = 'portrait-sessions' LIMIT 1")->fetchColumn();
        $eventId     = $this->pdo->query("SELECT id FROM services WHERE slug = 'event-photography' LIMIT 1")->fetchColumn();

        $bookings = [
            ['name' => 'Amelia & George Thornton', 'email' => 'amelia.thornton@example.com', 'phone' => '+44 7700 900123', 'service_id' => $weddingId,  'event_date' => date('Y-m-d', strtotime('+6 months')), 'event_type' => 'Wedding',         'location' => 'Ashdown House, Sussex',    'guest_count' => 120, 'budget' => 3500.00, 'notes' => 'Church ceremony at 1pm, reception until midnight.',         'status' => 'new'],
            ['name' => 'Daniel Park',              'email' => 'daniel.park@example.com',     'phone' => '+44 7700 900456', 'service_id' => $portraitId, 'event_date' => date('Y-m-d', strtotime('+3 weeks')), 'event_type' => 'Headshot',        'location' => 'Studio preferred',         'guest_count' => null,'budget' => 650.00,  'notes' => 'Need 3 final selects for LinkedIn and press profile.',     'status' => 'contacted'],
            ['name' => 'Horizon Tech Ltd',         'email' => 'events@horizontech.example.com','phone' =>'+44 20 7946 0321','service_id' => $eventId,   'event_date' => date('Y-m-d', strtotime('+5 weeks')), 'event_type' => 'Product Launch',  'location' => 'The Shard, London',        'guest_count' => 80,  'budget' => 1100.00, 'notes' => 'Evening launch event, 6pm-10pm. Need social media exports same night.','status' => 'booked'],
            ['name' => 'Francesca Marini',         'email' => 'f.marini@example.com',        'phone' => '+44 7700 900789', 'service_id' => $portraitId, 'event_date' => date('Y-m-d', strtotime('+10 weeks')),'event_type' => 'Maternity',       'location' => 'Outdoor, Hampstead Heath', 'guest_count' => null,'budget' => 650.00,  'notes' => 'Currently 28 weeks. Would like to shoot at 34-35 weeks.', 'status' => 'new'],
        ];

        foreach ($bookings as $b) {
            $this->pdo->prepare(
                'INSERT INTO booking_requests (name, email, phone, service_id, event_date, event_type, location, guest_count, budget, notes, status, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())'
            )->execute([$b['name'], $b['email'], $b['phone'], $b['service_id'], $b['event_date'], $b['event_type'], $b['location'], $b['guest_count'], $b['budget'], $b['notes'], $b['status']]);
        }

        echo '   [ok]   ' . count($bookings) . " booking requests seeded\n";
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function step(string $label, callable $fn): void
    {
        echo "\n  [{$label}]\n";
        $fn();
    }
}
