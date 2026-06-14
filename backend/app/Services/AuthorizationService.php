<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AuthorizationService
{
    public function __construct(private readonly Database $db) {}

    public function getUserRoles(int $userId): array
    {
        $rows = $this->db->query(
            'SELECT r.slug FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?',
            [$userId]
        )->fetchAll();

        return array_column($rows, 'slug');
    }

    public function getUserPermissions(int $userId): array
    {
        $rows = $this->db->query(
            'SELECT DISTINCT p.slug FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?',
            [$userId]
        )->fetchAll();

        return array_column($rows, 'slug');
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions, true);
    }

    public function userHasRole(int $userId, string $role): bool
    {
        $roles = $this->getUserRoles($userId);
        return in_array($role, $roles, true);
    }
}
