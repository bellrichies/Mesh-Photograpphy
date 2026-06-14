<?php
declare(strict_types=1);

class RolesPermissionsSeeder
{
    public function run(\PDO $pdo): void
    {
        $permissions = [
            // Gallery
            ['view-galleries',       'View galleries'],
            ['manage-galleries',     'Create, edit, delete galleries'],
            // Blog
            ['view-blog',            'View blog posts'],
            ['manage-blog',          'Create, edit, delete blog posts'],
            // Media
            ['view-media',           'View media library'],
            ['manage-media',         'Upload and delete media'],
            // Services
            ['view-services',        'View services'],
            ['manage-services',      'Create, edit, delete services'],
            // Testimonials
            ['view-testimonials',    'View testimonials'],
            ['manage-testimonials',  'Create, edit, delete testimonials'],
            // Hero Slides
            ['view-hero-slides',     'View hero slides'],
            ['manage-hero-slides',   'Create, edit, delete hero slides'],
            // Pages
            ['view-pages',           'View CMS pages'],
            ['manage-pages',         'Create, edit, delete pages'],
            // Inquiries
            ['view-inquiries',       'View contact inquiries'],
            ['manage-inquiries',     'Manage and reply to inquiries'],
            // Bookings
            ['view-bookings',        'View booking requests'],
            ['manage-bookings',      'Manage booking requests'],
            // Settings
            ['view-settings',        'View site settings'],
            ['manage-settings',      'Update site settings'],
            // Users
            ['view-users',           'View admin users'],
            ['manage-users',         'Create, edit, delete admin users'],
        ];

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO permissions (slug, name)
            VALUES (?, ?)
        ");

        foreach ($permissions as [$slug, $name]) {
            $stmt->execute([$slug, $name]);
        }

        // Roles
        $roles = [
            ['super-admin',  'Super Admin',  'Full access to everything'],
            ['editor',       'Editor',       'Manage content but not users or settings'],
            ['viewer',       'Viewer',       'Read-only access to the admin panel'],
        ];

        $roleStmt = $pdo->prepare("
            INSERT IGNORE INTO roles (slug, name, description)
            VALUES (?, ?, ?)
        ");

        foreach ($roles as [$slug, $name, $desc]) {
            $roleStmt->execute([$slug, $name, $desc]);
        }

        // Super-admin gets all permissions
        $adminRole  = $pdo->query("SELECT id FROM roles WHERE slug = 'super-admin'")->fetch();
        $editorRole = $pdo->query("SELECT id FROM roles WHERE slug = 'editor'")->fetch();

        if ($adminRole) {
            $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(\PDO::FETCH_COLUMN);
            $rpStmt   = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($allPerms as $permId) {
                $rpStmt->execute([$adminRole['id'], $permId]);
            }
        }

        // Editor gets content permissions (no users, no settings)
        if ($editorRole) {
            $editorSlugs = [
                'view-galleries', 'manage-galleries',
                'view-blog',      'manage-blog',
                'view-media',     'manage-media',
                'view-services',  'manage-services',
                'view-testimonials', 'manage-testimonials',
                'view-hero-slides',  'manage-hero-slides',
                'view-pages',     'manage-pages',
                'view-inquiries', 'manage-inquiries',
                'view-bookings',  'manage-bookings',
            ];
            $in     = implode(',', array_fill(0, count($editorSlugs), '?'));
            $perms  = $pdo->prepare("SELECT id FROM permissions WHERE slug IN ($in)")->execute($editorSlugs);
            $stmt2  = $pdo->prepare("SELECT id FROM permissions WHERE slug IN ($in)");
            $stmt2->execute($editorSlugs);
            $editorPerms = $stmt2->fetchAll(\PDO::FETCH_COLUMN);
            $rpStmt  = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($editorPerms as $permId) {
                $rpStmt->execute([$editorRole['id'], $permId]);
            }
        }

        echo "[RolesPermissionsSeeder] Seeded " . count($permissions) . " permissions and " . count($roles) . " roles.\n";
    }
}
