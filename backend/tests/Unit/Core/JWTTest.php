<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Exceptions\HttpException;
use App\Core\JWT;
use Tests\TestCase;

class JWTTest extends TestCase
{
    private JWT $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = new JWT('test-secret-key-for-unit-tests-only');
    }

    public function test_encode_returns_three_part_token(): void
    {
        $token = $this->jwt->encode(['sub' => 1], 900);
        $parts = explode('.', $token);

        $this->assertCount(3, $parts);
    }

    public function test_decode_returns_correct_payload(): void
    {
        $token   = $this->jwt->encode(['sub' => 42, 'email' => 'test@example.com'], 900);
        $payload = $this->jwt->decode($token);

        $this->assertSame(42, $payload['sub']);
        $this->assertSame('test@example.com', $payload['email']);
    }

    public function test_decode_throws_on_tampered_signature(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        $token  = $this->jwt->encode(['sub' => 1], 900);
        $parts  = explode('.', $token);
        $parts[2] = 'invalidsignature';

        $this->jwt->decode(implode('.', $parts));
    }

    public function test_decode_throws_on_malformed_token(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        $this->jwt->decode('not.a.valid.jwt.token');
    }

    public function test_decode_throws_on_expired_token(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(401);

        $token = $this->jwt->encode(['sub' => 1], -1); // already expired

        $this->jwt->decode($token);
    }

    public function test_different_secrets_produce_different_signatures(): void
    {
        $jwtA = new JWT('secret-a');
        $jwtB = new JWT('secret-b');

        $tokenA = $jwtA->encode(['sub' => 1], 900);

        $this->expectException(HttpException::class);
        $jwtB->decode($tokenA);
    }

    public function test_payload_contains_iat_and_exp(): void
    {
        $before  = time();
        $token   = $this->jwt->encode(['sub' => 1], 300);
        $after   = time();
        $payload = $this->jwt->decode($token);

        $this->assertGreaterThanOrEqual($before, $payload['iat']);
        $this->assertLessThanOrEqual($after, $payload['iat']);
        $this->assertSame($payload['iat'] + 300, $payload['exp']);
    }
}
