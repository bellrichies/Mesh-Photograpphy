<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Validator;
use App\Models\User;
use App\Services\LoginThrottleService;

class AuthController
{
    public function showLogin(Request $request, Response $response): Response
    {
        $view = new View(dirname(__DIR__, 3));

        return $response->html(
            $view->render('auth/login', [
                'title' => 'Admin Login',
                'adminPath' => admin_url(),
            ], 'layouts/auth')
        );
    }

    public function login(Request $request, Response $response): Response
    {
        $adminPath = admin_url();
        $securityLogger = app_security_logger();

        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            $securityLogger->log('auth.login_csrf_failed', $request, 'auth', null, 'Rejected login request with invalid CSRF token.');
            return $this->redirectToLogin($response, 'Session token mismatch. Please try again.', (string) $request->post('email', ''));
        }

        $validator = new Validator();
        $payload = [
            'email' => (string) $request->post('email', ''),
            'password' => (string) $request->post('password', ''),
        ];

        $isValid = $validator->validate($payload, [
            'email' => 'required|email|max:190',
            'password' => 'required|min:8|max:255',
        ]);

        if (! $isValid) {
            return $this->redirectToLogin($response, $this->firstError($validator->errors()) ?? 'Invalid login request.', $payload['email']);
        }

        $throttle = new LoginThrottleService(app_session());
        $normalizedEmail = strtolower(trim($payload['email']));
        $identifier = $throttle->key('login', $normalizedEmail, $request->ip(), substr($request->userAgent(), 0, 160));

        if ($throttle->isBlocked($identifier)) {
            $retryAfter = $throttle->retryAfter($identifier);
            $securityLogger->log('auth.login_blocked', $request, 'auth', null, 'Blocked login request due to rate limiting.', [
                'email' => $normalizedEmail,
                'retry_after' => $retryAfter,
            ]);
            return $this->redirectToLogin($response, 'Too many login attempts. Try again in ' . $retryAfter . ' seconds.', $payload['email']);
        }

        $userModel = new User(app_database());
        $user = $userModel->findByEmail($payload['email']);

        if (! is_array($user) || ! isset($user['password_hash']) || ! password_verify($payload['password'], (string) $user['password_hash'])) {
            $throttle->registerFailure($identifier);
            $securityLogger->log('auth.login_failed', $request, 'auth', null, 'Failed login attempt.', [
                'email' => $normalizedEmail,
            ]);
            return $this->redirectToLogin($response, 'Invalid credentials.', $payload['email']);
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            $throttle->registerFailure($identifier);
            $securityLogger->log('auth.login_inactive', $request, 'user', (int) ($user['id'] ?? 0), 'Rejected login for inactive account.', [
                'email' => $normalizedEmail,
            ]);
            return $this->redirectToLogin($response, 'Your account is inactive.', $payload['email']);
        }

        $throttle->clear($identifier);

        $userId = (int) ($user['id'] ?? 0);
        app_auth()->login($userId);
        $userModel->touchLastLoginAt($userId);
        $securityLogger->log('auth.login', $request, 'user', $userId, 'Admin user signed in.', [
            'email' => $normalizedEmail,
        ], $userId);

        app_session()->flash('success', 'Welcome back.');

        return $response->redirect($adminPath . '/dashboard', 302);
    }

    public function logout(Request $request, Response $response): Response
    {
        $adminPath = admin_url();
        $tokenKey = (string) config('app.csrf_token_name', '_token');

        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            app_security_logger()->log('auth.logout_csrf_failed', $request, 'auth', null, 'Rejected logout request with invalid CSRF token.');
            app_session()->flash('error', 'Session token mismatch. Please try again.');
            return $response->redirect($adminPath . '/dashboard', 302);
        }

        $userId = app_auth()->id();
        app_security_logger()->log('auth.logout', $request, 'user', $userId, 'Admin user signed out.', [], $userId);
        app_auth()->logout();
        app_session()->flash('success', 'Signed out successfully.');

        return $response->redirect($adminPath . '/login', 302);
    }

    /**
     * @param array<string, array<int, string>> $errors
     */
    private function firstError(array $errors): ?string
    {
        foreach ($errors as $fieldErrors) {
            if (isset($fieldErrors[0])) {
                return $fieldErrors[0];
            }
        }

        return null;
    }

    private function redirectToLogin(Response $response, string $message, string $email = ''): Response
    {
        if ($email !== '') {
            app_session()->flash('old_input', ['email' => $email]);
        }

        app_session()->flash('error', $message);

        return $response->redirect('/' . trim((string) config('app.admin_path', '/admin'), '/') . '/login', 302);
    }
}
