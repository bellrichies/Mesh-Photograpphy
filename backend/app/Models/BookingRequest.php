<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BookingRequest
{
    public function __construct(private readonly Database $db) {}

    public function create(array $data): int
    {
        $this->db->query(
            'INSERT INTO booking_requests
             (name, email, phone, service_id, event_date, event_type, location, guest_count, budget, notes, ip_address, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['name'],
                $data['email'],
                $data['phone']        ?? null,
                $data['service_id']   ?? null,
                $data['event_date']   ?? null,
                $data['event_type']   ?? null,
                $data['location']     ?? null,
                $data['guest_count']  ?? null,
                $data['budget']       ?? null,
                $data['notes']        ?? null,
                $data['ip_address']   ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }
}
