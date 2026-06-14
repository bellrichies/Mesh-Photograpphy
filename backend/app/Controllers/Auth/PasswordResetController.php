<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class PasswordResetController extends Controller
{
    public function request(Request $request, Response $response): Response
    {
        $data  = $request->json();
        $email = trim((string) ($data['email'] ?? ''));

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->validationError(['email' => ['A valid email address is required.']]);
        }

        $db   = app_database();
        $user = $db->query(
            'SELECT id, email FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1',
            [$email]
        )->fetch();

        // Always return 200 to avoid user enumeration
        if (!$user) {
            return $this->success(null, 'If that email exists, a reset link has been sent.');
        }

        // Invalidate old tokens for this email
        $db->query(
            'UPDATE password_reset_tokens SET used_at = NOW() WHERE email = ? AND used_at IS NULL',
            [$email]
        );

        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->query(
            'INSERT INTO password_reset_tokens (email, token, expires_at, created_at) VALUES (?,?,?,NOW())',
            [$email, $token, $expiresAt]
        );

        $appUrl   = rtrim($_ENV['APP_URL'] ?? '', '/');
        $resetUrl = $appUrl . '/admin/reset-password?token=' . $token;
        $siteName = $_ENV['APP_NAME'] ?? 'Mesh Photography';
        $adminEmail = $_ENV['MAIL_FROM'] ?? '';

        if ($adminEmail) {
            $subject = "Password Reset — {$siteName}";
            $body    = "You requested a password reset for your {$siteName} account.\n\n"
                . "Click the link below to reset your password (expires in 1 hour):\n\n"
                . $resetUrl . "\n\n"
                . "If you did not request this, ignore this email.\n";

            @mail($email, $subject, $body, "From: {$siteName} <{$adminEmail}>\r\nContent-Type: text/plain; charset=UTF-8");
        }

        return $this->success(null, 'If that email exists, a reset link has been sent.');
    }

    public function reset(Request $request, Response $response): Response
    {
        $data     = $request->json();
        $token    = trim((string) ($data['token'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if (!$token) {
            return $this->validationError(['token' => ['Reset token is required.']]);
        }

        if (strlen($password) < 8) {
            return $this->validationError(['password' => ['Password must be at least 8 characters.']]);
        }

        $db  = app_database();
        $row = $db->query(
            'SELECT * FROM password_reset_tokens
             WHERE token = ? AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1',
            [$token]
        )->fetch();

        if (!$row) {
            return $this->error('Reset token is invalid or has expired.', 400);
        }

        $user = $db->query(
            'SELECT id FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1',
            [$row['email']]
        )->fetch();

        if (!$user) {
            return $this->error('User not found.', 404);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $db->query(
            'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
            [$hash, (int) $user['id']]
        );

        $db->query(
            'UPDATE password_reset_tokens SET used_at = NOW() WHERE token = ?',
            [$token]
        );

        // Revoke all refresh tokens for this user
        $db->query(
            'DELETE FROM user_refresh_tokens WHERE user_id = ?',
            [(int) $user['id']]
        );

        return $this->success(null, 'Password reset successfully. You can now log in.');
    }
}
