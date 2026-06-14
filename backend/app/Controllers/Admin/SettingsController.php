<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class SettingsController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT `group`, `key`, `value`, `type` FROM settings ORDER BY `group`, `key`'
        )->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group']][$row['key']] = $row['value'];
        }

        return $this->success($grouped);
    }

    public function update(Request $request, Response $response): Response
    {
        $db   = app_database();
        $data = $request->json();

        foreach ($data as $group => $keys) {
            if (!is_array($keys)) continue;
            foreach ($keys as $key => $value) {
                $db->query(
                    'UPDATE settings SET `value`=?, updated_at=NOW() WHERE `group`=? AND `key`=?',
                    [(string)$value, (string)$group, (string)$key]
                );
            }
        }

        app_settings_refresh_cache();

        return $this->success(null, 'Settings updated.');
    }
}
