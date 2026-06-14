<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Middleware\CorsMiddleware;
use Throwable;

class Application
{
    private Router $router;
    private Request $request;
    private ErrorHandler $errorHandler;

    public function __construct(private readonly string $basePath)
    {
        $this->request      = new Request();
        $this->router       = new Router();
        $this->errorHandler = new ErrorHandler($basePath, $this->request);

        $this->loadRoutes();
    }

    public function run(): void
    {
        try {
            // CORS middleware runs globally before any routing
            $corsMiddleware = new CorsMiddleware();
            $corsResponse = null;

            // Handle CORS and potentially return early for OPTIONS
            $corsResult = $corsMiddleware->handle($this->request, function (Request $req): Response {
                return $this->router->dispatch($req);
            });

            $corsResult->send();
        } catch (Throwable $e) {
            // Ensure CORS headers are set on error responses too
            $this->applyCorsHeaders();
            $this->errorHandler->handle($e)->send();
        }
    }

    private function loadRoutes(): void
    {
        $routesPath = $this->basePath . '/routes';

        foreach (['api', 'auth', 'admin-api'] as $file) {
            $path = $routesPath . "/{$file}.php";
            if (file_exists($path)) {
                $loader = require $path;
                if (is_callable($loader)) {
                    $loader($this->router);
                }
            }
        }
    }

    private function applyCorsHeaders(): void
    {
        $origin         = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = config('cors.allowed_origins', []);

        if (in_array($origin, $allowedOrigins, true)) {
            if (!headers_sent()) {
                header("Access-Control-Allow-Origin: $origin");
                header('Access-Control-Allow-Credentials: true');
            }
        }
    }
}
