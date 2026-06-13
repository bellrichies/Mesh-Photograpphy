<?php

declare(strict_types=1);

use App\Controllers\Auth\AuthController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\GuestMiddleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

return static function (Router $router): void {
    $adminPrefix = '/' . trim((string) config('app.admin_path', '/admin'), '/');
    $router->get('/login', static function (Request $request, Response $response) use ($adminPrefix): Response {
        return $response->redirect($adminPrefix . '/login', 302);
    })->middleware(GuestMiddleware::class);

    $router->post('/login', static function (Request $request, Response $response) use ($adminPrefix): Response {
        return $response->redirect($adminPrefix . '/login', 302);
    })->middleware(GuestMiddleware::class);

    $router->get($adminPrefix . '/login', [AuthController::class, 'showLogin'])->middleware(GuestMiddleware::class);
    $router->post($adminPrefix . '/login', [AuthController::class, 'login'])->middleware(GuestMiddleware::class);

    $router->post('/logout', static function (Request $request, Response $response) use ($adminPrefix): Response {
        return $response->redirect($adminPrefix . '/logout', 307);
    })->middleware(AuthMiddleware::class);

    $router->post($adminPrefix . '/logout', [AuthController::class, 'logout'])->middleware(AuthMiddleware::class);
};
