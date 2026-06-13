<?php $bookingRequest = isset($bookingRequest) && is_array($bookingRequest) ? $bookingRequest : []; ?>
<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #141414; line-height: 1.6;">
    <h1 style="font-size: 24px; margin-bottom: 16px;">New Booking Request</h1>
    <p>A new availability request has been submitted.</p>
    <table cellpadding="8" cellspacing="0" border="0" style="border-collapse: collapse; width: 100%; max-width: 680px; margin-top: 16px;">
        <tr><td><strong>Name</strong></td><td><?= htmlspecialchars(trim((string) (($bookingRequest['first_name'] ?? '') . ' ' . ($bookingRequest['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Email</strong></td><td><?= htmlspecialchars((string) ($bookingRequest['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Phone</strong></td><td><?= htmlspecialchars((string) (($bookingRequest['phone'] ?? '') !== '' ? ($bookingRequest['phone'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Service</strong></td><td><?= htmlspecialchars((string) (($bookingRequest['service_title'] ?? '') !== '' ? ($bookingRequest['service_title'] ?? '') : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Event Type</strong></td><td><?= htmlspecialchars((string) ($bookingRequest['event_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Requested Date</strong></td><td><?= htmlspecialchars((string) ($bookingRequest['requested_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Requested Time</strong></td><td><?= htmlspecialchars((string) (($bookingRequest['requested_time'] ?? '') !== '' ? substr((string) ($bookingRequest['requested_time'] ?? ''), 0, 5) : 'Not provided'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td><strong>Location</strong></td><td><?= htmlspecialchars((string) ($bookingRequest['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
    </table>
    <?php if ((string) ($bookingRequest['notes'] ?? '') !== ''): ?>
        <h2 style="font-size: 18px; margin-top: 24px;">Notes</h2>
        <p><?= nl2br(htmlspecialchars((string) ($bookingRequest['notes'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endif; ?>
</body>
</html>