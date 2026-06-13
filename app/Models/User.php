<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, first_name, last_name, email, password_hash, status, last_login_at FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1';
        $row = $this->database->query($sql, ['email' => $email])->fetch();

        return is_array($row) ? $row : null;
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT id, first_name, last_name, email, status, last_login_at FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1';
        $row = $this->database->query($sql, ['id' => $id])->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allActive(): array
    {
        $rows = $this->database->query(
            'SELECT id, first_name, last_name, email, status
             FROM users
             WHERE status = "active" AND deleted_at IS NULL
             ORDER BY first_name ASC, last_name ASC, email ASC'
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function touchLastLoginAt(int $id): void
    {
        $this->database->query(
            'UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function hasRole(int $userId, string $roleSlug): bool
    {
        $sql = 'SELECT 1
                FROM role_user ru
                INNER JOIN roles r ON r.id = ru.role_id
                WHERE ru.user_id = :user_id AND r.slug = :role_slug
                LIMIT 1';

        return (bool) $this->database->query($sql, [
            'user_id' => $userId,
            'role_slug' => $roleSlug,
        ])->fetchColumn();
    }

    public function hasPermission(int $userId, string $permissionSlug): bool
    {
        $sql = 'SELECT 1
                FROM role_user ru
                INNER JOIN permission_role pr ON pr.role_id = ru.role_id
                INNER JOIN permissions p ON p.id = pr.permission_id
                WHERE ru.user_id = :user_id AND p.slug = :permission_slug
                LIMIT 1';

        return (bool) $this->database->query($sql, [
            'user_id' => $userId,
            'permission_slug' => $permissionSlug,
        ])->fetchColumn();
    }
}
