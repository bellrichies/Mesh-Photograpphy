<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class BookingController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $db      = app_database();
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $offset  = ($page - 1) * $perPage;

        $where  = ['deleted_at IS NULL'];
        $params = [];

        if ($status = $request->query('status')) {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);
        $total    = (int) $db->query("SELECT COUNT(*) AS cnt FROM booking_requests WHERE {$whereStr}", $params)->fetch()['cnt'];
        $rows     = $db->query(
            "SELECT * FROM booking_requests WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        )->fetchAll();

        $items = array_map([$this, 'formatRow'], $rows);
        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }

    public function show(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $row = $db->query('SELECT * FROM booking_requests WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Booking not found.');
        return $this->success($this->formatRow($row));
    }

    public function updateStatus(Request $request, Response $response): Response
    {
        $db     = app_database();
        $id     = (int) $request->param('id');
        $row    = $db->query('SELECT id FROM booking_requests WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Booking not found.');

        $status  = $request->json()['status'] ?? '';
        $allowed = ['new', 'contacted', 'quoted', 'booked', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return $this->validationError(['status' => ['Invalid status value.']]);
        }

        $db->query('UPDATE booking_requests SET status=?, updated_at=NOW() WHERE id=?', [$status, $id]);
        return $this->success(['id' => $id, 'status' => $status]);
    }

    private function formatRow(array $row): array
    {
        return [
            'id'             => (int)$row['id'],
            'name'           => $row['name'],
            'email'          => $row['email'],
            'phone'          => $row['phone']          ?? null,
            'event_type'     => $row['event_type'],
            'event_date'     => $row['event_date']     ?? null,
            'event_location' => $row['event_location'] ?? null,
            'message'        => $row['message'],
            'status'         => $row['status'],
            'ip_address'     => $row['ip_address']     ?? null,
            'created_at'     => $row['created_at'],
        ];
    }
}
