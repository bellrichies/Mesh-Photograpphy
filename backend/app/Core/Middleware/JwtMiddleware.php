<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\HttpException;

class JwtMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new HttpException(401, 'Authentication required');
        }

        $payload = app_jwt()->decode($token);
        $request->setAuthPayload($payload);

        return $next($request);
    }
}
