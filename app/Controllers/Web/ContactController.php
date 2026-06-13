<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;
use App\Models\Service;
use App\Services\InquiryService;
use App\Services\SeoService;
use App\Validators\InquiryValidator;
use RuntimeException;

class ContactController
{
    public function show(Request $request, Response $response): Response
    {
        $old = app_session()->getFlash('old_input', []);
        $serviceInterest = trim((string) $request->query('service', ''));
        $values = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'company_name' => '',
            'service_interest' => $serviceInterest,
            'preferred_date' => '',
            'budget_range' => '',
            'location' => '',
            'referral_source' => '',
            'message' => '',
        ];

        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
        }

        $view = new View(dirname(__DIR__, 3));
        $page = $this->page('contact');
        $seo = $page !== null
            ? $this->seo()->forPage($page)
            : $this->seo()->forArchive([
                'title' => 'Contact',
                'description' => 'Contact the studio for inquiries, availability, and project planning.',
                'canonical_path' => '/contact',
            ]);

        return $response->html($view->render('web/contact', [
            'title' => (string) ($seo['meta_title'] ?? ($page['title'] ?? 'Contact')),
            'values' => $values,
            'services' => (new Service(app_database()))->allPublished(100),
            'contactEmail' => (string) app_setting('contact', 'contact_email', ''),
            'contactPhone' => (string) app_setting('contact', 'phone', ''),
            'contactAddress' => (string) app_setting('contact', 'address', ''),
            'businessHours' => app_setting('contact', 'business_hours', []),
            'page' => $page,
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

            $result = $this->inquiryService()->capture($payload);
            app_session()->set('contact_last_submission_at', time());
            app_session()->flash(
                'success',
                ($result['mail_enabled'] ?? false) === true
                    ? 'Thank you. Your inquiry has been received and a confirmation email is on its way.'
                    : 'Thank you. Your inquiry has been received and will be reviewed shortly.'
            );

            return $response->redirect('/contact', 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());

            return $response->redirect('/contact', 302);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request): array
    {
        $honeypot = trim((string) $request->post('website', ''));
        $status = $honeypot !== '' ? 'spam' : 'new';

        return [
            'first_name' => trim((string) $request->post('first_name', '')),
            'last_name' => trim((string) $request->post('last_name', '')),
            'email' => trim((string) $request->post('email', '')),
            'phone' => trim((string) $request->post('phone', '')),
            'company_name' => trim((string) $request->post('company_name', '')),
            'service_interest' => trim((string) $request->post('service_interest', '')),
            'preferred_date' => $this->nullableDate($request->post('preferred_date', '')),
            'budget_range' => trim((string) $request->post('budget_range', '')),
            'location' => trim((string) $request->post('location', '')),
            'referral_source' => trim((string) $request->post('referral_source', '')),
            'message' => trim((string) $request->post('message', '')),
            'status' => $status,
            'source_ip' => trim((string) $request->server('REMOTE_ADDR', '')),
            'user_agent' => trim((string) $request->server('HTTP_USER_AGENT', '')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new InquiryValidator();

        if ($validator->validate($payload)) {
            return;
        }

        foreach ($validator->errors() as $messages) {
            if (isset($messages[0])) {
                throw new RuntimeException((string) $messages[0]);
            }
        }

        throw new RuntimeException('Inquiry validation failed.');
    }

    private function ensureRequestIsAllowed(): void
    {
        $lastSubmissionAt = (int) app_session()->get('contact_last_submission_at', 0);
        if ($lastSubmissionAt > 0 && (time() - $lastSubmissionAt) < 15) {
            throw new RuntimeException('Please wait a few seconds before sending another inquiry.');
        }
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    private function nullableDate(mixed $value): ?string
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' ? $stringValue : null;
    }

    private function inquiryService(): InquiryService
    {
        return new InquiryService();
    }

    private function seo(): SeoService
    {
        return new SeoService(app_database());
    }

    private function page(string $slug): ?array
    {
        return (new Page(app_database()))->findPublishedBySlug($slug);
    }
}
