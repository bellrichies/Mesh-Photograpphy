<?php
declare(strict_types=1);

class AdminUserSeeder
{
    public function run(\PDO $pdo): void
    {
        $email     = 'admin@meshphoto.com';
        $password  = password_hash('changeme', PASSWORD_BCRYPT);
        $firstName = 'Admin';
        $lastName  = 'User';

        // Upsert: insert or skip if email already exists
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (first_name, last_name, email, password, status)
            VALUES (?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$firstName, $lastName, $email, $password]);

        // Assign super-admin role
        $user = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $user->execute([$email]);
        $userId = $user->fetchColumn();

        $role = $pdo->query("SELECT id FROM roles WHERE slug = 'super-admin'")->fetchColumn();

        if ($userId && $role) {
            $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)")
                ->execute([$userId, $role]);
        }

        echo "[AdminUserSeeder] Admin user: {$email} / changeme (CHANGE THIS IN PRODUCTION)\n";
    }
}
