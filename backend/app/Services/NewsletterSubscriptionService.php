<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NewsletterSubscription;
use PDOException;

class NewsletterSubscriptionService
{
    public function __construct(private readonly NewsletterSubscription $model) {}

    public function subscribe(string $email, string $ip, string $userAgent, string $source = 'footer'): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $existing = $this->model->findByNormalizedEmail($normalizedEmail);

        if ($existing && $existing['status'] === 'active') {
            return [
                'id' => (int) $existing['id'],
                'status' => 'already_subscribed',
            ];
        }

        $payload = [
            'email' => $normalizedEmail,
            'email_normalized' => $normalizedEmail,
            'source' => $this->limit($source, 50),
            'ip_address' => $ip,
            'user_agent' => $this->limit($userAgent, 512),
        ];

        if ($existing) {
            $this->model->reactivate((int) $existing['id'], $payload);

            return [
                'id' => (int) $existing['id'],
                'status' => 'reactivated',
            ];
        }

        return $this->createWithDuplicateRecovery($payload);
    }

    private function createWithDuplicateRecovery(array $payload): array
    {
        try {
            return [
                'id' => $this->model->create($payload),
                'status' => 'subscribed',
            ];
        } catch (PDOException $e) {
            if (!$this->isDuplicateKey($e)) {
                throw $e;
            }

            $existing = $this->model->findByNormalizedEmail($payload['email_normalized']);
            if (!$existing) {
                throw $e;
            }

            if (($existing['status'] ?? null) !== 'active') {
                $this->model->reactivate((int) $existing['id'], $payload);

                return [
                    'id' => (int) $existing['id'],
                    'status' => 'reactivated',
                ];
            }

            return [
                'id' => (int) $existing['id'],
                'status' => 'already_subscribed',
            ];
        }
    }

    private function isDuplicateKey(PDOException $e): bool
    {
        return ($e->errorInfo[0] ?? null) === '23000'
            || str_contains($e->getMessage(), 'Duplicate entry');
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function limit(string $value, int $maxLength): string
    {
        return substr($value, 0, $maxLength);
    }
}
