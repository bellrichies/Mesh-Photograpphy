<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        $cspParts = config('security.csp', []);
        if ($cspParts) {
            header('Content-Security-Policy: ' . implode('; ', $cspParts));
        }

        header('X-Frame-Options: ' . config('security.x_frame_options', 'DENY'));
        header('X-Content-Type-Options: ' . config('security.x_content_type_options', 'nosniff'));
        header('Referrer-Policy: ' . config('security.referrer_policy', 'strict-origin-when-cross-origin'));
        header('Permissions-Policy: ' . config('security.permissions_policy', 'camera=(), microphone=(), geolocation=()'));
        header('X-XSS-Protection: 0'); // Disabled per OWASP — modern CSP is the defence

        if (env('APP_ENV') === 'production') {
            $maxAge     = (int) config('security.hsts_max_age', 31536000);
            $hsts       = "max-age={$maxAge}";
            if (config('security.hsts_include_subdomains', true)) {
                $hsts .= '; includeSubDomains';
            }
            if (config('security.hsts_preload', true)) {
                $hsts .= '; preload';
            }
            header("Strict-Transport-Security: {$hsts}");
        }

        return $response;
    }
}
