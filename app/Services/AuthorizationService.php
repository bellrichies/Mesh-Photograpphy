<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AuthorizationService
{
    public function __construct(private readonly Database $database)
    {
    }

    public function userHasRole(int $userId, string $roleSlug): bool
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

    public function userHasPermission(int $userId, string $permissionSlug): bool
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
