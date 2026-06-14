<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class UserRefreshToken
{
    public function __construct(private readonly Database $db) {}

    public function store(int $userId, string $tokenHash, string $expiresAt): void
    {
        $this->db->query(
            'INSERT INTO user_refresh_tokens (user_id, token_hash, expires_at)
             VALUES (?, ?, ?)',
            [$userId, $tokenHash, $expiresAt]
        );
    }

    public function findValid(string $tokenHash): ?array
    {
        return $this->db->query(
            'SELECT * FROM user_refresh_tokens
             WHERE token_hash = ? AND revoked_at IS NULL AND expires_at > NOW()
             LIMIT 1',
            [$tokenHash]
        )->fetch() ?: null;
    }

    public function revoke(string $tokenHash): void
    {
        $this->db->query(
            'UPDATE user_refresh_tokens SET revoked_at = NOW() WHERE token_hash = ?',
            [$tokenHash]
        );
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->db->query(
            'UPDATE user_refresh_tokens SET revoked_at = NOW()
             WHERE user_id = ? AND revoked_at IS NULL',
            [$userId]
        );
    }

    public function pruneExpired(): void
    {
        $this->db->query(
            'DELETE FROM user_refresh_tokens WHERE expires_at < NOW() OR revoked_at IS NOT NULL'
        );
    }
}
