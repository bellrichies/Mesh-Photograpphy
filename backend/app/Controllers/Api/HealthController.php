<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class HealthController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $dbStatus = 'connected';

        try {
            app_database()->query('SELECT 1');
        } catch (\Throwable) {
            $dbStatus = 'disconnected';
        }

        return $this->success([
            'status'    => $dbStatus === 'connected' ? 'healthy' : 'degraded',
            'db'        => $dbStatus,
            'version'   => '1.0.0',
            'timestamp' => date('c'),
        ]);
    }
}
