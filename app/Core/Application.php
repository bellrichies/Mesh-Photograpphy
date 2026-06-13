<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class Application
{
    private Router $router;

    private Request $request;

    private View $view;

    private ErrorHandler $errorHandler;

    public function __construct(private readonly string $basePath)
    {
        $this->router = new Router();
        $this->request = new Request();
        $this->view = new View($basePath);
        $this->errorHandler = new ErrorHandler($basePath, $this->request);
        $this->errorHandler->registerShutdownHandler();

        $this->loadRoutes();
    }

    public function run(): void
    {
        try {
            $response = $this->router->dispatch($this->request);
            $response->send();
        } catch (Throwable $exception) {
            $this->errorHandler->handle($exception)->send();
        }
    }

    public function router(): Router
    {
        return $this->router;
    }

    private function loadRoutes(): void
    {
        foreach (['auth', 'admin', 'api', 'web'] as $routeFile) {
            $path = $this->basePath . '/routes/' . $routeFile . '.php';
            if (! is_file($path)) {
                continue;
            }

            $registerRoutes = require $path;
            if (is_callable($registerRoutes)) {
                $registerRoutes($this->router);
            }
        }
    }
}
