<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

class LoginThrottleService
{
    private const STORAGE_KEY = 'auth_login_throttle';

    public function __construct(private readonly Session $session)
    {
    }

    public function isBlocked(string $identifier, int $maxAttempts = 5, int $decaySeconds = 300): bool
    {
        $state = $this->state($identifier);
        if ($state === null) {
            return false;
        }

        if (($state['attempts'] ?? 0) < $maxAttempts) {
            return false;
        }

        $blockedUntil = (int) ($state['blocked_until'] ?? 0);
        return $blockedUntil > time();
    }

    public function registerFailure(string $identifier, int $maxAttempts = 5, int $decaySeconds = 300): void
    {
        $store = $this->store();
        $entry = $store[$identifier] ?? ['attempts' => 0, 'blocked_until' => 0, 'last_attempt_at' => 0];

        $lastAttemptAt = (int) ($entry['last_attempt_at'] ?? 0);
        if ($lastAttemptAt > 0 && (time() - $lastAttemptAt) > $decaySeconds) {
            $entry = ['attempts' => 0, 'blocked_until' => 0, 'last_attempt_at' => 0];
        }

        $entry['attempts'] = (int) $entry['attempts'] + 1;
        $entry['last_attempt_at'] = time();
        if ($entry['attempts'] >= $maxAttempts) {
            $entry['blocked_until'] = time() + $decaySeconds;
        }

        $store[$identifier] = $entry;
        $this->session->set(self::STORAGE_KEY, $store);
    }

    public function clear(string $identifier): void
    {
        $store = $this->store();
        unset($store[$identifier]);
        $this->session->set(self::STORAGE_KEY, $store);
    }

    public function retryAfter(string $identifier): int
    {
        $state = $this->state($identifier);
        if ($state === null) {
            return 0;
        }

        return max(0, (int) ($state['blocked_until'] ?? 0) - time());
    }

    public function key(string $scope, string ...$parts): string
    {
        $normalized = array_map(
            static fn (string $part): string => trim(strtolower($part)),
            array_filter($parts, static fn (string $part): bool => trim($part) !== '')
        );

        array_unshift($normalized, trim(strtolower($scope)));

        return hash('sha256', implode('|', $normalized));
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function store(): array
    {
        $raw = $this->session->get(self::STORAGE_KEY, []);
        return is_array($raw) ? $raw : [];
    }

    /**
     * @return array<string, int>|null
     */
    private function state(string $identifier): ?array
    {
        $store = $this->store();
        if (! isset($store[$identifier]) || ! is_array($store[$identifier])) {
            return null;
        }

        return $store[$identifier];
    }
}
