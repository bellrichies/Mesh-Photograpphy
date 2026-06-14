<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;

class HeroSlideController extends Controller
{
    private HeroSlideService $service;

    public function __construct()
    {
        $this->service = new HeroSlideService(new HeroSlide(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->success($this->service->listPublished());
    }
}
