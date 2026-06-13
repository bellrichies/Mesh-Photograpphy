<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class BookingRequest
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT br.*, s.title AS service_title, s.slug AS service_slug,
                    i.id AS inquiry_record_id, i.email AS inquiry_email,
                    CONCAT_WS(" ", i.first_name, i.last_name) AS inquiry_contact_name
             FROM booking_requests br
             LEFT JOIN services s ON s.id = br.service_id AND s.deleted_at IS NULL
             LEFT JOIN inquiries i ON i.id = br.inquiry_id
             WHERE br.id = :id
             LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters, int $limit = 20, int $offset = 0): array
    {
        [$whereClause, $params] = $this->buildWhere($filters);

        $rows = $this->database->query(
            sprintf(
                'SELECT br.*, s.title AS service_title, s.slug AS service_slug,
                        i.id AS inquiry_record_id, i.email AS inquiry_email
                 FROM booking_requests br
                 LEFT JOIN services s ON s.id = br.service_id AND s.deleted_at IS NULL
                 LEFT JOIN inquiries i ON i.id = br.inquiry_id
                 WHERE %s
                 ORDER BY br.requested_date DESC, br.created_at DESC, br.id DESC
                 LIMIT %d OFFSET %d',
                $whereClause,
                max(1, $limit),
                max(0, $offset)
            ),
            $params
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function count(array $filters): int
    {
        [$whereClause, $params] = $this->buildWhere($filters);

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM booking_requests br WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO booking_requests (
                inquiry_id, service_id, first_name, last_name, email, phone,
                requested_date, requested_time, event_type, location, hours_needed,
                guest_count, notes, status, source_ip, user_agent, created_at, updated_at
            ) VALUES (
                :inquiry_id, :service_id, :first_name, :last_name, :email, :phone,
                :requested_date, :requested_time, :event_type, :location, :hours_needed,
                :guest_count, :notes, :status, :source_ip, :user_agent, NOW(), NOW()
            )',
            [
                'inquiry_id' => $data['inquiry_id'] ?? null,
                'service_id' => $data['service_id'] ?? null,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? null,
                'requested_date' => $data['requested_date'] ?? null,
                'requested_time' => $data['requested_time'] ?? null,
                'event_type' => $data['event_type'] ?? '',
                'location' => $data['location'] ?? '',
                'hours_needed' => $data['hours_needed'] ?? null,
                'guest_count' => $data['guest_count'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'new',
                'source_ip' => $data['source_ip'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->database->query(
            'UPDATE booking_requests SET status = :status, updated_at = NOW() WHERE id = :id',
            ['id' => $id, 'status' => $status]
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $whereParts = ['1 = 1'];
        $params = [];

        if (isset($filters['query']) && is_string($filters['query']) && trim($filters['query']) !== '') {
            $whereParts[] = '(
                br.first_name LIKE :query
                OR br.last_name LIKE :query
                OR br.email LIKE :query
                OR br.phone LIKE :query
                OR br.event_type LIKE :query
                OR br.location LIKE :query
                OR br.notes LIKE :query
            )';
            $params['query'] = '%' . trim($filters['query']) . '%';
        }

        if (isset($filters['status']) && is_string($filters['status']) && $filters['status'] !== '') {
            $whereParts[] = 'br.status = :status';
            $params['status'] = $filters['status'];
        }

        if (isset($filters['service_id']) && is_numeric($filters['service_id']) && (int) $filters['service_id'] > 0) {
            $whereParts[] = 'br.service_id = :service_id';
            $params['service_id'] = (int) $filters['service_id'];
        }

        return [implode(' AND ', $whereParts), $params];
    }
}