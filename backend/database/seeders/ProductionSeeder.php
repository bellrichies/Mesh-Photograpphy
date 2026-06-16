<?php

declare(strict_types=1);

/**
 * Production seeder.
 *
 * Runs the required seeders in dependency order for a fresh production
 * database. Each delegated seeder is idempotent and safe to rerun.
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

        echo PHP_EOL . 'Mesh Photography Production Seeder' . PHP_EOL;

        $this->step('Roles & permissions', fn () => $this->seedRolesPermissions());
        $this->step('Admin user', fn () => $this->seedAdminUser());
        $this->step('Core CMS data', fn () => $this->seedCoreCms());
        $this->step('Verify integrity', fn () => $this->verifyIntegrity());

        echo 'Done' . PHP_EOL . PHP_EOL;
    }

    private function seedRolesPermissions(): void
    {
        require_once __DIR__ . '/RolesPermissionsSeeder.php';

        (new RolesPermissionsSeeder())->run($this->pdo);
    }

    private function seedAdminUser(): void
    {
        require_once __DIR__ . '/AdminUserSeeder.php';

        (new AdminUserSeeder())->run($this->pdo);
    }

    private function seedCoreCms(): void
    {
        require_once __DIR__ . '/CoreCmsSeeder.php';

        (new CoreCmsSeeder())->run($this->pdo);
    }

    private function verifyIntegrity(): void
    {
        $checks = [
            'super-admin users' => [
                "SELECT COUNT(*)
                 FROM users u
                 INNER JOIN user_roles ur ON ur.user_id = u.id
                 INNER JOIN roles r ON r.id = ur.role_id
                 WHERE r.slug = 'super-admin'
                   AND u.status = ?
                   AND u.deleted_at IS NULL",
                ['active'],
            ],
            'roles' => ['SELECT COUNT(*) FROM roles', []],
            'permissions' => ['SELECT COUNT(*) FROM permissions', []],
            'site_settings' => ['SELECT COUNT(*) FROM site_settings', []],
            'blog_categories' => ['SELECT COUNT(*) FROM blog_categories', []],
        ];

        $passed = true;

        foreach ($checks as $label => [$sql, $params]) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $count = (int) $stmt->fetchColumn();

            if ($count === 0) {
                echo "   [FAIL] {$label} is empty" . PHP_EOL;
                $passed = false;
                continue;
            }

            echo "   [OK]   {$label}: {$count} row(s)" . PHP_EOL;
        }

        if (!$passed) {
            throw new RuntimeException('Production seed integrity check failed.');
        }
    }

    private function step(string $label, callable $callback): void
    {
        echo PHP_EOL . "  [{$label}]" . PHP_EOL;
        $callback();
    }
}
