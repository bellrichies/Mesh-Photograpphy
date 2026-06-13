<?php $bookingRequest = isset($bookingRequest) && is_array($bookingRequest) ? $bookingRequest : []; ?>
New Booking Request

Name: <?= trim((string) (($bookingRequest['first_name'] ?? '') . ' ' . ($bookingRequest['last_name'] ?? ''))) ?>
Email: <?= (string) ($bookingRequest['email'] ?? '') ?>
Phone: <?= (string) (($bookingRequest['phone'] ?? '') !== '' ? ($bookingRequest['phone'] ?? '') : 'Not provided') ?>
Service: <?= (string) (($bookingRequest['service_title'] ?? '') !== '' ? ($bookingRequest['service_title'] ?? '') : 'Not provided') ?>
Event Type: <?= (string) ($bookingRequest['event_type'] ?? '') ?>
Requested Date: <?= (string) ($bookingRequest['requested_date'] ?? '') ?>
Requested Time: <?= (string) (($bookingRequest['requested_time'] ?? '') !== '' ? substr((string) ($bookingRequest['requested_time'] ?? ''), 0, 5) : 'Not provided') ?>
Location: <?= (string) ($bookingRequest['location'] ?? '') ?>

Notes:
<?= (string) (($bookingRequest['notes'] ?? '') !== '' ? ($bookingRequest['notes'] ?? '') : 'None') ?>