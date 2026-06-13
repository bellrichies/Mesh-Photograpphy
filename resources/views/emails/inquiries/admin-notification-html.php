<?php $inquiry = isset($inquiry) && is_array($inquiry) ? $inquiry : []; ?>
<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #141414; line-height: 1.6;">
    <h1 style="font-size: 24px; margin-bottom: 16px;">New Inquiry Received</h1>
    <p>A new inquiry has been submitted through the public contact form.</p>
    <table cellpadding="8" cellspacing="0" border="0" style="border-collapse: collapse; width: 100%; max-width: 680px; margin-top: 16px;">
        <tr><td><strong>Name</strong></td><td><?= htmlspecialchars(trim((string) (($inquiry['first_name'] ?? '') . ' ' . ($inquiry['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Email</strong></td><td><?= htmlspecialchars((string) ($inquiry['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Phone</strong></td><td><?= htmlspecialchars((string) (($inquiry['phone'] ?? '') !== '' ? ($inquiry['phone'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Service</strong></td><td><?= htmlspecialchars((string) (($inquiry['service_interest'] ?? '') !== '' ? ($inquiry['service_interest'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Preferred Date</strong></td><td><?= htmlspecialchars((string) (($inquiry['preferred_date'] ?? '') !== '' ? ($inquiry['preferred_date'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Budget</strong></td><td><?= htmlspecialchars((string) (($inquiry['budget_range'] ?? '') !== '' ? ($inquiry['budget_range'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Location</strong></td><td><?= htmlspecialchars((string) (($inquiry['location'] ?? '') !== '' ? ($inquiry['location'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Referral Source</strong></td><td><?= htmlspecialchars((string) (($inquiry['referral_source'] ?? '') !== '' ? ($inquiry['referral_source'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
    </table>
    <h2 style="font-size: 18px; margin-top: 24px;">Message</h2>
    <p><?= nl2br(htmlspecialchars((string) ($inquiry['message'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
</body>
</html>