<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\BookingRequest;
use App\Models\Service;
use RuntimeException;

class BookingController
{
    public function index(Request $request, Response $response): Response
    {
        $filters = [
            'query' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'service_id' => (int) $request->query('service_id', 0),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $model = new BookingRequest(app_database());
        $items = $model->search($filters, $limit, $offset);
        $total = $model->count($filters);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/bookings/index', [
            'title' => 'Booking Requests',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Bookings', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => [
                'q' => $filters['query'],
                'status' => $filters['status'],
                'service_id' => $filters['service_id'] > 0 ? (string) $filters['service_id'] : '',
            ],
            'services' => (new Service(app_database()))->allPublished(100),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
        ], 'layouts/admin'));
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $bookingRequestId = (int) ($params['id'] ?? 0);
        $bookingRequest = (new BookingRequest(app_database()))->findById($bookingRequestId);
        if (! is_array($bookingRequest)) {
            throw new HttpException(404, 'Booking request not found.');
        }

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/bookings/show', [
            'title' => 'Booking Request Details',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Bookings', 'href' => $this->adminPath() . '/bookings'],
                ['label' => 'Details', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'bookingRequest' => $bookingRequest,
        ], 'layouts/admin'));
    }

    public function updateStatus(Request $request, Response $response, array $params): Response
    {
        $bookingRequestId = (int) ($params['id'] ?? 0);
        $bookingRequest = (new BookingRequest(app_database()))->findById($bookingRequestId);
        if (! is_array($bookingRequest)) {
            throw new HttpException(404, 'Booking request not found.');
        }

        try {
            $this->verifyCsrf($request);
            $status = trim((string) $request->post('status', ''));
            if (! in_array($status, ['new', 'in_progress', 'quoted', 'confirmed', 'archived'], true)) {
                throw new RuntimeException('Please select a valid booking status.');
            }

            (new BookingRequest(app_database()))->updateStatus($bookingRequestId, $status);
            app_session()->flash('success', 'Booking request status updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/bookings/view/' . $bookingRequestId, 302);
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
}
