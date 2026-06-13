<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Config;
use Dotenv\Dotenv;

if (! function_exists('bootstrapApplication')) {
    function bootstrapApplication(string $basePath): Application
    {
        require_once $basePath . '/vendor/autoload.php';
        require_once $basePath . '/bootstrap/helpers.php';

        if (! is_file($basePath . '/.env')) {
            throw new \RuntimeException('Application environment file not found. Create a .env file before bootstrapping.');
        }

        Dotenv::createImmutable($basePath)->safeLoad();

        validateRequiredEnvKeys([
            'APP_NAME',
            'APP_ENV',
            'APP_DEBUG',
            'APP_URL',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'SESSION_DRIVER',
            'SESSION_LIFETIME',
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_FROM_ADDRESS',
            'ADMIN_PATH',
            'CSRF_TOKEN_NAME',
        ]);

        Config::load($basePath . '/config');

        $isDebug = (bool) config('app.debug', false);
        error_reporting(E_ALL);
        ini_set('display_errors', $isDebug ? '1' : '0');
        date_default_timezone_set((string) config('app.timezone', 'UTC'));

        return new Application($basePath);
    }
}

if (! function_exists('validateRequiredEnvKeys')) {
    /**
     * @param array<int, string> $keys
     */
    function validateRequiredEnvKeys(array $keys): void
    {
        $missing = [];

        foreach ($keys as $key) {
            $value = env($key);
            if ($value === null || $value === '') {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException('Missing required environment keys: ' . implode(', ', $missing));
        }
    }
}
