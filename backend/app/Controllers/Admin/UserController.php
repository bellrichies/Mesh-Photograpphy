<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class UserController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT u.id, u.email, u.first_name, u.last_name, u.status, u.last_login_at, u.created_at,
                    GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR \',\') AS roles
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE u.deleted_at IS NULL
             GROUP BY u.id
             ORDER BY u.created_at DESC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $err  = $this->validate($data, [
            'email'      => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'password'   => 'required|string',
        ]);
        if ($err) return $err;

        $db = app_database();
        if ($db->query('SELECT id FROM users WHERE email=? AND deleted_at IS NULL', [$data['email']])->fetch()) {
            return $this->validationError(['email' => ['Email already in use.']]);
        }

        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $db->query(
            'INSERT INTO users (email, first_name, last_name, password, status, created_at, updated_at)
             VALUES (?,?,?,?,\'active\',NOW(),NOW())',
            [$data['email'], $data['first_name'], $data['last_name'], $hash]
        );

        $id = (int) $db->lastInsertId();

        if (!empty($data['role_ids'])) {
            foreach ($data['role_ids'] as $roleId) {
                $db->query('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?,?)', [$id, (int)$roleId]);
            }
        }

        $row = $db->query('SELECT u.*, GROUP_CONCAT(DISTINCT r.name SEPARATOR \',\') AS roles FROM users u LEFT JOIN user_roles ur ON u.id=ur.user_id LEFT JOIN roles r ON ur.role_id=r.id WHERE u.id=? GROUP BY u.id', [$id])->fetch();

        return $this->created($this->formatRow($row));
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $data = $request->json();

        if (!$db->query('SELECT id FROM users WHERE id=? AND deleted_at IS NULL', [$id])->fetch()) {
            throw new HttpException(404, 'User not found.');
        }

        $sets   = ['first_name=?', 'last_name=?', 'status=?', 'updated_at=NOW()'];
        $params = [$data['first_name'] ?? '', $data['last_name'] ?? '', $data['status'] ?? 'active'];

        if (!empty($data['password'])) {
            $sets[]   = 'password=?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $params[] = $id;
        $db->query('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id=?', $params);

        if (isset($data['role_ids'])) {
            $db->query('DELETE FROM user_roles WHERE user_id=?', [$id]);
            foreach ($data['role_ids'] as $roleId) {
                $db->query('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?,?)', [$id, (int)$roleId]);
            }
        }

        $row = $db->query('SELECT u.*, GROUP_CONCAT(DISTINCT r.name SEPARATOR \',\') AS roles FROM users u LEFT JOIN user_roles ur ON u.id=ur.user_id LEFT JOIN roles r ON ur.role_id=r.id WHERE u.id=? GROUP BY u.id', [$id])->fetch();

        return $this->success($this->formatRow($row));
    }

    public function roles(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT id, name, description FROM roles ORDER BY name ASC'
        )->fetchAll();

        return $this->success(array_map(fn($r) => [
            'id'          => (int)$r['id'],
            'name'        => $r['name'],
            'description' => $r['description'] ?? null,
        ], $rows));
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $me  = $request->authPayload()['user_id'] ?? $request->authPayload()['sub'] ?? null;

        if ($id === (int)$me) {
            return $this->error('You cannot delete your own account.', 403);
        }

        if (!$db->query('SELECT id FROM users WHERE id=? AND deleted_at IS NULL', [$id])->fetch()) {
            throw new HttpException(404, 'User not found.');
        }

        $db->query('UPDATE users SET deleted_at=NOW() WHERE id=?', [$id]);
        return $this->noContent();
    }

    private function formatRow(array $row): array
    {
        $roles = $row['roles'] ? explode(',', $row['roles']) : [];
        return [
            'id'            => (int)$row['id'],
            'email'         => $row['email'],
            'first_name'    => $row['first_name'],
            'last_name'     => $row['last_name'],
            'roles'         => $roles,
            'status'        => $row['status'] ?? 'active',
            'last_login_at' => $row['last_login_at'] ?? null,
            'created_at'    => $row['created_at'],
        ];
    }
}
