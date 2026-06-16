<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\TeamMember;
use App\Services\TeamMemberService;

class TeamMemberController extends Controller
{
    private TeamMemberService $service;

    public function __construct()
    {
        $this->service = new TeamMemberService(new TeamMember(app_database()));
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->success($this->service->listPublished());
    }
}
