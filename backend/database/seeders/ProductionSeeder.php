<?php

declare(strict_types=1);

/**
 * Production seeder — PL-14
 *
 * Runs all required seed steps in the correct order for a fresh production
 * database. Safe to run on an existing database: each step is idempotent.
 *
 * Usage:
 *   php database/console.php seed:production
 */
class ProductionSeeder
{
    private PDO $pdo;

    public function run(PDO $pdo): void
    {
        $this->pdo = $pdo;

        echo "\n── Mesh Photography Production Seeder ──────────────────────────\n";

        $this->step('Roles & permissions', fn () => $this->seedRolesPermissions());
        $this->step('Admin user',          fn () => $this->seedAdminUser());
        $this->step('Core CMS pages',      fn () => $this->seedCoreCmsPages());
        $this->step('Default settings',    fn () => $this->seedSettings());
        $this->step('Verify integrity',    fn () => $this->verifyIntegrity());

        echo "── Done ──────────────────────────────────────────────────────────\n\n";
    }

    // ── Steps ─────────────────────────────────────────────────────────────────

    private function seedRolesPermissions(): void
    {
        $roles = ['super-admin', 'admin', 'editor', 'viewer'];
        foreach ($roles as $role) {
            $this->upsert('roles', ['name' => $role], ['name' => $role]);
        }

        $permissions = [
            'manage-galleries', 'manage-blog', 'manage-services', 'manage-testimonials',
            'manage-hero-slides', 'manage-pages', 'manage-media', 'manage-inquiries',
            'manage-bookings', 'manage-settings', 'manage-users', 'view-activity-logs',
        ];
        foreach ($permissions as $perm) {
            $this->upsert('permissions', ['name' => $perm], ['name' => $perm]);
        }

        // Grant all permissions to super-admin and admin roles
        $adminRoles = ['super-admin', 'admin'];
        foreach ($adminRoles as $roleName) {
            $roleId = $this->findId('roles', 'name', $roleName);
            if (!$roleId) continue;

            foreach ($permissions as $perm) {
                $permId = $this->findId('permissions', 'name', $perm);
                if (!$permId) continue;

                $exists = $this->pdo->prepare('SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?');
                $exists->execute([$roleId, $permId]);
                if (!$exists->fetchColumn()) {
                    $this->pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)')
                              ->execute([$roleId, $permId]);
                }
            }
        }

        echo "   Roles: " . implode(', ', $roles) . "\n";
        echo "   Permissions: " . count($permissions) . " entries\n";
    }

    private function seedAdminUser(): void
    {
        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@meshphoto.com';

        $exists = $this->pdo->prepare('SELECT id FROM admin_users WHERE email = ?');
        $exists->execute([$email]);

        if ($exists->fetchColumn()) {
            echo "   Admin user already exists ({$email}) — skipped\n";
            return;
        }

        $password = $_ENV['ADMIN_INITIAL_PASSWORD'] ?? null;
        if (!$password) {
            // Generate a secure random password and print it once
            $password = bin2hex(random_bytes(12));
            echo "   !! Generated initial admin password: {$password}\n";
            echo "   !! Change this immediately after first login.\n";
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_users (email, password_hash, first_name, last_name, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT), 'Admin', 'User', 'active']);

        $userId = (int) $this->pdo->lastInsertId();
        $roleId = $this->findId('roles', 'name', 'super-admin');

        if ($userId && $roleId) {
            $this->pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)')
                      ->execute([$userId, $roleId]);
        }

        echo "   Created admin user: {$email}\n";
    }

    private function seedCoreCmsPages(): void
    {
        $pages = [
            ['title' => 'About',          'slug' => 'about'],
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy'],
            ['title' => 'Terms',          'slug' => 'terms'],
            ['title' => 'Cookie Policy',  'slug' => 'cookie-policy'],
        ];

        $created = 0;
        foreach ($pages as $page) {
            $exists = $this->pdo->prepare('SELECT 1 FROM pages WHERE slug = ? AND deleted_at IS NULL');
            $exists->execute([$page['slug']]);
            if ($exists->fetchColumn()) continue;

            $this->pdo->prepare(
                'INSERT INTO pages (title, slug, body, is_published, seo_title, seo_description, created_at, updated_at)
                 VALUES (?, ?, NULL, 1, ?, ?, NOW(), NOW())'
            )->execute([
                $page['title'],
                $page['slug'],
                $page['title'] . ' | Mesh Photography',
                'Default CMS page for ' . $page['title'] . '.',
            ]);

            $created++;
        }

        echo "   CMS pages: {$created} created, " . (count($pages) - $created) . " already exist\n";
    }

    private function seedSettings(): void
    {
        $defaults = [
            ['key' => 'site_name',           'value' => 'Mesh Photography',        'group' => 'general'],
            ['key' => 'site_tagline',         'value' => 'Capturing Life in Light', 'group' => 'general'],
            ['key' => 'contact_email',        'value' => 'hello@meshphoto.com',     'group' => 'contact'],
            ['key' => 'contact_phone',        'value' => '',                        'group' => 'contact'],
            ['key' => 'contact_address',      'value' => '',                        'group' => 'contact'],
            ['key' => 'social_instagram',     'value' => '',                        'group' => 'social'],
            ['key' => 'social_facebook',      'value' => '',                        'group' => 'social'],
            ['key' => 'social_twitter',       'value' => '',                        'group' => 'social'],
            ['key' => 'social_x',             'value' => '',                        'group' => 'social'],
            ['key' => 'social_youtube',       'value' => '',                        'group' => 'social'],
            ['key' => 'social_pinterest',     'value' => '',                        'group' => 'social'],
            ['key' => 'social_linkedin',      'value' => '',                        'group' => 'social'],
            ['key' => 'social_tiktok',        'value' => '',                        'group' => 'social'],
            ['key' => 'booking_lead_time',    'value' => '7',                       'group' => 'booking'],
            ['key' => 'google_analytics_id',  'value' => '',                        'group' => 'analytics'],
        ];

        $created = 0;
        foreach ($defaults as $setting) {
            $exists = $this->pdo->prepare('SELECT 1 FROM site_settings WHERE key_name = ?');
            $exists->execute([$setting['key']]);
            if ($exists->fetchColumn()) continue;

            $this->pdo->prepare(
                'INSERT INTO site_settings (key_name, value, type, group_name, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())'
            )->execute([$setting['key'], $setting['value'], 'string', $setting['group']]);

            $created++;
        }

        echo "   Settings: {$created} created, " . (count($defaults) - $created) . " already exist\n";
    }

    private function verifyIntegrity(): void
    {
        $checks = [
            'admin_users'    => 'SELECT COUNT(*) FROM admin_users WHERE status = ? AND deleted_at IS NULL',
            'roles'          => 'SELECT COUNT(*) FROM roles',
            'permissions'    => 'SELECT COUNT(*) FROM permissions',
            'site_settings'  => 'SELECT COUNT(*) FROM site_settings',
        ];

        $pass = true;
        foreach ($checks as $table => $sql) {
            $params = $table === 'admin_users' ? ['active'] : [];
            $stmt   = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $count = (int) $stmt->fetchColumn();

            if ($count === 0) {
                echo "   [FAIL] {$table} is empty\n";
                $pass = false;
            } else {
                echo "   [OK]   {$table}: {$count} row(s)\n";
            }
        }

        if (!$pass) {
            throw new RuntimeException('Integrity check failed — review output above');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function upsert(string $table, array $where, array $data): void
    {
        $col  = array_key_first($where);
        $val  = $where[$col];
        $stmt = $this->pdo->prepare("SELECT 1 FROM `{$table}` WHERE `{$col}` = ?");
        $stmt->execute([$val]);
        if ($stmt->fetchColumn()) return;

        $cols   = implode(', ', array_map(fn ($k) => "`{$k}`", array_keys($data)));
        $ph     = implode(', ', array_fill(0, count($data), '?'));
        $this->pdo->prepare("INSERT INTO `{$table}` ({$cols}) VALUES ({$ph})")
                  ->execute(array_values($data));
    }

    private function findId(string $table, string $col, string $val): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM `{$table}` WHERE `{$col}` = ? LIMIT 1");
        $stmt->execute([$val]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }

    private function step(string $label, callable $fn): void
    {
        echo "\n  [{$label}]\n";
        $fn();
    }
}
