<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (app_auth()->check()) {
            return (new Response())->redirect('/' . trim((string) config('app.admin_path', '/admin'), '/') . '/dashboard');
        }

        $failureReason = app_auth()->pullSecurityFailureReason();
        if ($failureReason !== null) {
            app_session()->flash('error', match ($failureReason) {
                'session_idle_timeout' => 'Your session expired due to inactivity. Please sign in again.',
                default => 'Your session could not be verified. Please sign in again.',
            });
        }

        return $next($request);
    }
}
