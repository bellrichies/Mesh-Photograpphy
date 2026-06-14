<?php

declare(strict_types=1);

namespace App\Core;

class RouteDefinition
{
    private array $middlewares = [];

    public function __construct(
        public readonly string $method,
        public readonly string $pattern,
        public readonly mixed $handler
    ) {}

    public function middleware(array $middlewares): static
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
