<?php
declare(strict_types=1);

return new class {
    public string $description = 'Create team_members and clients tables for the About page';

    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS team_members (
                id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name          VARCHAR(150)    NOT NULL,
                role          VARCHAR(150)    NULL,
                bio           TEXT            NULL,
                photo_id      BIGINT UNSIGNED NULL,
                email         VARCHAR(190)    NULL,
                instagram_url VARCHAR(255)    NULL,
                is_published  TINYINT(1)      NOT NULL DEFAULT 1,
                sort_order    SMALLINT        NOT NULL DEFAULT 0,
                deleted_at    DATETIME        NULL,
                created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_team_members_deleted_at (deleted_at),
                CONSTRAINT fk_team_members_photo FOREIGN KEY (photo_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS clients (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name         VARCHAR(150)    NOT NULL,
                website_url  VARCHAR(255)    NULL,
                logo_id      BIGINT UNSIGNED NULL,
                is_published TINYINT(1)      NOT NULL DEFAULT 1,
                sort_order   SMALLINT        NOT NULL DEFAULT 0,
                deleted_at   DATETIME        NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_clients_deleted_at (deleted_at),
                CONSTRAINT fk_clients_logo FOREIGN KEY (logo_id) REFERENCES media(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->seed($pdo);

        echo "[Migration] Created team_members and clients tables.\n";
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS clients');
        $pdo->exec('DROP TABLE IF EXISTS team_members');
    }

    private function seed(\PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');

        // Only seed when the tables are empty so re-runs / existing data are untouched.
        $hasTeam = (int) $pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
        if ($hasTeam === 0) {
            $team = [
                ['Mesh Adeyemi', 'Founder & Lead Photographer', 'Mesh founded the studio after years shooting editorial and commercial work. Calm on set and meticulous in the edit, he leads every flagship session himself.', null, 0],
                ['Amara Okafor', 'Associate Photographer', 'Amara brings a documentary eye to weddings and family sessions, with a gift for catching the quiet in-between moments that matter most.', 'https://instagram.com', 1],
                ['Daniel Reyes', 'Studio & Post-Production Manager', 'Daniel keeps every shoot running on time and oversees the colour-grading process that gives Mesh imagery its signature warmth.', null, 2],
            ];
            $stmt = $pdo->prepare('
                INSERT INTO team_members (name, role, bio, instagram_url, sort_order, is_published, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 1, ?, ?)
            ');
            foreach ($team as [$name, $role, $bio, $instagram, $order]) {
                $stmt->execute([$name, $role, $bio, $instagram, $order, $now, $now]);
            }
        }

        $hasClients = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
        if ($hasClients === 0) {
            $clients = [
                ['Vogue Weddings', 'https://example.com', 0],
                ['The Knot', 'https://example.com', 1],
                ['Bloom & Co.', null, 2],
                ['Harbour House Events', null, 3],
                ['Meridian Studios', 'https://example.com', 4],
                ['Coastline Resorts', null, 5],
            ];
            $stmt = $pdo->prepare('
                INSERT INTO clients (name, website_url, sort_order, is_published, created_at, updated_at)
                VALUES (?, ?, ?, 1, ?, ?)
            ');
            foreach ($clients as [$name, $url, $order]) {
                $stmt->execute([$name, $url, $order, $now, $now]);
            }
        }
    }
};
