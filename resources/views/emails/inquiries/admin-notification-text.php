<?php $inquiry = isset($inquiry) && is_array($inquiry) ? $inquiry : []; ?>
New Inquiry Received

Name: <?= trim((string) (($inquiry['first_name'] ?? '') . ' ' . ($inquiry['last_name'] ?? ''))) ?>
Email: <?= (string) ($inquiry['email'] ?? '') ?>
Phone: <?= (string) (($inquiry['phone'] ?? '') !== '' ? ($inquiry['phone'] ?? '') : 'Not provided') ?>
Service: <?= (string) (($inquiry['service_interest'] ?? '') !== '' ? ($inquiry['service_interest'] ?? '') : 'Not provided') ?>
Preferred Date: <?= (string) (($inquiry['preferred_date'] ?? '') !== '' ? ($inquiry['preferred_date'] ?? '') : 'Not provided') ?>
Budget: <?= (string) (($inquiry['budget_range'] ?? '') !== '' ? ($inquiry['budget_range'] ?? '') : 'Not provided') ?>
Location: <?= (string) (($inquiry['location'] ?? '') !== '' ? ($inquiry['location'] ?? '') : 'Not provided') ?>
Referral Source: <?= (string) (($inquiry['referral_source'] ?? '') !== '' ? ($inquiry['referral_source'] ?? '') : 'Not provided') ?>

Message:
<?= (string) ($inquiry['message'] ?? '') ?>