<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $db      = app_database();
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));
        $offset  = ($page - 1) * $perPage;

        $where  = [];
        $params = [];

        if ($action = $request->query('action')) {
            $where[]  = 'al.action = ?';
            $params[] = $action;
        }

        if ($modelType = $request->query('model_type')) {
            $where[]  = 'al.model_type = ?';
            $params[] = $modelType;
        }

        if ($userId = $request->query('user_id')) {
            $where[]  = 'al.user_id = ?';
            $params[] = (int) $userId;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM audit_logs al {$whereStr}",
            $params
        )->fetch()['cnt'];

        $rows = $db->query(
            "SELECT al.*, u.first_name, u.last_name, u.email AS user_email
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             {$whereStr}
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        )->fetchAll();

        $items = array_map(fn(array $r) => [
            'id'         => (int) $r['id'],
            'action'     => $r['action'],
            'model_type' => $r['model_type'],
            'model_id'   => $r['model_id'] ? (int) $r['model_id'] : null,
            'user'       => ($r['first_name'] ?? null) ? [
                'id'    => (int) $r['user_id'],
                'name'  => trim($r['first_name'] . ' ' . $r['last_name']),
                'email' => $r['user_email'],
            ] : null,
            'ip_address' => $r['ip_address'],
            'created_at' => $r['created_at'],
        ], $rows);

        return $this->success($items, 'Success', 200, $this->paginate($items, $total, $page, $perPage));
    }
}
