<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\JWT;
use App\Core\Exceptions\HttpException;
use App\Models\User;
use App\Models\UserRefreshToken;

class JwtService
{
    public function __construct(
        private readonly JWT                  $jwt,
        private readonly User                 $userModel,
        private readonly UserRefreshToken     $tokenModel,
        private readonly AuthorizationService $authz
    ) {}

    public function issueTokens(array $user): array
    {
        $permissions = $this->authz->getUserPermissions($user['id']);
        $roles       = $this->authz->getUserRoles($user['id']);

        $accessToken = $this->jwt->encode([
            'sub'         => $user['id'],
            'email'       => $user['email'],
            'roles'       => $roles,
            'permissions' => $permissions,
        ], config('jwt.access_ttl'));

        $rawRefresh = bin2hex(random_bytes(64));
        $tokenHash  = hash('sha256', $rawRefresh);
        $expiresAt  = date('Y-m-d H:i:s', time() + config('jwt.refresh_ttl'));

        $this->tokenModel->store($user['id'], $tokenHash, $expiresAt);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefresh,
            'expires_in'    => config('jwt.access_ttl'),
        ];
    }

    public function refreshAccessToken(string $rawRefreshToken): array
    {
        $tokenHash = hash('sha256', $rawRefreshToken);
        $record    = $this->tokenModel->findValid($tokenHash);

        if (!$record) {
            throw new HttpException(401, 'Invalid or expired refresh token');
        }

        $user = $this->userModel->findById($record['user_id']);

        if (!$user || ($user['status'] ?? '') !== 'active') {
            throw new HttpException(401, 'User account is inactive');
        }

        // Rotate: revoke old, issue new
        $this->tokenModel->revoke($tokenHash);

        return $this->issueTokens($user);
    }

    public function revokeRefreshToken(string $rawRefreshToken): void
    {
        $tokenHash = hash('sha256', $rawRefreshToken);
        $this->tokenModel->revoke($tokenHash);
    }

    public function setRefreshCookie(string $rawRefreshToken): void
    {
        setcookie(
            config('jwt.refresh_cookie'),
            $rawRefreshToken,
            [
                'expires'  => time() + config('jwt.refresh_ttl'),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => env('APP_ENV') === 'production',
            ]
        );
    }

    public function clearRefreshCookie(): void
    {
        setcookie(
            config('jwt.refresh_cookie'),
            '',
            ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict']
        );
    }

    public function getRefreshTokenFromCookie(): ?string
    {
        return $_COOKIE[config('jwt.refresh_cookie')] ?? null;
    }

    public function buildUserPayload(array $user): array
    {
        return [
            'id'          => $user['id'],
            'email'       => $user['email'],
            'first_name'  => $user['first_name'],
            'last_name'   => $user['last_name'],
            'roles'       => $this->authz->getUserRoles($user['id']),
            'permissions' => $this->authz->getUserPermissions($user['id']),
        ];
    }
}
