<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\HttpException;

class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission) {}

    public static function for(string $permission): static
    {
        return new static($permission);
    }

    public function handle(Request $request, callable $next): Response
    {
        $payload     = $request->authPayload();
        $permissions = $payload['permissions'] ?? [];

        if (!in_array($this->permission, $permissions, true)) {
            throw new HttpException(
                403,
                "You do not have permission to perform this action."
            );
        }

        return $next($request);
    }
}
