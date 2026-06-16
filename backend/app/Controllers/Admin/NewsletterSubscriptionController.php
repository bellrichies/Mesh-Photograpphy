<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\NewsletterSubscription;
use App\Services\ActivityLogService;

class NewsletterSubscriptionController extends Controller
{
    private NewsletterSubscription $model;

    public function __construct()
    {
        $this->model = new NewsletterSubscription(app_database());
    }

    public function index(Request $request, Response $response): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));
        $filters = $this->filters($request);

        $items = array_map([$this, 'formatRow'], $this->model->findForAdmin($filters, $page, $perPage));
        $total = $this->model->countForAdmin($filters);

        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }

    public function export(Request $request, Response $response): Response
    {
        $filters = $this->filters($request);
        $rows = $this->model->exportRows($filters);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['email', 'status', 'source', 'subscribed_at']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['email'],
                $row['status'],
                $row['source'],
                $row['subscribed_at'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        (new ActivityLogService(app_database()))->recordForRequest(
            $request,
            'export',
            'newsletter_subscriptions',
            null,
            'Exported newsletter subscribers CSV.',
            ['filters' => $filters, 'row_count' => count($rows)]
        );

        return (new Response())->csv(
            "\xEF\xBB\xBF" . $csv,
            'newsletter-subscribers-' . date('Y-m-d') . '.csv'
        );
    }

    private function filters(Request $request): array
    {
        return array_filter([
            'status' => $request->query('status'),
            'q' => trim((string) $request->query('q', '')),
        ], fn(mixed $value): bool => $value !== null && $value !== '');
    }

    private function formatRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'email' => $row['email'],
            'status' => $row['status'],
            'source' => $row['source'],
            'ip_address' => $row['ip_address'],
            'user_agent' => $row['user_agent'],
            'subscribed_at' => $row['subscribed_at'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }
}
