<?php

declare(strict_types=1);

class AdminUserSeeder
{
    public function run(PDO $pdo): void
    {
        $email = trim((string) ($_ENV['ADMIN_EMAIL'] ?? 'admin@meshphoto.com'));
        $password = (string) ($_ENV['ADMIN_INITIAL_PASSWORD'] ?? '');
        $isProduction = ($_ENV['APP_ENV'] ?? 'local') === 'production';
        $usesDefaultPassword = false;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('ADMIN_EMAIL must be a valid email address.');
        }

        if ($password === '') {
            if ($isProduction) {
                throw new RuntimeException('ADMIN_INITIAL_PASSWORD must be set before running production seeders.');
            }

            $password = 'changeme';
            $usesDefaultPassword = true;
        }

        if ($isProduction && strlen($password) < 12) {
            throw new RuntimeException('ADMIN_INITIAL_PASSWORD must be at least 12 characters in production.');
        }

        $roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'super-admin'")->fetchColumn();
        if (!$roleId) {
            throw new RuntimeException('The super-admin role was not found. Run seed:roles-permissions first.');
        }

        $existing = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $existing->execute([$email]);
        $userId = $existing->fetchColumn();

        if (!$userId) {
            $stmt = $pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, password, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([
                'Admin',
                'User',
                $email,
                password_hash($password, PASSWORD_BCRYPT),
                'active',
            ]);

            $userId = $pdo->lastInsertId();
            echo "[AdminUserSeeder] Created admin user: {$email}" . PHP_EOL;
        } else {
            echo "[AdminUserSeeder] Admin user already exists: {$email}" . PHP_EOL;
        }

        $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)')
            ->execute([$userId, $roleId]);

        if ($usesDefaultPassword) {
            echo '[AdminUserSeeder] Default local password: changeme (change this before production).' . PHP_EOL;
            return;
        }

        echo '[AdminUserSeeder] Password source: ADMIN_INITIAL_PASSWORD.' . PHP_EOL;
    }
}
