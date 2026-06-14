<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\Middleware\JwtMiddleware;

return function (Router $router): void {
    $router->post('/api/v1/auth/login',   'Auth\AuthController@login');
    $router->post('/api/v1/auth/refresh', 'Auth\AuthController@refresh');
    $router->post('/api/v1/auth/logout',  'Auth\AuthController@logout')
           ->middleware([JwtMiddleware::class]);
    $router->get('/api/v1/auth/me',       'Auth\AuthController@me')
           ->middleware([JwtMiddleware::class]);

    // Password reset (no auth required)
    $router->post('/api/v1/auth/password/request', 'Auth\PasswordResetController@request');
    $router->post('/api/v1/auth/password/reset',   'Auth\PasswordResetController@reset');
};
