<?php
declare(strict_types=1);

return new class {
    public string $description = 'Seed default CMS-managed pages, legal sections, and social settings';

    public function up(\PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        $pages = [
            'terms' => [
                'title' => 'Terms',
                'seo_title' => 'Terms | Mesh Photography',
                'seo_description' => 'Read the terms for using Mesh Photography services and website.',
                'sections' => [
                    ['hero_title', 'text', 'Hero Title', 'Terms', '0'],
                    ['hero_subtitle', 'text', 'Hero Subtitle', 'The terms that govern our website, bookings, image usage, and client communication.', '1'],
                    ['policy_intro', 'rich_text', 'Policy Introduction', '<p>Welcome to Mesh Photography. By accessing our website or requesting our services, you agree to the terms below. Individual photography engagements may include a separate signed agreement that takes precedence for that engagement.</p>', '2'],
                    ['policy_content', 'rich_text', 'Policy Content', '<h2>Services</h2><p>Mesh Photography provides professional photography services including editorial, portrait, wedding, family, and corporate photography. Booking requests submitted through the website are subject to availability and confirmation.</p><h2>Intellectual Property</h2><p>Unless explicitly transferred in writing, photographs, images, and content produced by Mesh Photography remain the intellectual property of Mesh Photography. Clients receive usage rights described in their service agreement.</p><h2>Bookings and Cancellations</h2><p>Deposits, rescheduling, delivery timelines, cancellation terms, and usage permissions are governed by the agreement attached to each confirmed session.</p><h2>Limitation of Liability</h2><p>Mesh Photography is not liable for indirect, incidental, or consequential damages arising from website use or services beyond the fees paid for the applicable service.</p><h2>Changes to These Terms</h2><p>We may update these terms as our services evolve. Continued use of the website after updates constitutes acceptance of the revised terms.</p>', '3'],
                    ['policy_contact', 'rich_text', 'Contact Block', '<p>Questions about these terms? Contact us at <a href="mailto:hello@meshphoto.com">hello@meshphoto.com</a>.</p>', '4'],
                ],
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'seo_title' => 'Privacy Policy | Mesh Photography',
                'seo_description' => 'Learn how Mesh Photography collects, uses, and protects personal information.',
                'sections' => [
                    ['hero_title', 'text', 'Hero Title', 'Privacy Policy', '0'],
                    ['hero_subtitle', 'text', 'Hero Subtitle', 'How we collect, use, protect, and retain personal information shared with our studio.', '1'],
                    ['policy_intro', 'rich_text', 'Policy Introduction', '<p>We respect your privacy and are committed to protecting the personal information you share when visiting our website, submitting a form, or booking photography services.</p>', '2'],
                    ['policy_content', 'rich_text', 'Policy Content', '<h2>Information We Collect</h2><p>We may collect details you provide directly, including name, email address, phone number, event details, project notes, and messages sent through our contact or booking forms.</p><h2>How We Use Information</h2><p>We use this information to respond to inquiries, manage booking requests, deliver services, send service-related communication, improve our website, and maintain business records.</p><h2>Data Sharing</h2><p>We do not sell personal information. We may share limited information with trusted service providers only when needed to operate the website, deliver client services, or comply with legal obligations.</p><h2>Security</h2><p>We use reasonable technical and organisational safeguards to protect personal information from unauthorised access, disclosure, alteration, or destruction.</p><h2>Your Choices</h2><p>You may request access, correction, or deletion of your personal information where applicable by contacting us.</p>', '3'],
                    ['policy_contact', 'rich_text', 'Contact Block', '<p>Questions about privacy? Contact us at <a href="mailto:hello@meshphoto.com">hello@meshphoto.com</a>.</p>', '4'],
                ],
            ],
            'cookie-policy' => [
                'title' => 'Cookie Policy',
                'seo_title' => 'Cookie Policy | Mesh Photography',
                'seo_description' => 'Learn how Mesh Photography uses cookies and similar technologies.',
                'sections' => [
                    ['hero_title', 'text', 'Hero Title', 'Cookie Policy', '0'],
                    ['hero_subtitle', 'text', 'Hero Subtitle', 'How cookies and similar technologies support site functionality and measurement.', '1'],
                    ['policy_intro', 'rich_text', 'Policy Introduction', '<p>This Cookie Policy explains how Mesh Photography may use cookies and similar technologies when you visit our website.</p>', '2'],
                    ['policy_content', 'rich_text', 'Policy Content', '<h2>What Are Cookies?</h2><p>Cookies are small text files stored on your device by websites you visit. They help websites remember preferences, support core functionality, and understand site usage.</p><h2>How We Use Cookies</h2><p>We may use essential cookies for website functionality and analytics cookies to understand how visitors use the site. We do not use cookies to sell personal information.</p><h2>Managing Cookies</h2><p>You can manage or disable cookies through your browser settings. Disabling some cookies may affect website functionality.</p><h2>Updates</h2><p>We may update this Cookie Policy as our website and tools change.</p>', '3'],
                    ['policy_contact', 'rich_text', 'Contact Block', '<p>Questions about cookies? Contact us at <a href="mailto:hello@meshphoto.com">hello@meshphoto.com</a>.</p>', '4'],
                ],
            ],
        ];

        foreach ($pages as $slug => $page) {
            $pageId = $this->ensurePage($pdo, $slug, $page, $now);
            $this->insertSections($pdo, $pageId, $page['sections'], $now);
        }

        $this->ensureSocialSettings($pdo);

        echo "[Migration] Seeded CMS defaults for About, Terms, Privacy Policy, Cookie Policy, and social settings.\n";
    }

    public function down(\PDO $pdo): void
    {
        $keys = [
            'social_twitter',
            'social_x',
            'social_youtube',
            'social_pinterest',
            'social_linkedin',
            'social_tiktok',
        ];

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $pdo->prepare("DELETE FROM site_settings WHERE key_name IN ({$placeholders}) AND (value IS NULL OR value = '')")
            ->execute($keys);
    }

    private function ensurePage(\PDO $pdo, string $slug, array $page, string $now): int
    {
        $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$existing) {
            $pdo->prepare('
                INSERT INTO pages
                    (title, slug, body, is_published, seo_title, seo_description, template, seo_robots, created_at, updated_at)
                VALUES
                    (:title, :slug, :body, 1, :seo_title, :seo_description, :template, :seo_robots, :created_at, :updated_at)
            ')->execute([
                'title' => $page['title'],
                'slug' => $slug,
                'body' => null,
                'seo_title' => $page['seo_title'],
                'seo_description' => $page['seo_description'],
                'template' => $slug === 'about' ? 'about' : 'policy',
                'seo_robots' => 'index, follow',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return (int) $pdo->lastInsertId();
        }

        $pdo->prepare('
            UPDATE pages
            SET
                title = COALESCE(NULLIF(title, \'\'), :title),
                seo_title = COALESCE(NULLIF(seo_title, \'\'), :seo_title),
                seo_description = COALESCE(NULLIF(seo_description, \'\'), :seo_description),
                template = COALESCE(NULLIF(template, \'\'), :template),
                seo_robots = COALESCE(NULLIF(seo_robots, \'\'), \'index, follow\'),
                is_published = 1,
                updated_at = updated_at
            WHERE id = :id
        ')->execute([
            'title' => $page['title'],
            'seo_title' => $page['seo_title'],
            'seo_description' => $page['seo_description'],
            'template' => $slug === 'about' ? 'about' : 'policy',
            'id' => (int) $existing['id'],
        ]);

        return (int) $existing['id'];
    }

    private function insertSections(\PDO $pdo, int $pageId, array $sections, string $now): void
    {
        $stmt = $pdo->prepare('
            INSERT IGNORE INTO page_sections
                (page_id, section_key, section_type, label, content, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');

        foreach ($sections as [$key, $type, $label, $content, $sortOrder]) {
            $stmt->execute([$pageId, $key, $type, $label, $content, (int) $sortOrder, $now, $now]);
        }
    }

    private function ensureSocialSettings(\PDO $pdo): void
    {
        $settings = [
            ['social_twitter', '', 'string', 'social'],
            ['social_x', '', 'string', 'social'],
            ['social_youtube', '', 'string', 'social'],
            ['social_pinterest', '', 'string', 'social'],
            ['social_linkedin', '', 'string', 'social'],
            ['social_tiktok', '', 'string', 'social'],
        ];

        $stmt = $pdo->prepare('
            INSERT IGNORE INTO site_settings (key_name, value, type, group_name)
            VALUES (?, ?, ?, ?)
        ');

        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
    }
};
