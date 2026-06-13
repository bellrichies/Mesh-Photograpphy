<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use App\Core\Middleware\MiddlewareInterface;

class Router
{
    /**
     * @var array<int, array{
     *   method: string,
     *   path: string,
     *   regex: string,
     *   paramNames: array<int, string>,
     *   handler: mixed,
     *   middleware: array<int, callable|string>
     * }>
     */
    private array $routes = [];

    public function get(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, mixed $handler, array $middleware = []): RouteDefinition
    {
        [$regex, $paramNames] = $this->compilePath($path);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => $regex,
            'paramNames' => $paramNames,
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        $routeIndex = array_key_last($this->routes);
        return new RouteDefinition($this, (int) $routeIndex);
    }

    /**
     * @param array<int, callable|string> $middleware
     */
    public function setRouteMiddleware(int $routeIndex, array $middleware): void
    {
        if (! isset($this->routes[$routeIndex])) {
            return;
        }

        $this->routes[$routeIndex]['middleware'] = $middleware;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (! preg_match($route['regex'], $request->path(), $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['paramNames'] as $paramName) {
                $params[$paramName] = $matches[$paramName] ?? '';
            }

            $request->setRouteParams($params);
            return $this->runRoute($route, $request, $params);
        }

        throw new HttpException(404, 'Route not found');
    }

    /**
     * @param array{
     *   handler: mixed,
     *   middleware: array<int, callable|string>
     * } $route
     * @param array<string, string> $params
     */
    private function runRoute(array $route, Request $request, array $params): Response
    {
        $runner = function (Request $request) use ($route, $params): Response {
            $result = $this->invokeHandler($route['handler'], $request, $params);
            return $this->normalizeResult($result);
        };

        foreach (array_reverse($route['middleware']) as $middleware) {
            $next = $runner;
            $runner = function (Request $request) use ($middleware, $next): Response {
                if (is_callable($middleware)) {
                    $result = $middleware($request, $next);
                    return $this->normalizeResult($result);
                }

                if (is_string($middleware) && class_exists($middleware)) {
                    $instance = new $middleware();
                    if ($instance instanceof MiddlewareInterface) {
                        $result = $instance->handle($request, $next);
                        return $this->normalizeResult($result);
                    }
                }

                throw new HttpException(500, 'Invalid middleware definition');
            };
        }

        return $runner($request);
    }

    /**
     * @param array<string, string> $params
     */
    private function invokeHandler(mixed $handler, Request $request, array $params): mixed
    {
        $response = new Response();

        if (is_callable($handler)) {
            return $handler($request, $response, $params);
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = new $class();
            return $instance->{$method}($request, $response, $params);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$classOrObject, $method] = $handler;
            $instance = is_string($classOrObject) ? new $classOrObject() : $classOrObject;
            return $instance->{$method}($request, $response, $params);
        }

        throw new HttpException(500, 'Unsupported route handler');
    }

    private function normalizeResult(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        $response = new Response();

        if (is_array($result)) {
            return $response->json($result);
        }

        return $response->html((string) $result);
    }

    /**
     * @return array{string, array<int, string>}
     */
    private function compilePath(string $path): array
    {
        $normalized = '/' . trim($path, '/');
        if ($normalized === '//') {
            $normalized = '/';
        }

        $paramNames = [];
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function (array $matches) use (&$paramNames): string {
            $paramNames[] = $matches[1];
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $normalized);

        return ['#^' . $pattern . '$#', $paramNames];
    }
}

class RouteDefinition
{
    public function __construct(private readonly Router $router, private readonly int $routeIndex)
    {
    }

    /**
     * @param array<int, callable|string>|callable|string $middleware
     */
    public function middleware(array|callable|string $middleware): self
    {
        $list = is_array($middleware) ? $middleware : [$middleware];
        $this->router->setRouteMiddleware($this->routeIndex, $list);
        return $this;
    }
}
