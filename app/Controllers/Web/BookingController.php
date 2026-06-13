<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\BookingRequest;
use App\Models\Inquiry;
use App\Models\Service;
use App\Services\BookingRequestService;
use App\Services\SeoService;
use App\Validators\BookingRequestValidator;
use RuntimeException;

class BookingController
{
    public function show(Request $request, Response $response): Response
    {
        $old = app_session()->getFlash('old_input', []);
        $services = (new Service(app_database()))->allPublished(100);
        $selectedService = $this->resolveSelectedService((string) $request->query('service', ''));
        $linkedInquiry = $this->resolveLinkedInquiry((string) $request->query('inquiry', ''));

        $values = [
            'inquiry_id' => $linkedInquiry !== null ? (string) ($linkedInquiry['id'] ?? '') : '',
            'service_id' => $selectedService !== null ? (string) ($selectedService['id'] ?? '') : '',
            'first_name' => $linkedInquiry['first_name'] ?? '',
            'last_name' => $linkedInquiry['last_name'] ?? '',
            'email' => $linkedInquiry['email'] ?? '',
            'phone' => $linkedInquiry['phone'] ?? '',
            'requested_date' => $linkedInquiry['preferred_date'] ?? '',
            'requested_time' => '',
            'event_type' => '',
            'location' => $linkedInquiry['location'] ?? '',
            'hours_needed' => '',
            'guest_count' => '',
            'notes' => '',
        ];

        if ($selectedService === null && $linkedInquiry !== null && (string) ($linkedInquiry['service_interest'] ?? '') !== '') {
            foreach ($services as $service) {
                if (strcasecmp((string) ($service['title'] ?? ''), (string) ($linkedInquiry['service_interest'] ?? '')) === 0) {
                    $values['service_id'] = (string) ($service['id'] ?? '');
                    $selectedService = $service;
                    break;
                }
            }
        }

        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
        }

        $view = new View(dirname(__DIR__, 3));
        $seo = $this->seo()->forArchive([
            'title' => 'Booking Request',
            'description' => 'Request availability, share event details, and start the booking workflow.',
            'canonical_path' => '/booking',
        ]);
        return $response->html($view->render('web/booking/form', [
            'title' => (string) ($seo['meta_title'] ?? 'Booking Request'),
            'values' => $values,
            'services' => $services,
            'selectedService' => $selectedService,
            'linkedInquiry' => $linkedInquiry,
            'seo' => $seo,
        ], 'layouts/main'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $this->ensureRequestIsAllowed();

            $payload = $this->payloadFromRequest($request);
            $this->validatePayload($payload);
            $result = $this->bookingService()->capture($payload);
            app_session()->set('booking_last_submission_at', time());
            app_session()->flash(
                'success',
                ($result['mail_enabled'] ?? false) === true
                    ? 'Thank you. Your booking request has been recorded and a confirmation email is on its way.'
                    : 'Thank you. Your booking request has been recorded and will be reviewed shortly.'
            );

            return $response->redirect('/booking', 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());

            return $response->redirect('/booking', 302);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request): array
    {
        return [
            'inquiry_id' => $this->nullableInt($request->post('inquiry_id', '')),
            'service_id' => $this->nullableInt($request->post('service_id', '')),
            'first_name' => trim((string) $request->post('first_name', '')),
            'last_name' => trim((string) $request->post('last_name', '')),
            'email' => trim((string) $request->post('email', '')),
            'phone' => trim((string) $request->post('phone', '')),
            'requested_date' => trim((string) $request->post('requested_date', '')),
            'requested_time' => $this->nullableTime($request->post('requested_time', '')),
            'event_type' => trim((string) $request->post('event_type', '')),
            'location' => trim((string) $request->post('location', '')),
            'hours_needed' => $this->nullableDecimal($request->post('hours_needed', '')),
            'guest_count' => $this->nullableInt($request->post('guest_count', '')),
            'notes' => trim((string) $request->post('notes', '')),
            'status' => 'new',
            'source_ip' => trim((string) $request->server('REMOTE_ADDR', '')),
            'user_agent' => trim((string) $request->server('HTTP_USER_AGENT', '')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new BookingRequestValidator();
        $input = $payload;
        $input['inquiry_id'] = $payload['inquiry_id'] !== null ? (string) $payload['inquiry_id'] : '';
        $input['service_id'] = $payload['service_id'] !== null ? (string) $payload['service_id'] : '';
        $input['hours_needed'] = $payload['hours_needed'] !== null ? (string) $payload['hours_needed'] : '';
        $input['guest_count'] = $payload['guest_count'] !== null ? (string) $payload['guest_count'] : '';

        if (! $validator->validate($input)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Booking request validation failed.');
        }

        if (($payload['service_id'] ?? null) !== null && ! is_array((new Service(app_database()))->findById((int) $payload['service_id']))) {
            throw new RuntimeException('Selected service was not found.');
        }

        if (($payload['inquiry_id'] ?? null) !== null && ! is_array((new Inquiry(app_database()))->findById((int) $payload['inquiry_id']))) {
            throw new RuntimeException('Linked inquiry was not found.');
        }
    }

    private function ensureRequestIsAllowed(): void
    {
        $lastSubmissionAt = (int) app_session()->get('booking_last_submission_at', 0);
        if ($lastSubmissionAt > 0 && (time() - $lastSubmissionAt) < 15) {
            throw new RuntimeException('Please wait a few seconds before sending another booking request.');
        }
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    private function resolveSelectedService(string $serviceSlug): ?array
    {
        $serviceSlug = trim($serviceSlug);
        if ($serviceSlug === '') {
            return null;
        }

        return (new Service(app_database()))->findPublishedBySlug($serviceSlug);
    }

    private function resolveLinkedInquiry(string $inquiryId): ?array
    {
        $inquiryId = trim($inquiryId);
        if ($inquiryId === '' || ! ctype_digit($inquiryId)) {
            return null;
        }

        return (new Inquiry(app_database()))->findById((int) $inquiryId);
    }

    private function bookingService(): BookingRequestService
    {
        return new BookingRequestService();
    }

    private function nullableInt(mixed $value): ?int
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' && ctype_digit($stringValue) ? (int) $stringValue : null;
    }

    private function nullableTime(mixed $value): ?string
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' ? $stringValue : null;
    }

    private function nullableDecimal(mixed $value): ?string
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' && is_numeric($stringValue) ? $stringValue : null;
    }

    private function seo(): SeoService
    {
        return new SeoService(app_database());
    }
}