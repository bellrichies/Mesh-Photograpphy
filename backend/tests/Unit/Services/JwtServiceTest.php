<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Exceptions\HttpException;
use App\Core\JWT;
use App\Models\User;
use App\Models\UserRefreshToken;
use App\Services\AuthorizationService;
use App\Services\JwtService;
use Tests\TestCase;

class JwtServiceTest extends TestCase
{
    private JWT $jwt;
    private User $userModel;
    private UserRefreshToken $tokenModel;
    private AuthorizationService $authz;
    private JwtService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfig('jwt.access_ttl', 900);
        $this->setConfig('jwt.refresh_ttl', 604800);
        $this->setConfig('jwt.refresh_cookie', 'mesh_refresh_token');

        $this->jwt        = new JWT('test-jwt-secret-for-unit-tests-only');
        $this->userModel  = $this->createMock(User::class);
        $this->tokenModel = $this->createMock(UserRefreshToken::class);
        $this->authz      = $this->createMock(AuthorizationService::class);

        $this->authz->method('getUserPermissions')->willReturn(['manage-galleries']);
        $this->authz->method('getUserRoles')->willReturn(['admin']);

        $this->service = new JwtService($this->jwt, $this->userModel, $this->tokenModel, $this->authz);
    }

    public function test_issue_tokens_returns_access_and_refresh_token(): void
    {
        $this->tokenModel->expects($this->once())->method('store');

        $user = ['id' => 1, 'email' => 'admin@meshphoto.com'];

        $tokens = $this->service->issueTokens($user);

        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertArrayHasKey('expires_in', $tokens);
        $this->assertSame(900, $tokens['expires_in']);
    }

    public function test_issue_tokens_access_token_is_valid_jwt(): void
    {
        $this->tokenModel->method('store');

        $tokens  = $this->service->issueTokens(['id' => 1, 'email' => 'admin@meshphoto.com']);
        $payload = $this->jwt->decode($tokens['access_token']);

        $this->assertSame(1, $payload['sub']);
        $this->assertSame('admin@meshphoto.com', $payload['email']);
        $this->assertContains('admin', $payload['roles']);
    }

    public function test_refresh_access_token_throws_on_invalid_token(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        $this->tokenModel->method('findValid')->willReturn(null);

        $this->service->refreshAccessToken('invalid-raw-token');
    }

    public function test_refresh_access_token_rotates_refresh_token(): void
    {
        $rawRefresh = bin2hex(random_bytes(64));
        $tokenHash  = hash('sha256', $rawRefresh);

        $this->tokenModel
            ->method('findValid')
            ->with($tokenHash)
            ->willReturn(['user_id' => 1, 'token_hash' => $tokenHash]);

        $this->userModel
            ->method('findById')
            ->with(1)
            ->willReturn(['id' => 1, 'email' => 'admin@meshphoto.com', 'status' => 'active']);

        $this->tokenModel->expects($this->once())->method('revoke')->with($tokenHash);
        $this->tokenModel->expects($this->once())->method('store');

        $tokens = $this->service->refreshAccessToken($rawRefresh);

        $this->assertArrayHasKey('access_token', $tokens);
    }

    public function test_refresh_throws_when_user_is_inactive(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        $rawRefresh = bin2hex(random_bytes(64));
        $tokenHash  = hash('sha256', $rawRefresh);

        $this->tokenModel->method('findValid')->willReturn(['user_id' => 2, 'token_hash' => $tokenHash]);
        $this->userModel->method('findById')->willReturn(['id' => 2, 'email' => 'x@x.com', 'status' => 'suspended']);

        $this->service->refreshAccessToken($rawRefresh);
    }

    public function test_revoke_calls_token_model(): void
    {
        $rawRefresh = bin2hex(random_bytes(64));
        $tokenHash  = hash('sha256', $rawRefresh);

        $this->tokenModel->expects($this->once())->method('revoke')->with($tokenHash);

        $this->service->revokeRefreshToken($rawRefresh);
    }

    public function test_build_user_payload_includes_roles_and_permissions(): void
    {
        $user    = ['id' => 1, 'email' => 'admin@meshphoto.com', 'first_name' => 'Admin', 'last_name' => 'User'];
        $payload = $this->service->buildUserPayload($user);

        $this->assertArrayHasKey('roles', $payload);
        $this->assertArrayHasKey('permissions', $payload);
        $this->assertSame('admin@meshphoto.com', $payload['email']);
    }
}
