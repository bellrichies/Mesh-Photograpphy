<?php
$user = isset($user) && is_array($user) ? $user : [];
$resetUrl = (string) ($resetUrl ?? '#');
$expiresAt = (string) ($expiresAt ?? '');
?>
<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #141414; line-height: 1.6;">
    <h1 style="font-size: 24px; margin-bottom: 16px;">Password reset instructions</h1>
    <p>Hello <?= htmlspecialchars((string) (($user['first_name'] ?? '') !== '' ? ($user['first_name'] ?? '') : 'there'), ENT_QUOTES, 'UTF-8') ?>,</p>
    <p>A password reset was requested for your account. Use the link below to continue.</p>
    <p style="margin: 20px 0;"><a href="<?= htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') ?>" style="display: inline-block; padding: 12px 18px; background: #141414; color: #ffffff; text-decoration: none;">Reset Password</a></p>
    <p>If you did not request this, you can ignore this message.<?= $expiresAt !== '' ? ' This link expires at ' . htmlspecialchars($expiresAt, ENT_QUOTES, 'UTF-8') . '.' : '' ?></p>
</body>
</html>