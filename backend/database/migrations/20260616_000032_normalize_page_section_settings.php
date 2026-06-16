<?php
declare(strict_types=1);

return new class {
    public string $description = 'Normalize empty CMS page section settings';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            UPDATE page_sections
            SET settings_json = NULL
            WHERE settings_json IS NOT NULL
              AND TRIM(settings_json) IN ('[]', '{}', 'null', '')
        ");
    }

    public function down(\PDO $pdo): void
    {
        // No destructive rollback: NULL and empty JSON settings are equivalent.
    }
};
