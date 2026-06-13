<?php $bookingRequest = isset($bookingRequest) && is_array($bookingRequest) ? $bookingRequest : []; ?>
<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #141414; line-height: 1.6;">
    <h1 style="font-size: 24px; margin-bottom: 16px;">We received your booking request</h1>
    <p>Thank you for checking availability with <?= htmlspecialchars((string) config('app.name', 'Mesh Photography'), ENT_QUOTES, 'UTF-8') ?>.</p>
    <p>Your requested date has been recorded and will be reviewed alongside current calendar availability.</p>
    <ul>
        <li>Event type: <?= htmlspecialchars((string) ($bookingRequest['event_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        <li>Requested date: <?= htmlspecialchars((string) ($bookingRequest['requested_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        <li>Location: <?= htmlspecialchars((string) ($bookingRequest['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
    </ul>
    <p>This is an automated confirmation. A follow-up will come after the request is reviewed.</p>
</body>
</html>