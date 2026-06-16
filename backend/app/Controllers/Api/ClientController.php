<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Client;
use App\Services\ClientService;

class ClientController extends Controller
{
    private ClientService $service;

    public function __construct()
    {
        $this->service = new ClientService(new Client(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->success($this->service->listPublished());
    }
}
