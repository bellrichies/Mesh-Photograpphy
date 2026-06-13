<?php $inquiry = isset($inquiry) && is_array($inquiry) ? $inquiry : []; ?>
We received your inquiry

Thank you for reaching out to <?= (string) config('app.name', 'Mesh Photography') ?>.
Your message has been recorded and will be reviewed shortly.

Service interest: <?= (string) (($inquiry['service_interest'] ?? '') !== '' ? ($inquiry['service_interest'] ?? '') : 'General inquiry') ?>
Preferred date: <?= (string) (($inquiry['preferred_date'] ?? '') !== '' ? ($inquiry['preferred_date'] ?? '') : 'Flexible or not provided') ?>
Location: <?= (string) (($inquiry['location'] ?? '') !== '' ? ($inquiry['location'] ?? '') : 'Not provided') ?>

This is an automated confirmation. No reply is required unless you need to add more context.