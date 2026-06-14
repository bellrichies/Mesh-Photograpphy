<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Testimonial;
use App\Services\TestimonialService;

class TestimonialController extends Controller
{
    private TestimonialService $service;

    public function __construct()
    {
        $this->service = new TestimonialService(new Testimonial(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->success($this->service->listPublished());
    }
}
