<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;

class InquiryController extends Controller
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
        $total    = (int) $db->query("SELECT COUNT(*) AS cnt FROM inquiries WHERE {$whereStr}", $params)->fetch()['cnt'];
        $rows     = $db->query(
            "SELECT * FROM inquiries WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        )->fetchAll();

        $items = array_map([$this, 'formatRow'], $rows);
        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }

    public function show(Request $request, Response $response): Response
    {
        $db  = app_database();
        $id  = (int) $request->param('id');
        $row = $db->query('SELECT * FROM inquiries WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Inquiry not found.');

        $notes = $db->query(
            'SELECT n.*, u.first_name, u.last_name FROM inquiry_notes n
             LEFT JOIN users u ON n.created_by = u.id
             WHERE n.inquiry_id=? ORDER BY n.created_at ASC',
            [$id]
        )->fetchAll();

        $data = $this->formatRow($row);
        $data['notes'] = array_map(fn($n) => [
            'id'         => (int)$n['id'],
            'note'       => $n['note'],
            'created_by' => ['id' => (int)$n['created_by'], 'name' => trim($n['first_name'] . ' ' . $n['last_name'])],
            'created_at' => $n['created_at'],
        ], $notes);

        return $this->success($data);
    }

    public function updateStatus(Request $request, Response $response): Response
    {
        $db     = app_database();
        $id     = (int) $request->param('id');
        $row    = $db->query('SELECT * FROM inquiries WHERE id=? AND deleted_at IS NULL LIMIT 1', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Inquiry not found.');

        $status  = $request->json()['status'] ?? '';
        $allowed = ['new', 'in_progress', 'replied', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return $this->validationError(['status' => ['Invalid status value.']]);
        }

        $db->query('UPDATE inquiries SET status=?, updated_at=NOW() WHERE id=?', [$status, $id]);
        return $this->success(['id' => $id, 'status' => $status]);
    }

    public function addNote(Request $request, Response $response): Response
    {
        $db   = app_database();
        $id   = (int) $request->param('id');
        $row  = $db->query('SELECT id FROM inquiries WHERE id=? AND deleted_at IS NULL', [$id])->fetch();
        if (!$row) throw new HttpException(404, 'Inquiry not found.');

        $note    = trim($request->json()['note'] ?? '');
        if (!$note) return $this->validationError(['note' => ['Note is required.']]);

        $payload = $request->authPayload();
        $userId  = $payload['user_id'] ?? $payload['sub'] ?? null;

        $db->query(
            'INSERT INTO inquiry_notes (inquiry_id, note, created_by, created_at) VALUES (?,?,?,NOW())',
            [$id, $note, $userId]
        );

        return $this->created(['note' => $note]);
    }

    public function export(Request $request, Response $response): Response
    {
        $db     = app_database();
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if ($status = $request->query('status')) {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);
        $rows     = $db->query(
            "SELECT id, name, email, phone, subject, message, status, ip_address, created_at
             FROM inquiries WHERE {$whereStr} ORDER BY created_at DESC",
            $params
        )->fetchAll();

        $columns = ['ID', 'Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'IP', 'Date'];

        ob_start();
        $out = fopen('php://output', 'w');
        fputcsv($out, $columns);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['name'],
                $r['email'],
                $r['phone']      ?? '',
                $r['subject']    ?? '',
                $r['message'],
                $r['status'],
                $r['ip_address'] ?? '',
                $r['created_at'],
            ]);
        }
        fclose($out);
        $csv = ob_get_clean();

        $filename = 'inquiries-' . date('Y-m-d') . '.csv';

        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=UTF-8');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header('Cache-Control: no-cache, must-revalidate');
        }

        echo $csv;
        exit;
    }

    private function formatRow(array $row): array
    {
        return [
            'id'         => (int)$row['id'],
            'name'       => $row['name'],
            'email'      => $row['email'],
            'phone'      => $row['phone']   ?? null,
            'subject'    => $row['subject'] ?? null,
            'message'    => $row['message'],
            'status'     => $row['status'],
            'ip_address' => $row['ip_address'] ?? null,
            'created_at' => $row['created_at'],
            'notes'      => [],
        ];
    }
}
