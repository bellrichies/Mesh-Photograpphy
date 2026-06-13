<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Concerns;

use App\Core\Request;
use App\Core\Response;

trait InteractsWithAdminJson
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     */
    protected function jsonSuccess(Response $response, string $message, array $data = [], array $meta = [], int $statusCode = 200): Response
    {
        return $response->json([
            'ok' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
            'meta' => $meta,
        ], $statusCode);
    }

    /**
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $meta
     */
    protected function jsonError(Response $response, string $message, array $errors = [], array $meta = [], int $statusCode = 422): Response
    {
        return $response->json([
            'ok' => false,
            'message' => $message,
            'data' => [],
            'errors' => $errors,
            'meta' => $meta,
        ], $statusCode);
    }

    protected function isAjaxRequest(Request $request): bool
    {
        $requestedWith = strtolower((string) $request->server('HTTP_X_REQUESTED_WITH', ''));
        $accept = strtolower((string) $request->server('HTTP_ACCEPT', ''));

        return $requestedWith === 'xmlhttprequest'
            || str_contains($accept, 'application/json')
            || $request->input('_ajax', '0') === '1';
    }
}