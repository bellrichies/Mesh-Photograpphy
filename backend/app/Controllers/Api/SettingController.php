<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Setting;
use App\Services\SettingService;

class SettingController extends Controller
{
    private SettingService $service;

    public function __construct()
    {
        $this->service = new SettingService(new Setting(app_database()));
    }

    public function public(Request $request, Response $response): Response
    {
        return $this->success($this->service->getPublic());
    }
}
