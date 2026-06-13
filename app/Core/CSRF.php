<?php

declare(strict_types=1);

namespace App\Core;

class CSRF
{
    public function __construct(private readonly Session $session, ?string $tokenKey = null)
    {
        $this->tokenKey = $tokenKey ?? (string) config('app.csrf_token_name', '_token');
    }

    private readonly string $tokenKey;

    public function token(): string
    {
        $token = (string) $this->session->get($this->tokenKey, '');

        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set($this->tokenKey, $token);
        }

        return $token;
    }

    public function verify(?string $token): bool
    {
        if (! is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($this->token(), $token);
    }

    public function inputField(): string
    {
        return '<input type="hidden" name="' . $this->tokenKey . '" value="' . $this->token() . '">';
    }
}
