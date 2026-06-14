<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Inquiry
{
    public function __construct(private readonly Database $db) {}

    public function create(array $data): int
    {
        $this->db->query(
            'INSERT INTO inquiries (name, email, phone, subject, message, service_id, ip_address, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['name'],
                $data['email'],
                $data['phone']   ?? null,
                $data['subject'] ?? null,
                $data['message'],
                $data['service_id'] ?? null,
                $data['ip_address'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }
}
