<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BookingRequest;

class BookingRequestService
{
    public function __construct(
        private readonly ?BookingRequest $bookingRequests = null,
        private readonly ?MailService $mailService = null,
        private readonly ?LogService $logger = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{booking_request_id: int, mail_enabled: bool, admin_notification_sent: bool, user_confirmation_sent: bool}
     */
    public function capture(array $payload): array
    {
        $bookingRequestId = $this->bookingModel()->create($payload);
        $savedBookingRequest = $this->bookingModel()->findById($bookingRequestId) ?? array_merge($payload, ['id' => $bookingRequestId]);

        $mailEnabled = $this->mailService()->isEnabled();
        $adminSent = false;
        $userSent = false;

        if ($mailEnabled) {
            $recipients = $this->resolveRecipients();

            if ($recipients !== []) {
                $adminSent = $this->mailService()->sendAdminBookingRequestNotification($savedBookingRequest, $recipients);
            }

            $userSent = $this->mailService()->sendBookingRequestConfirmation($savedBookingRequest);

            if (! $adminSent || ! $userSent) {
                $this->logger()->error('mail', 'Booking mail workflow completed with failures.', [
                    'booking_request_id' => $bookingRequestId,
                    'admin_notification_sent' => $adminSent,
                    'user_confirmation_sent' => $userSent,
                ]);
            }
        }

        return [
            'booking_request_id' => $bookingRequestId,
            'mail_enabled' => $mailEnabled,
            'admin_notification_sent' => $adminSent,
            'user_confirmation_sent' => $userSent,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveRecipients(): array
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

    private function bookingModel(): BookingRequest
    {
        return $this->bookingRequests ?? new BookingRequest(app_database());
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