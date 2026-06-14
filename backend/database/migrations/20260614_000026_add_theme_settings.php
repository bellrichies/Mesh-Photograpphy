<?php
declare(strict_types=1);

return new class {
    public string $description = 'Add theme (colour and typography) settings to site_settings';

    public function up(\PDO $pdo): void
    {
        $settings = [
            // Colours
            ['theme_primary_color',   '#C4923B', 'string', 'theme'],
            ['theme_secondary_color', '#FAF9F7', 'string', 'theme'],
            ['theme_accent_color',    '#B8860B', 'string', 'theme'],
            ['theme_text_color',      '#1A1A1A', 'string', 'theme'],
            ['theme_bg_color',        '#FAF9F7', 'string', 'theme'],
            // Typography
            ['theme_display_font',    'Cormorant Garamond', 'string', 'theme'],
            ['theme_body_font',       'Inter',              'string', 'theme'],
        ];

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO site_settings (key_name, value, type, group_name)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($settings as $row) {
            $stmt->execute($row);
        }

        echo "[Migration] Inserted " . count($settings) . " theme settings.\n";
    }

    public function down(\PDO $pdo): void
    {
        $pdo->prepare("DELETE FROM site_settings WHERE group_name = 'theme'")->execute();
    }
};
