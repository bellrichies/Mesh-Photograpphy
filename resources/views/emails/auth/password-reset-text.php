<?php
$user = isset($user) && is_array($user) ? $user : [];
$resetUrl = (string) ($resetUrl ?? '#');
$expiresAt = (string) ($expiresAt ?? '');
?>
Password reset instructions

Hello <?= (string) (($user['first_name'] ?? '') !== '' ? ($user['first_name'] ?? '') : 'there') ?>,

A password reset was requested for your account. Use the link below to continue:
<?= $resetUrl ?>

If you did not request this, you can ignore this message.<?= $expiresAt !== '' ? ' This link expires at ' . $expiresAt . '.' : '' ?>