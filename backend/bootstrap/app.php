<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Config;
use Dotenv\Dotenv;

function bootstrapApplication(string $basePath): Application
{
    define('BASE_PATH', $basePath);

    // 1. Autoloader
    require $basePath . '/vendor/autoload.php';

    // 2. Helpers (already loaded via composer autoload files, but require for safety)
    if (!function_exists('env')) {
        require $basePath . '/bootstrap/helpers.php';
    }

    // 3. Guard: .env must exist
    if (!file_exists($basePath . '/.env')) {
        throw new RuntimeException(
            '.env file not found. Copy .env.example to .env and configure it.'
        );
    }

    // 4. Load .env
    $dotenv = Dotenv::createImmutable($basePath);
    $dotenv->safeLoad();

    // 5. Validate required env keys
    validateRequiredEnvKeys([
        'APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL',
        'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
        'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_FROM_ADDRESS',
        'JWT_SECRET',
    ]);

    // 6. Config load
    Config::load($basePath . '/config');

    // 7. Error reporting
    $debug = (bool) env('APP_DEBUG', false);
    error_reporting(E_ALL);
    ini_set('display_errors', $debug ? '1' : '0');

    // 8. Timezone
    date_default_timezone_set(config('app.timezone', 'UTC'));

    // 9. Return Application
    return new Application($basePath);
}
