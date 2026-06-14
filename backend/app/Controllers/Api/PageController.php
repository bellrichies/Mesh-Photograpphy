<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Page;
use App\Services\PageService;

class PageController extends Controller
{
    private PageService $service;

    public function __construct()
    {
        $this->service = new PageService(new Page(app_database()));
    }

    public function show(Request $request, Response $response): Response
    {
        $slug = $request->param('slug');
        $page = $this->service->getBySlug($slug);

        if (!$page) {
            throw new HttpException(404, 'Page not found');
        }

        return $this->success($page);
    }
}
