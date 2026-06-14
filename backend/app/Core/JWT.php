<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

class JWT
{
    public function __construct(private readonly string $secret) {}

    public function encode(array $payload, int $ttl): string
    {
        $header          = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat']  = time();
        $payload['exp']  = time() + $ttl;
        $body            = $this->base64url(json_encode($payload));
        $sig             = $this->base64url(hash_hmac('sha256', "$header.$body", $this->secret, true));
        return "$header.$body.$sig";
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new HttpException(401, 'Malformed token');
        }

        [$header, $body, $sig] = $parts;

        $expectedSig = $this->base64url(
            hash_hmac('sha256', "$header.$body", $this->secret, true)
        );

        if (!hash_equals($expectedSig, $sig)) {
            throw new HttpException(401, 'Invalid token signature');
        }

        $payload = json_decode($this->base64urlDecode($body), true);

        if (!is_array($payload)) {
            throw new HttpException(401, 'Invalid token payload');
        }

        if (($payload['exp'] ?? 0) < time()) {
            throw new HttpException(401, 'Token expired');
        }

        return $payload;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
