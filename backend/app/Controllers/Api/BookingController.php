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
        $this->notifyAdmin($data);

        return $this->success(null, 'Booking request received! We\'ll contact you within 24 hours.', 201);
    }

    private function notifyAdmin(array $data): void
    {
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? $_ENV['MAIL_FROM'] ?? '';
        if (!$adminEmail) return;

        $siteName  = $_ENV['APP_NAME'] ?? 'Mesh Photography';
        $from      = $data['name'] ?? 'Unknown';
        $email     = $data['email'] ?? '';
        $eventType = $data['event_type'] ?? '—';
        $eventDate = $data['event_date'] ?? '—';
        $location  = $data['location'] ?? '—';
        $notes     = $data['notes'] ?? '';

        $body = "New booking request via {$siteName}:\n\n"
            . "From:       {$from} <{$email}>\n"
            . "Event Type: {$eventType}\n"
            . "Date:       {$eventDate}\n"
            . "Location:   {$location}\n\n"
            . "Notes:\n{$notes}\n";

        @mail(
            $adminEmail,
            "[{$siteName}] New booking request from {$from}",
            $body,
            "From: {$siteName} <{$adminEmail}>\r\nContent-Type: text/plain; charset=UTF-8"
        );
    }
}
