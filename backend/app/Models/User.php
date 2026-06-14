<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public function __construct(private readonly Database $db) {}

    public function findById(int $id): ?array
    {
        return $this->db->query(
            'SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->query(
            'SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1',
            [$email]
        )->fetch() ?: null;
    }

    public function allActive(): array
    {
        return $this->db->query(
            'SELECT id, email, first_name, last_name, status, last_login_at, created_at
             FROM users WHERE deleted_at IS NULL AND status = ? ORDER BY created_at DESC',
            ['active']
        )->fetchAll();
    }

    public function create(array $data): int
    {
        $this->db->query(
            'INSERT INTO users (email, password, first_name, last_name, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['first_name'],
                $data['last_name'],
                $data['status'] ?? 'active',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];

        foreach (['email', 'first_name', 'last_name', 'status'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return;
        }

        $fields[]  = 'updated_at = NOW()';
        $params[]  = $id;

        $this->db->query('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?', $params);
    }

    public function softDelete(int $id): void
    {
        $this->db->query('UPDATE users SET deleted_at = NOW() WHERE id = ?', [$id]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
