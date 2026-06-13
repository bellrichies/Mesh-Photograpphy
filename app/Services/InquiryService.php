<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Inquiry;

class InquiryService
{
    public function __construct(
        private readonly ?Inquiry $inquiries = null,
        private readonly ?MailService $mailService = null,
        private readonly ?LogService $logger = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{inquiry_id: int, mail_enabled: bool, admin_notification_sent: bool, user_confirmation_sent: bool}
     */
    public function capture(array $payload): array
    {
        $inquiryId = $this->inquiryModel()->create($payload);
        $savedInquiry = $this->inquiryModel()->findById($inquiryId) ?? array_merge($payload, ['id' => $inquiryId]);

        $mailEnabled = $this->mailService()->isEnabled() && (string) ($payload['status'] ?? 'new') !== 'spam';
        $adminSent = false;
        $userSent = false;

        if ($mailEnabled) {
            $recipients = $this->resolveInquiryRecipients();

            if ($recipients !== []) {
                $adminSent = $this->mailService()->sendAdminInquiryNotification($savedInquiry, $recipients);
            }

            $userSent = $this->mailService()->sendInquiryConfirmation($savedInquiry);

            if (! $adminSent || ! $userSent) {
                $this->logger()->error('mail', 'Inquiry mail workflow completed with failures.', [
                    'inquiry_id' => $inquiryId,
                    'admin_notification_sent' => $adminSent,
                    'user_confirmation_sent' => $userSent,
                ]);
            }
        }

        return [
            'inquiry_id' => $inquiryId,
            'mail_enabled' => $mailEnabled,
            'admin_notification_sent' => $adminSent,
            'user_confirmation_sent' => $userSent,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveInquiryRecipients(): array
    {
        $configured = app_setting('email', 'inquiry_recipients', []);
        if (is_array($configured)) {
            $recipients = array_values(array_filter(array_map('strval', $configured), static fn (string $address): bool => trim($address) !== ''));
            if ($recipients !== []) {
                return $recipients;
            }
        }

        $contactEmail = trim((string) app_setting('contact', 'contact_email', ''));
        return $contactEmail !== '' ? [$contactEmail] : [];
    }

    private function inquiryModel(): Inquiry
    {
        return $this->inquiries ?? new Inquiry(app_database());
    }

    private function mailService(): MailService
    {
        return $this->mailService ?? new MailService($this->logger());
    }

    private function logger(): LogService
    {
        return $this->logger ?? new LogService();
    }
}