<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;
use App\Services\ServiceService;

class ServiceController extends Controller
{
    private ServiceService $service;

    public function __construct()
    {
        $this->service = new ServiceService(new Service(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->success($this->service->listPublished());
    }

    public function show(Request $request, Response $response): Response
    {
        $slug   = $request->param('slug');
        $detail = $this->service->getBySlug($slug);

        if (!$detail) {
            throw new HttpException(404, 'Service not found');
        }

        return $this->success($detail);
    }
}
