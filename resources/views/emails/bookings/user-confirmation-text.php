<?php $bookingRequest = isset($bookingRequest) && is_array($bookingRequest) ? $bookingRequest : []; ?>
We received your booking request

Thank you for checking availability with <?= (string) config('app.name', 'Mesh Photography') ?>.
Your requested date has been recorded and will be reviewed alongside current calendar availability.

Event type: <?= (string) ($bookingRequest['event_type'] ?? '') ?>
Requested date: <?= (string) ($bookingRequest['requested_date'] ?? '') ?>
Location: <?= (string) ($bookingRequest['location'] ?? '') ?>

This is an automated confirmation. A follow-up will come after the request is reviewed.