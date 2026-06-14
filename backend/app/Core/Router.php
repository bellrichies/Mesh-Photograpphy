<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

class Router
{
    /** @var RouteDefinition[] */
    private array $routes = [];

    public function get(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('DELETE', $path, $handler);
    }

    public function options(string $path, mixed $handler): RouteDefinition
    {
        return $this->add('OPTIONS', $path, $handler);
    }

    private function add(string $method, string $path, mixed $handler): RouteDefinition
    {
        $route = new RouteDefinition($method, $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $pattern = $this->compilePattern($route->pattern);

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            // Extract named captures as route params
            $params = array_filter(
                $matches,
                fn($key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );
            $request->setRouteParams($params);

            return $this->runMiddlewareChain(
                $request,
                $route->getMiddlewares(),
                $route->handler
            );
        }

        throw new HttpException(404, 'Not found');
    }

    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function runMiddlewareChain(Request $request, array $middlewares, mixed $handler): Response
    {
        $core = function (Request $req) use ($handler): Response {
            return $this->callHandler($req, $handler);
        };

        // Wrap handler in middleware chain (last-in is outermost)
        $chain = array_reduce(
            array_reverse($middlewares),
            function (callable $next, mixed $middlewareSpec) {
                return function (Request $req) use ($next, $middlewareSpec): Response {
                    $middleware = $this->resolveMiddleware($middlewareSpec);
                    return $middleware->handle($req, $next);
                };
            },
            $core
        );

        return $chain($request);
    }

    private function callHandler(Request $request, mixed $handler): Response
    {
        if (is_callable($handler)) {
            return $handler($request, new Response());
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler);
            $fullClass = str_starts_with($class, 'App\\') ? $class : 'App\\Controllers\\' . $class;
            $instance  = new $fullClass();
            return $instance->$method($request, new Response());
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = new $class();
            return $instance->$method($request, new Response());
        }

        throw new \RuntimeException('Invalid route handler');
    }

    private function resolveMiddleware(mixed $spec): \App\Core\Middleware\MiddlewareInterface
    {
        if (is_object($spec)) {
            return $spec;
        }

        if (is_string($spec)) {
            return new $spec();
        }

        throw new \RuntimeException('Cannot resolve middleware: ' . print_r($spec, true));
    }
}
