<?php $inquiry = isset($inquiry) && is_array($inquiry) ? $inquiry : []; ?>
<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #141414; line-height: 1.6;">
    <h1 style="font-size: 24px; margin-bottom: 16px;">We received your inquiry</h1>
    <p>Thank you for reaching out to <?= htmlspecialchars((string) config('app.name', 'Mesh Photography'), ENT_QUOTES, 'UTF-8') ?>.</p>
    <p>Your message has been recorded and will be reviewed shortly. A response will follow after availability and project fit are reviewed.</p>
    <p style="margin-top: 20px;"><strong>Summary</strong></p>
    <ul>
        <li>Service interest: <?= htmlspecialchars((string) (($inquiry['service_interest'] ?? '') !== '' ? ($inquiry['service_interest'] ?? '') : 'General inquiry'), ENT_QUOTES, 'UTF-8') ?></li>
        <li>Preferred date: <?= htmlspecialchars((string) (($inquiry['preferred_date'] ?? '') !== '' ? ($inquiry['preferred_date'] ?? '') : 'Flexible or not provided'), ENT_QUOTES, 'UTF-8') ?></li>
        <li>Location: <?= htmlspecialchars((string) (($inquiry['location'] ?? '') !== '' ? ($inquiry['location'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></li>
    </ul>
    <p style="margin-top: 20px;">This is an automated confirmation. No reply is required unless you need to add more context.</p>
</body>
</html>