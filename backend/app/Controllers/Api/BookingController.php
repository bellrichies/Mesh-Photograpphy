<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\BookingRequest;
use App\Services\BookingService;

class BookingController extends Controller
{
    private BookingService $service;

    public function __construct()
    {
        $this->service = new BookingService(new BookingRequest(app_database()));
    }

    public function store(Request $request, Response $response): Response
    {
        $data      = $request->json();
        $validator = new Validator($data, [
            'name'       => 'required|string|max:150',
            'email'      => 'required|email',
            'event_type' => 'required|string|max:150',
            'phone'      => 'string|max:30',
            'event_date' => 'string',
            'location'   => 'string|max:255',
            'notes'      => 'string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $this->service->create($data, $request->ip());

        return $this->success(null, 'Booking request received! We\'ll contact you within 24 hours.', 201);
    }
}
