<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Request;
use Throwable;

class ActivityLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'access_token',
        'refresh_token',
        'jwt',
        'secret',
    ];

    public function __construct(private readonly Database $db) {}

    public function recordForRequest(
        Request $request,
        string $action,
        ?string $resource,
        ?int $resourceId,
        string $description,
        array $metadata = []
    ): void {
        $payload = $request->authPayload() ?? [];
        $userId  = $payload['sub'] ?? $payload['user_id'] ?? null;

        $this->record([
            'user_id'        => $userId ? (int) $userId : null,
            'action'         => $action,
            'model_type'     => $resource,
            'model_id'       => $resourceId,
            'description'    => $description,
            'new_values'     => $metadata,
            'ip_address'     => $request->ip(),
            'user_agent'     => $this->limit($request->userAgent(), 500),
            'request_method' => $request->method(),
            'request_path'   => $this->limit($request->path(), 255),
        ]);
    }

    public function recordAuthEvent(
        Request $request,
        string $action,
        ?array $user,
        string $description,
        array $metadata = []
    ): void {
        $this->record([
            'user_id'        => isset($user['id']) ? (int) $user['id'] : null,
            'action'         => $action,
            'model_type'     => 'auth',
            'model_id'       => isset($user['id']) ? (int) $user['id'] : null,
            'description'    => $description,
            'new_values'     => $metadata,
            'ip_address'     => $request->ip(),
            'user_agent'     => $this->limit($request->userAgent(), 500),
            'request_method' => $request->method(),
            'request_path'   => $this->limit($request->path(), 255),
        ]);
    }

    public function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if ($this->isSensitiveKey($normalizedKey)) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            $sanitized[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $sanitized;
    }

    private function record(array $entry): void
    {
        try {
            $this->db->query(
                'INSERT INTO audit_logs
                    (user_id, action, model_type, model_id, description, old_values, new_values,
                     ip_address, user_agent, request_method, request_path, created_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $entry['user_id'] ?? null,
                    $this->limit((string) ($entry['action'] ?? 'activity'), 100),
                    $entry['model_type'] ?? null,
                    $entry['model_id'] ?? null,
                    $entry['description'] ?? null,
                    isset($entry['old_values']) ? $this->encodeJson($entry['old_values']) : null,
                    isset($entry['new_values']) ? $this->encodeJson($entry['new_values']) : null,
                    $entry['ip_address'] ?? null,
                    $entry['user_agent'] ?? null,
                    $entry['request_method'] ?? null,
                    $entry['request_path'] ?? null,
                ]
            );
        } catch (Throwable $e) {
            error_log('Activity log write failed: ' . $e->getMessage());
        }
    }

    private function isSensitiveKey(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if ($key === $sensitiveKey || str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    private function encodeJson(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function limit(string $value, int $maxLength): string
    {
        return substr($value, 0, $maxLength);
    }
}
