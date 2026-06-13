<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Inquiry
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            'SELECT i.*,
                    (SELECT COUNT(*) FROM inquiry_notes notes WHERE notes.inquiry_id = i.id) AS note_count
             FROM inquiries i
             WHERE i.id = :id
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
                'SELECT i.*, (SELECT COUNT(*) FROM inquiry_notes notes WHERE notes.inquiry_id = i.id) AS note_count
                 FROM inquiries i
                 WHERE %s
                 ORDER BY i.created_at DESC, i.id DESC
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
            'SELECT COUNT(*) FROM inquiries i WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO inquiries (
                first_name, last_name, email, phone, company_name, service_interest,
                preferred_date, budget_range, location, referral_source, message,
                status, source_ip, user_agent, created_at, updated_at
            ) VALUES (
                :first_name, :last_name, :email, :phone, :company_name, :service_interest,
                :preferred_date, :budget_range, :location, :referral_source, :message,
                :status, :source_ip, :user_agent, NOW(), NOW()
            )',
            [
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'service_interest' => $data['service_interest'] ?? null,
                'preferred_date' => $data['preferred_date'] ?? null,
                'budget_range' => $data['budget_range'] ?? null,
                'location' => $data['location'] ?? null,
                'referral_source' => $data['referral_source'] ?? null,
                'message' => $data['message'] ?? '',
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
            'UPDATE inquiries SET status = :status, updated_at = NOW() WHERE id = :id',
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
                i.first_name LIKE :query
                OR i.last_name LIKE :query
                OR i.email LIKE :query
                OR i.phone LIKE :query
                OR i.company_name LIKE :query
                OR i.service_interest LIKE :query
                OR i.location LIKE :query
                OR i.referral_source LIKE :query
                OR i.message LIKE :query
            )';
            $params['query'] = '%' . trim($filters['query']) . '%';
        }

        if (isset($filters['status']) && is_string($filters['status']) && $filters['status'] !== '') {
            $whereParts[] = 'i.status = :status';
            $params['status'] = $filters['status'];
        }

        if (isset($filters['service_interest']) && is_string($filters['service_interest']) && trim($filters['service_interest']) !== '') {
            $whereParts[] = 'i.service_interest = :service_interest';
            $params['service_interest'] = trim($filters['service_interest']);
        }

        return [implode(' AND ', $whereParts), $params];
    }
}