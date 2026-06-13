<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    private const SESSION_KEY = 'admin_user_id';

    /** @var null|callable */
    private $userResolver = null;

    private ?string $lastSecurityFailureReason = null;

    public function __construct(private readonly Session $session)
    {
    }

    public function login(int $userId): void
    {
        $this->session->regenerate();
        $this->session->set(self::SESSION_KEY, $userId);
        $this->session->refreshSecurityContext();
        $this->lastSecurityFailureReason = null;
    }

    public function logout(): void
    {
        $this->clearAuthenticatedState();
    }

    public function check(): bool
    {
        if ($this->id() === null) {
            return false;
        }

        $reason = $this->session->validateSecurityContext();
        if ($reason !== null) {
            $this->lastSecurityFailureReason = $reason;
            $this->clearAuthenticatedState();
            return false;
        }

        $this->lastSecurityFailureReason = null;
        return true;
    }

    public function id(): ?int
    {
        $userId = $this->session->get(self::SESSION_KEY);
        return is_int($userId) ? $userId : null;
    }

    public function user(): mixed
    {
        $id = $this->id();
        if ($id === null) {
            return null;
        }

        if (is_callable($this->userResolver)) {
            return ($this->userResolver)($id);
        }

        return ['id' => $id];
    }

    public function currentUser(): ?array
    {
        $user = $this->user();
        return is_array($user) ? $user : null;
    }

    public function setUserResolver(callable $resolver): void
    {
        $this->userResolver = $resolver;
    }

    public function pullSecurityFailureReason(): ?string
    {
        $reason = $this->lastSecurityFailureReason;
        $this->lastSecurityFailureReason = null;

        return $reason;
    }

    private function clearAuthenticatedState(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->session->clearSecurityContext();
        $this->session->regenerate();
    }
}
