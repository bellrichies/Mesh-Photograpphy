<?php
declare(strict_types=1);

return new class {
    public string $description = 'Seed the default About CMS page and structured sections';

    public function up(\PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        $pageId = $this->ensurePage($pdo, [
            'title' => 'About',
            'slug' => 'about',
            'body' => null,
            'seo_title' => 'About | Mesh Photography',
            'seo_description' => 'Learn the story behind Mesh Photography, our approach, values, and the people behind the lens.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sections = [
            ['hero_title', 'text', 'Hero Title', 'About', 0],
            ['hero_subtitle', 'text', 'Hero Subtitle', 'Editorial imagery for couples, founders, and families - crafted with intention, delivered with warmth.', 1],
            ['hero_image', 'image', 'Hero Image', null, 2],
            ['story_heading', 'text', 'Story Heading', 'Born from a love of storytelling through light', 3],
            [
                'story_body',
                'rich_text',
                'Brand Story',
                '<p>Mesh Photography was born from a deep love of storytelling through images. We believe every moment, whether grand or quiet, carries meaning, and our job is to preserve it with honesty and care.</p>
<p>Based in Washington, DC, we travel wherever love, life, and light take us. From intimate elopements in the mountains to editorial campaigns downtown, we bring the same craft and attention to every shoot.</p>
<p>We are not interested in stiff poses or forced smiles. We create space for genuine moments to emerge, then we are there to catch them. Documentary instincts. Editorial sensibility. Timeless results.</p>',
                4,
            ],
            ['mission_heading', 'text', 'Mission Heading', 'To freeze fleeting moments in their most honest form', 5],
            [
                'mission_body',
                'rich_text',
                'Mission Body',
                '<p>Every image we deliver is a commitment to authenticity. We push past performance and manufactured perfection to find the real story: the glance, the laugh, the quiet in-between. That is where life actually lives.</p>',
                6,
            ],
            ['vision_heading', 'text', 'Vision Heading', 'Images that outlast the moment they were taken', 7],
            [
                'vision_body',
                'rich_text',
                'Vision Body',
                '<p>We envision a body of work that families will pass down across generations: images free of fleeting trends and full of enduring emotion. Photography as heirloom, not content.</p>',
                8,
            ],
            [
                'approach_body',
                'rich_text',
                'Our Approach',
                '<p>Before every session, we invest in understanding you: your dynamic, your surroundings, and your story. That investment pays off in every frame.</p>
<p>On the day, we guide with a light touch. Prompts, not poses. Movement, not stiffness. You will forget the camera is there. That is when the best images happen.</p>',
                9,
            ],
            ['team_heading', 'text', 'Team Heading', 'The people behind the lens', 10],
            [
                'team_body',
                'rich_text',
                'Team Body',
                '<p>Our studio pairs careful preparation with calm direction so every client feels seen, prepared, and at ease in front of the camera.</p>',
                11,
            ],
            ['clients_heading', 'text', 'Clients Heading', 'Trusted by couples, families, and founders', 12],
            [
                'clients_body',
                'rich_text',
                'Clients Body',
                '<p>We work with clients who value intentional imagery, thoughtful communication, and a photography experience that feels personal from first conversation to final delivery.</p>',
                13,
            ],
            ['cta_heading', 'text', 'CTA Heading', "Let's create something lasting together", 14],
            ['cta_body', 'text', 'CTA Body', "Whether you have a clear vision or you are starting from scratch, we are here to guide you every step of the way.", 15],
            [
                'stats',
                'json',
                'Stats Bar',
                json_encode([
                    ['value' => '8+', 'label' => 'Years of Experience'],
                    ['value' => '400+', 'label' => 'Sessions Delivered'],
                    ['value' => '12', 'label' => 'Cities Worked In'],
                    ['value' => '98%', 'label' => 'Client Satisfaction'],
                ], JSON_THROW_ON_ERROR),
                16,
            ],
        ];

        $this->insertSections($pdo, $pageId, $sections, $now);

        echo "[Migration] Seeded default About CMS page sections.\n";
    }

    public function down(\PDO $pdo): void
    {
        $row = $pdo->query("SELECT id FROM pages WHERE slug = 'about' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }

        $pdo->prepare('DELETE FROM page_sections WHERE page_id = ?')->execute([$row['id']]);
        $pdo->prepare("DELETE FROM pages WHERE slug = 'about'")->execute();
    }

    private function ensurePage(\PDO $pdo, array $page): int
    {
        $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = ? LIMIT 1');
        $stmt->execute([$page['slug']]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($existing) {
            return (int) $existing['id'];
        }

        $pdo->prepare('
            INSERT INTO pages (title, slug, body, is_published, seo_title, seo_description, created_at, updated_at)
            VALUES (:title, :slug, :body, 1, :seo_title, :seo_description, :created_at, :updated_at)
        ')->execute($page);

        return (int) $pdo->lastInsertId();
    }

    private function insertSections(\PDO $pdo, int $pageId, array $sections, string $now): void
    {
        $stmt = $pdo->prepare('
            INSERT IGNORE INTO page_sections
                (page_id, section_key, section_type, label, content, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');

        foreach ($sections as [$key, $type, $label, $content, $sortOrder]) {
            $stmt->execute([$pageId, $key, $type, $label, $content, $sortOrder, $now, $now]);
        }
    }
};
