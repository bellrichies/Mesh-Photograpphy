<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\View;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public function __construct(
        private readonly LogService $logger = new LogService(),
    ) {
    }

    public function isEnabled(): bool
    {
        $setting = app_setting('email', 'mail_enabled', null);
        if ($setting !== null && $setting !== '') {
            return in_array((string) $setting, ['1', 'true', 'on'], true);
        }

        return (bool) config('mail.enabled', true);
    }

    /**
     * @param array<int, string> $to
     * @param array<string, mixed> $data
     */
    public function sendTemplatedMessage(
        array $to,
        string $subject,
        string $htmlView,
        string $textView,
        array $data,
        ?array $replyTo = null,
    ): bool {
        if (! $this->isEnabled()) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $this->configureMailer($mail);
            $from = $this->resolveFrom();
            $mail->setFrom($from['address'], $from['name']);

            if ($replyTo !== null && ($replyTo['address'] ?? '') !== '') {
                $mail->addReplyTo((string) $replyTo['address'], (string) ($replyTo['name'] ?? ''));
            } else {
                $defaultReplyTo = $this->resolveReplyTo();
                if (($defaultReplyTo['address'] ?? '') !== '') {
                    $mail->addReplyTo((string) $defaultReplyTo['address'], (string) ($defaultReplyTo['name'] ?? ''));
                }
            }

            foreach ($to as $address) {
                $normalized = trim($address);
                if ($normalized !== '') {
                    $mail->addAddress($normalized);
                }
            }

            $view = new View(dirname(__DIR__, 2));
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $view->render($htmlView, $data);
            $mail->AltBody = $view->render($textView, $data);
            $mail->send();

            return true;
        } catch (MailException $exception) {
            $this->logger->error('mail', 'Mail delivery failed.', [
                'subject' => $subject,
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $inquiry
     * @param array<int, string> $recipients
     */
    public function sendAdminInquiryNotification(array $inquiry, array $recipients): bool
    {
        return $this->sendTemplatedMessage(
            $recipients,
            'New Inquiry: ' . trim((string) (($inquiry['first_name'] ?? '') . ' ' . ($inquiry['last_name'] ?? ''))),
            'emails/inquiries/admin-notification-html',
            'emails/inquiries/admin-notification-text',
            ['inquiry' => $inquiry],
            [
                'address' => (string) ($inquiry['email'] ?? ''),
                'name' => trim((string) (($inquiry['first_name'] ?? '') . ' ' . ($inquiry['last_name'] ?? ''))),
            ]
        );
    }

    /**
     * @param array<string, mixed> $inquiry
     */
    public function sendInquiryConfirmation(array $inquiry): bool
    {
        $address = trim((string) ($inquiry['email'] ?? ''));
        if ($address === '') {
            return false;
        }

        return $this->sendTemplatedMessage(
            [$address],
            'We received your inquiry',
            'emails/inquiries/user-confirmation-html',
            'emails/inquiries/user-confirmation-text',
            ['inquiry' => $inquiry]
        );
    }

    /**
     * @param array<string, mixed> $bookingRequest
     * @param array<int, string> $recipients
     */
    public function sendAdminBookingRequestNotification(array $bookingRequest, array $recipients): bool
    {
        return $this->sendTemplatedMessage(
            $recipients,
            'New Booking Request: ' . trim((string) (($bookingRequest['first_name'] ?? '') . ' ' . ($bookingRequest['last_name'] ?? ''))),
            'emails/bookings/admin-notification-html',
            'emails/bookings/admin-notification-text',
            ['bookingRequest' => $bookingRequest],
            [
                'address' => (string) ($bookingRequest['email'] ?? ''),
                'name' => trim((string) (($bookingRequest['first_name'] ?? '') . ' ' . ($bookingRequest['last_name'] ?? ''))),
            ]
        );
    }

    /**
     * @param array<string, mixed> $bookingRequest
     */
    public function sendBookingRequestConfirmation(array $bookingRequest): bool
    {
        $address = trim((string) ($bookingRequest['email'] ?? ''));
        if ($address === '') {
            return false;
        }

        return $this->sendTemplatedMessage(
            [$address],
            'We received your booking request',
            'emails/bookings/user-confirmation-html',
            'emails/bookings/user-confirmation-text',
            ['bookingRequest' => $bookingRequest]
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    public function sendPasswordResetEmail(array $user, string $resetUrl, string $expiresAt): bool
    {
        $address = trim((string) ($user['email'] ?? ''));
        if ($address === '') {
            return false;
        }

        return $this->sendTemplatedMessage(
            [$address],
            'Password reset instructions',
            'emails/auth/password-reset-html',
            'emails/auth/password-reset-text',
            [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expiresAt' => $expiresAt,
            ]
        );
    }

    /**
     * @return array{address: string, name: string}
     */
    private function resolveFrom(): array
    {
        $address = trim((string) app_setting('email', 'mailer_from_email', (string) config('mail.from.address', '')));
        $name = trim((string) app_setting('email', 'mailer_from_name', (string) config('mail.from.name', config('app.name', 'Mesh Photography'))));

        return [
            'address' => $address,
            'name' => $name !== '' ? $name : (string) config('app.name', 'Mesh Photography'),
        ];
    }

    /**
     * @return array{address: string, name: string}
     */
    private function resolveReplyTo(): array
    {
        $address = trim((string) app_setting('email', 'mailer_reply_to', (string) config('mail.reply_to.address', '')));
        $name = trim((string) config('mail.reply_to.name', ''));

        return ['address' => $address, 'name' => $name];
    }

    private function configureMailer(PHPMailer $mail): void
    {
        $mailer = (string) config('mail.default', 'smtp');
        if ($mailer === 'smtp') {
            $mail->isSMTP();
            $mail->Host = (string) config('mail.mailers.smtp.host', '127.0.0.1');
            $mail->Port = (int) config('mail.mailers.smtp.port', 1025);
            $mail->SMTPAuth = trim((string) config('mail.mailers.smtp.username', '')) !== '';
            $mail->Username = (string) config('mail.mailers.smtp.username', '');
            $mail->Password = (string) config('mail.mailers.smtp.password', '');
            $mail->Timeout = (int) config('mail.mailers.smtp.timeout', 30);

            $encryption = trim((string) config('mail.mailers.smtp.encryption', ''));
            if ($encryption !== '') {
                $mail->SMTPSecure = $encryption;
            }
        }
    }
}