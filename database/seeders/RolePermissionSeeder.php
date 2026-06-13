<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;

class RolePermissionSeeder
{
    public function run(Database $database): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access to all administrative capabilities.'],
            ['name' => 'Editor', 'slug' => 'editor', 'description' => 'Can manage editorial content workflows.'],
            ['name' => 'Content Manager', 'slug' => 'content-manager', 'description' => 'Can manage pages, media, and structured content.'],
        ];

        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'manage-users'],
            ['name' => 'Manage Settings', 'slug' => 'manage-settings'],
            ['name' => 'Manage Media', 'slug' => 'manage-media'],
            ['name' => 'Manage Pages', 'slug' => 'manage-pages'],
            ['name' => 'Manage Galleries', 'slug' => 'manage-galleries'],
            ['name' => 'Manage Services', 'slug' => 'manage-services'],
            ['name' => 'Manage Blog', 'slug' => 'manage-blog'],
            ['name' => 'Manage Testimonials', 'slug' => 'manage-testimonials'],
            ['name' => 'Manage Inquiries', 'slug' => 'manage-inquiries'],
        ];

        foreach ($roles as $role) {
            $database->query(
                'INSERT INTO roles (name, slug, description, created_at, updated_at)
                 VALUES (:name, :slug, :description, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), updated_at = NOW()',
                $role
            );
        }

        foreach ($permissions as $permission) {
            $database->query(
                'INSERT INTO permissions (name, slug, created_at, updated_at)
                 VALUES (:name, :slug, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = NOW()',
                $permission
            );
        }

        $superAdminRoleId = (int) $database->query('SELECT id FROM roles WHERE slug = :slug LIMIT 1', ['slug' => 'super-admin'])->fetchColumn();
        if ($superAdminRoleId > 0) {
            $allPermissionIds = $database->query('SELECT id FROM permissions')->fetchAll();
            if (is_array($allPermissionIds)) {
                foreach ($allPermissionIds as $row) {
                    $permissionId = (int) ($row['id'] ?? 0);
                    if ($permissionId <= 0) {
                        continue;
                    }

                    $database->query(
                        'INSERT IGNORE INTO permission_role (permission_id, role_id, created_at) VALUES (:permission_id, :role_id, NOW())',
                        [
                            'permission_id' => $permissionId,
                            'role_id' => $superAdminRoleId,
                        ]
                    );
                }
            }
        }
    }
}
