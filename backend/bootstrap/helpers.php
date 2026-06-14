<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\JWT;

// ─── Environment ──────────────────────────────────────────────────────────────

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false) {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true', '(true)'   => true,
        'false', '(false)' => false,
        'null', '(null)'   => null,
        'empty', '(empty)' => '',
        default            => $value,
    };
}

// ─── Config ───────────────────────────────────────────────────────────────────

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

// ─── Database ─────────────────────────────────────────────────────────────────

function app_database(): Database
{
    static $instance = null;
    if ($instance === null) {
        $conn     = config('database.default', 'mysql');
        $config   = config("database.connections.{$conn}");
        $instance = new Database($config);
    }
    return $instance;
}

// ─── JWT ──────────────────────────────────────────────────────────────────────

function app_jwt(): JWT
{
    static $instance = null;
    if ($instance === null) {
        $secret = config('jwt.secret');
        if (!$secret) {
            throw new RuntimeException('JWT_SECRET is not set in .env');
        }
        $instance = new JWT($secret);
    }
    return $instance;
}

// ─── Settings ─────────────────────────────────────────────────────────────────

function app_setting(string $group, string $key, mixed $default = null): mixed
{
    $settings = app_settings_all();
    return $settings[$group][$key] ?? $default;
}

function app_settings_group(string $group): array
{
    return app_settings_all()[$group] ?? [];
}

function app_settings_all(): array
{
    static $cache = null;
    $ttl      = config('app.settings_cache_ttl', 300);
    $cacheDir = defined('BASE_PATH') ? BASE_PATH . '/storage/cache' : dirname(__DIR__) . '/storage/cache';
    $cacheFile = $cacheDir . '/settings.php';

    if ($cache !== null) {
        return $cache;
    }

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        $cache = require $cacheFile;
        return $cache;
    }

    try {
        $db   = app_database();
        $rows = $db->query('SELECT `group`, `key`, `value`, `type` FROM settings')->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($rows as $row) {
            $data[$row['group']][$row['key']] = $row['value'];
        }

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';', LOCK_EX);
        $cache = $data;
    } catch (Throwable) {
        $cache = [];
    }

    return $cache;
}

function app_settings_refresh_cache(): void
{
    $cacheDir  = defined('BASE_PATH') ? BASE_PATH . '/storage/cache' : dirname(__DIR__) . '/storage/cache';
    $cacheFile = $cacheDir . '/settings.php';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

// ─── Slugify ──────────────────────────────────────────────────────────────────

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^\w\s-]/u', '', $text);
    $text = preg_replace('/[\s_]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ─── Validation helper ────────────────────────────────────────────────────────

function validateRequiredEnvKeys(array $keys): void
{
    $missing = [];
    foreach ($keys as $key) {
        if (env($key) === null) {
            $missing[] = $key;
        }
    }
    if (!empty($missing)) {
        throw new RuntimeException('Missing required environment variables: ' . implode(', ', $missing));
    }
}
