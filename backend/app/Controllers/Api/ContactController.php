<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Inquiry;
use App\Services\InquiryService;

class ContactController extends Controller
{
    private InquiryService $service;

    public function __construct()
    {
        $this->service = new InquiryService(new Inquiry(app_database()));
    }

    public function store(Request $request, Response $response): Response
    {
        $data      = $request->json();
        $validator = new Validator($data, [
            'name'    => 'required|string|max:150',
            'email'   => 'required|email',
            'message' => 'required|string|min:10',
            'phone'   => 'string|max:30',
            'subject' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $this->service->create($data, $request->ip());

        return $this->success(null, 'Your message has been received. We\'ll be in touch soon!', 201);
    }
}
