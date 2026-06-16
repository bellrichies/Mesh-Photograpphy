<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class NewsletterSubscription
{
    public function __construct(private readonly Database $db) {}

    public function findForAdmin(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        [$where, $params] = $this->buildAdminWhere($filters);
        $offset = ($page - 1) * $perPage;

        return $this->db->query(
            'SELECT id, email, status, source, ip_address, user_agent, subscribed_at, created_at, updated_at
             FROM newsletter_subscriptions
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY subscribed_at DESC, id DESC
             LIMIT ? OFFSET ?',
            [...$params, $perPage, $offset]
        )->fetchAll();
    }

    public function countForAdmin(array $filters = []): int
    {
        [$where, $params] = $this->buildAdminWhere($filters);

        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt
             FROM newsletter_subscriptions
             WHERE ' . implode(' AND ', $where),
            $params
        )->fetch();

        return (int) ($row['cnt'] ?? 0);
    }

    public function stats(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'active') AS active,
                    SUM(status = 'unsubscribed') AS unsubscribed
             FROM newsletter_subscriptions"
        )->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'unsubscribed' => (int) ($row['unsubscribed'] ?? 0),
        ];
    }

    public function exportRows(array $filters = []): array
    {
        [$where, $params] = $this->buildAdminWhere($filters);

        return $this->db->query(
            'SELECT email, status, source, subscribed_at
             FROM newsletter_subscriptions
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY subscribed_at DESC, id DESC',
            $params
        )->fetchAll();
    }

    public function findByNormalizedEmail(string $email): ?array
    {
        $row = $this->db->query(
            'SELECT id, email, email_normalized, status FROM newsletter_subscriptions WHERE email_normalized = ? LIMIT 1',
            [$email]
        )->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $this->db->query(
            'INSERT INTO newsletter_subscriptions
                (email, email_normalized, status, source, ip_address, user_agent, subscribed_at, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())',
            [
                $data['email'],
                $data['email_normalized'],
                'active',
                $data['source'],
                $data['ip_address'] ?? null,
                $data['user_agent'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function reactivate(int $id, array $data): void
    {
        $this->db->query(
            'UPDATE newsletter_subscriptions
             SET email = ?,
                 email_normalized = ?,
                 status = ?,
                 source = ?,
                 ip_address = ?,
                 user_agent = ?,
                 subscribed_at = NOW(),
                 unsubscribed_at = NULL,
                 updated_at = NOW()
             WHERE id = ?',
            [
                $data['email'],
                $data['email_normalized'],
                'active',
                $data['source'],
                $data['ip_address'] ?? null,
                $data['user_agent'] ?? null,
                $id,
            ]
        );
    }

    private function buildAdminWhere(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];

        $status = $filters['status'] ?? null;
        if (in_array($status, ['active', 'unsubscribed'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = 'email LIKE ?';
            $params[] = '%' . $query . '%';
        }

        return [$where, $params];
    }
}
