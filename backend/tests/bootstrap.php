<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

// Minimal stubs so tests can call config() / env() without a full application boot
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return $GLOBALS['__test_config'][$key] ?? $default;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}
