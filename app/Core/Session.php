<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private const SECURITY_KEY = '_session_security';

    public function __construct()
    {
        $this->applyConfiguration();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->ageFlashData();
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function refreshSecurityContext(): void
    {
        $_SESSION[self::SECURITY_KEY] = [
            'fingerprint' => $this->fingerprint(),
            'last_activity_at' => time(),
            'issued_at' => time(),
        ];
    }

    public function clearSecurityContext(): void
    {
        unset($_SESSION[self::SECURITY_KEY]);
    }

    public function validateSecurityContext(): ?string
    {
        $context = $_SESSION[self::SECURITY_KEY] ?? null;
        if (! is_array($context) || ! isset($context['fingerprint'])) {
            return 'session_security_missing';
        }

        if (! hash_equals((string) $context['fingerprint'], $this->fingerprint())) {
            return 'session_fingerprint_mismatch';
        }

        $idleTimeoutSeconds = max(0, (int) config('session.idle_timeout', 60) * 60);
        $lastActivityAt = (int) ($context['last_activity_at'] ?? 0);

        if ($idleTimeoutSeconds > 0 && $lastActivityAt > 0 && (time() - $lastActivityAt) > $idleTimeoutSeconds) {
            return 'session_idle_timeout';
        }

        $context['last_activity_at'] = time();
        $_SESSION[self::SECURITY_KEY] = $context;

        return null;
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash']['old'][$key] ?? $default;
    }

    private function ageFlashData(): void
    {
        $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'] ?? [];
        $_SESSION['_flash']['new'] = [];
    }

    private function applyConfiguration(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if ((string) config('session.driver', 'file') === 'file') {
            $savePath = $this->resolveSessionSavePath((string) config('session.files_path', 'storage/sessions'));

            if (! is_dir($savePath)) {
                @mkdir($savePath, 0775, true);
            }

            if (is_dir($savePath)) {
                session_save_path($savePath);
            }
        }

        session_set_cookie_params([
            'lifetime' => (int) config('session.lifetime', 120) * 60,
            'path' => (string) config('session.path', '/'),
            'secure' => (bool) config('session.secure', false),
            'httponly' => (bool) config('session.http_only', true),
            'samesite' => (string) config('session.same_site', 'Lax'),
        ]);
    }

    private function resolveSessionSavePath(string $path): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path));
        if ($normalized === '') {
            return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
        }

        if (preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\|^\//', $normalized) === 1) {
            return $normalized;
        }

        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . trim($normalized, DIRECTORY_SEPARATOR);
    }

    private function fingerprint(): string
    {
        $parts = [];

        if ((bool) config('session.fingerprint.user_agent', true)) {
            $parts[] = strtolower(trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown-user-agent')));
        }

        if ((bool) config('session.fingerprint.accept_language', true)) {
            $parts[] = strtolower(trim((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown-language')));
        }

        if ((bool) config('session.fingerprint.ip', false)) {
            $parts[] = trim((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown-ip'));
        }

        if ($parts === []) {
            $parts[] = session_name();
        }

        return hash('sha256', implode('|', $parts));
    }
}
