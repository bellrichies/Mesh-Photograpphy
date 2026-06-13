<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (! app_auth()->check()) {
            app_session()->flash('error', $this->failureMessage(app_auth()->pullSecurityFailureReason()));
            return (new Response())->redirect('/' . trim((string) config('app.admin_path', '/admin'), '/') . '/login');
        }

        return $next($request);
    }

    private function failureMessage(?string $reason): string
    {
        return match ($reason) {
            'session_idle_timeout' => 'Your session expired due to inactivity. Please sign in again.',
            'session_fingerprint_mismatch', 'session_security_missing' => 'Your session could not be verified. Please sign in again.',
            default => 'Please sign in to access the admin area.',
        };
    }
}
