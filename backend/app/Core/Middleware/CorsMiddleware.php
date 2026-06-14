<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $origin         = $request->server('HTTP_ORIGIN', '');
        $allowedOrigins = config('cors.allowed_origins', ['http://localhost:5173']);

        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-Id');
            header('Access-Control-Max-Age: 86400');
        }

        if ($request->method() === 'OPTIONS') {
            return (new Response())->json([], 204);
        }

        return $next($request);
    }
}
