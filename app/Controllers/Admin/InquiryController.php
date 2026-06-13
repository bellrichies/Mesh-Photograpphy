<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Inquiry;
use App\Models\InquiryNote;
use App\Models\Service;
use App\Validators\InquiryNoteValidator;
use RuntimeException;

class InquiryController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $data = $this->indexData($request);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/inquiries/index', [
            'title' => 'Inquiries',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Inquiries', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $data['items'],
            'filters' => $data['filters'],
            'services' => $data['services'],
            'pagination' => $data['pagination'],
        ], 'layouts/admin'));
    }

    public function filter(Request $request, Response $response): Response
    {
        $data = $this->indexData($request);

        return $this->jsonSuccess($response, 'Inquiries loaded successfully.', [
            'html' => $this->renderResults($data),
        ], [
            'pagination' => $data['pagination'],
            'filters' => $data['filters'],
        ]);
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $inquiryId = (int) ($params['id'] ?? 0);
        $inquiry = (new Inquiry(app_database()))->findById($inquiryId);
        if (! is_array($inquiry)) {
            throw new HttpException(404, 'Inquiry not found.');
        }

        $notes = (new InquiryNote(app_database()))->byInquiryId($inquiryId);
        $old = app_session()->getFlash('old_input', []);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/inquiries/show', [
            'title' => 'Inquiry Details',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Inquiries', 'href' => $this->adminPath() . '/inquiries'],
                ['label' => 'Details', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'inquiry' => $inquiry,
            'notes' => $notes,
            'oldInput' => is_array($old) ? $old : [],
        ], 'layouts/admin'));
    }

    public function updateStatus(Request $request, Response $response, array $params): Response
    {
        $inquiryId = (int) ($params['id'] ?? 0);
        $inquiry = (new Inquiry(app_database()))->findById($inquiryId);
        if (! is_array($inquiry)) {
            throw new HttpException(404, 'Inquiry not found.');
        }

        try {
            $this->verifyCsrf($request);
            $status = trim((string) $request->post('status', ''));
            if (! in_array($status, ['new', 'in_progress', 'responded', 'archived', 'spam'], true)) {
                throw new RuntimeException('Please select a valid inquiry status.');
            }

            (new Inquiry(app_database()))->updateStatus($inquiryId, $status);

            if ($this->isAjaxRequest($request)) {
                return $this->jsonSuccess($response, 'Inquiry status updated successfully.', [
                    'status' => $status,
                    'status_label' => ucwords(str_replace('_', ' ', $status)),
                    'badge_class' => $this->statusBadgeClass($status),
                ]);
            }

            app_session()->flash('success', 'Inquiry status updated successfully.');
        } catch (RuntimeException $exception) {
            if ($this->isAjaxRequest($request)) {
                return $this->jsonError($response, $exception->getMessage());
            }

            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/inquiries/view/' . $inquiryId, 302);
    }

    public function addNote(Request $request, Response $response, array $params): Response
    {
        $inquiryId = (int) ($params['id'] ?? 0);
        $inquiry = (new Inquiry(app_database()))->findById($inquiryId);
        if (! is_array($inquiry)) {
            throw new HttpException(404, 'Inquiry not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = ['note' => trim((string) $request->post('note', ''))];
            $validator = new InquiryNoteValidator();

            if (! $validator->validate($payload)) {
                foreach ($validator->errors() as $messages) {
                    if (isset($messages[0])) {
                        throw new RuntimeException((string) $messages[0]);
                    }
                }
            }

            (new InquiryNote(app_database()))->create($inquiryId, app_auth()->id(), $payload['note']);
            app_session()->flash('success', 'Inquiry note added successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', ['note' => (string) $request->post('note', '')]);
        }

        return $response->redirect($this->adminPath() . '/inquiries/view/' . $inquiryId, 302);
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    private function adminPath(): string
    {
        return admin_url();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, filters: array<string, string>, services: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    private function indexData(Request $request): array
    {
        $filters = [
            'query' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'service_interest' => trim((string) $request->query('service_interest', '')),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $model = new Inquiry(app_database());
        $items = $model->search($filters, $limit, $offset);
        $total = $model->count($filters);

        return [
            'items' => $items,
            'filters' => [
                'q' => $filters['query'],
                'status' => $filters['status'],
                'service_interest' => $filters['service_interest'],
            ],
            'services' => (new Service(app_database()))->allPublished(100),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
        ];
    }

    /**
     * @param array{items: array<int, array<string, mixed>>, filters: array<string, string>, services: array<int, array<string, mixed>>, pagination: array<string, int>} $data
     */
    private function renderResults(array $data): string
    {
        $view = new View(dirname(__DIR__, 3));

        return $view->partial('admin/inquiries/results', [
            'items' => $data['items'],
            'filters' => $data['filters'],
            'pagination' => $data['pagination'],
            'adminPath' => $this->adminPath(),
        ]);
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'responded' => 'inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700',
            'archived' => 'inline-flex rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700',
            'spam' => 'inline-flex rounded-full bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700',
            'in_progress' => 'inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700',
            default => 'inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700',
        };
    }
}
