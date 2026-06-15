<?php

declare(strict_types=1);

// When running under PHP's built-in development server, serve static files
// (images, CSS, JS, etc.) directly without going through the PHP router.
// This mirrors what .htaccess / nginx does in production.
if (PHP_SAPI === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($uri !== null && $uri !== '/' && is_file(__DIR__ . $uri)) {
        return false; // Let the built-in server stream the file as-is
    }
}

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/app.php';

$app = bootstrapApplication($basePath);
$app->run();
