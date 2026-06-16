<?php
declare(strict_types=1);

return new class {
    public string $description = 'Add CMS image slots to the About page';

    /**
     * @var array<string, array{0: string, 1: string, 2: int}>
     */
    private array $imageSections = [
        'story_image' => ['image', 'Story Image', 5],
        'mission_image' => ['image', 'Mission Image', 8],
        'vision_image' => ['image', 'Vision Image', 11],
        'approach_image' => ['image', 'Approach Image', 13],
        'team_image' => ['image', 'Team Image', 16],
        'clients_image' => ['image', 'Clients Image', 19],
    ];

    /**
     * @var array<string, int>
     */
    private array $sortOrder = [
        'hero_title' => 0,
        'hero_subtitle' => 1,
        'hero_image' => 2,
        'story_heading' => 3,
        'story_body' => 4,
        'story_image' => 5,
        'mission_heading' => 6,
        'mission_body' => 7,
        'mission_image' => 8,
        'vision_heading' => 9,
        'vision_body' => 10,
        'vision_image' => 11,
        'approach_body' => 12,
        'approach_image' => 13,
        'team_heading' => 14,
        'team_body' => 15,
        'team_image' => 16,
        'clients_heading' => 17,
        'clients_body' => 18,
        'clients_image' => 19,
        'cta_heading' => 20,
        'cta_body' => 21,
        'stats' => 22,
    ];

    public function up(\PDO $pdo): void
    {
        $pageId = $this->aboutPageId($pdo);
        if ($pageId === null) {
            return;
        }

        $insert = $pdo->prepare('
            INSERT IGNORE INTO page_sections
                (page_id, section_key, section_type, label, content, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, NULL, ?, NOW(), NOW())
        ');

        foreach ($this->imageSections as $key => [$type, $label, $sortOrder]) {
            $insert->execute([$pageId, $key, $type, $label, $sortOrder]);
        }

        $updateImageSection = $pdo->prepare('
            UPDATE page_sections
            SET section_type = ?, label = ?, sort_order = ?
            WHERE page_id = ? AND section_key = ?
        ');

        foreach ($this->imageSections as $key => [$type, $label, $sortOrder]) {
            $updateImageSection->execute([$type, $label, $sortOrder, $pageId, $key]);
        }

        $updateSort = $pdo->prepare('
            UPDATE page_sections
            SET sort_order = ?
            WHERE page_id = ? AND section_key = ?
        ');

        foreach ($this->sortOrder as $key => $sortOrder) {
            $updateSort->execute([$sortOrder, $pageId, $key]);
        }
    }

    public function down(\PDO $pdo): void
    {
        $pageId = $this->aboutPageId($pdo);
        if ($pageId === null) {
            return;
        }

        $keys = array_keys($this->imageSections);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));

        $pdo->prepare("
            DELETE FROM page_sections
            WHERE page_id = ?
              AND section_key IN ({$placeholders})
              AND media_id IS NULL
              AND content IS NULL
        ")->execute(array_merge([$pageId], $keys));
    }

    private function aboutPageId(\PDO $pdo): ?int
    {
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = 'about' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute();
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }
};
