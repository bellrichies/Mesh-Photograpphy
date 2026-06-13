<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    /** @var array<string, mixed> */
    private array $query;

    /** @var array<string, mixed> */
    private array $post;

    /** @var array<string, mixed> */
    private array $files;

    /** @var array<string, mixed> */
    private array $server;

    /** @var array<string, mixed> */
    private array $cookies;

    private string $method;

    private string $path;

    /** @var array<string, string> */
    private array $routeParams = [];

    /**
     * @param array<string, mixed>|null $query
     * @param array<string, mixed>|null $post
     * @param array<string, mixed>|null $files
     * @param array<string, mixed>|null $server
     * @param array<string, mixed>|null $cookies
     */
    public function __construct(
        ?array $query = null,
        ?array $post = null,
        ?array $files = null,
        ?array $server = null,
        ?array $cookies = null
    ) {
        $this->query = $query ?? $_GET;
        $this->post = $post ?? $_POST;
        $this->files = $files ?? $_FILES;
        $this->server = $server ?? $_SERVER;
        $this->cookies = $cookies ?? $_COOKIE;

        $this->method = $this->detectMethod();
        $this->path = $this->detectPath();
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function ip(): string
    {
        return trim((string) ($this->server['REMOTE_ADDR'] ?? ''));
    }

    public function userAgent(): string
    {
        return trim((string) ($this->server['HTTP_USER_AGENT'] ?? ''));
    }

    public function isAjax(): bool
    {
        $requestedWith = strtolower((string) $this->server('HTTP_X_REQUESTED_WITH', ''));
        return $requestedWith === 'xmlhttprequest' || $this->input('_ajax', '0') === '1';
    }

    public function expectsJson(): bool
    {
        $accept = strtolower((string) $this->server('HTTP_ACCEPT', ''));

        return $this->isAjax()
            || str_contains($accept, 'application/json')
            || str_starts_with($this->path(), '/api');
    }

    public function isAdminRequest(): bool
    {
        $adminPath = '/' . trim((string) config('app.admin_path', '/admin'), '/');
        $adminPath = $adminPath === '/' ? '/admin' : $adminPath;
        $path = $this->path();

        return $path === $adminPath || str_starts_with($path, $adminPath . '/');
    }

    /**
     * @param array<string, string> $params
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    private function detectMethod(): string
    {
        $method = strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));

        if ($method === 'POST') {
            $methodOverride = strtoupper((string) ($this->post['_method'] ?? ''));
            if (in_array($methodOverride, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $methodOverride;
            }
        }

        return $method;
    }

    private function detectPath(): string
    {
        $requestUri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($requestUri, PHP_URL_PATH);

        $scriptName = (string) ($this->server['SCRIPT_NAME'] ?? '');
        $scriptPath = str_replace('\\', '/', $scriptName);
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        $baseCandidates = [];
        if ($scriptPath !== '' && $scriptPath !== '/') {
            $baseCandidates[] = rtrim($scriptPath, '/');
        }

        if ($scriptDir !== '' && $scriptDir !== '/') {
            $baseCandidates[] = $scriptDir;

            if (basename($scriptDir) === 'public') {
                $parentDir = rtrim(str_replace('\\', '/', dirname($scriptDir)), '/');
                if ($parentDir !== '' && $parentDir !== '/') {
                    $baseCandidates[] = $parentDir;
                }
            }
        }

        $appUrl = (string) ($this->server['APP_URL'] ?? '');
        if ($appUrl !== '') {
            $appBasePath = rtrim((string) parse_url($appUrl, PHP_URL_PATH), '/');
            if ($appBasePath !== '' && $appBasePath !== '/') {
                $baseCandidates[] = $appBasePath;
            }
        }

        foreach (array_unique($baseCandidates) as $candidate) {
            if ($path === $candidate) {
                $path = '/';
                break;
            }

            if (str_starts_with($path, $candidate . '/')) {
                $path = substr($path, strlen($candidate)) ?: '/';
                break;
            }
        }

        $normalized = '/' . ltrim($path, '/');
        return rtrim($normalized, '/') === '' ? '/' : rtrim($normalized, '/');
    }
}
