<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\ActivityLogService;

class AdminActivityLogMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $logger = new ActivityLogService(app_database());
            [$resource, $resourceId] = $this->resolveResource($request, $response);
            $action = $this->resolveAction($request, $resource);

            $logger->recordForRequest(
                $request,
                $action,
                $resource,
                $resourceId,
                $this->describe($action, $resource, $resourceId),
                [
                    'route_params' => $request->routeParams(),
                    'payload'      => $logger->sanitize($this->requestPayload($request)),
                    'status_code'  => $response->getStatusCode(),
                ]
            );
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (!str_starts_with($request->path(), '/api/v1/admin/')) {
            return false;
        }

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return false;
        }

        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }

    private function resolveAction(Request $request, ?string $resource): string
    {
        $path = $request->path();

        if (str_ends_with($path, '/upload')) {
            return 'upload';
        }

        if (str_ends_with($path, '/archive')) {
            return 'archive';
        }

        if (str_ends_with($path, '/autosave')) {
            return 'autosave';
        }

        if (str_ends_with($path, '/restore')) {
            return 'restore';
        }

        if (str_ends_with($path, '/reorder')) {
            return 'reorder';
        }

        return match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => $resource === 'settings' ? 'settings_update' : 'update',
            'DELETE' => 'delete',
            default => strtolower($request->method()),
        };
    }

    private function resolveResource(Request $request, Response $response): array
    {
        $segments = array_values(array_filter(explode('/', trim($request->path(), '/'))));
        $segments = array_slice($segments, 3); // api/v1/admin
        $resourceParts = [];

        foreach ($segments as $segment) {
            if (ctype_digit($segment)) {
                break;
            }

            if (in_array($segment, ['upload', 'archive', 'autosave', 'restore', 'reorder'], true)) {
                break;
            }

            $resourceParts[] = str_replace('-', '_', $segment);

            if (count($resourceParts) === 2) {
                break;
            }
        }

        if (($resourceParts[0] ?? null) === 'galleries' && isset($segments[2]) && $segments[2] === 'media') {
            $resourceParts = ['gallery', 'media'];
        }

        if (($resourceParts[0] ?? null) === 'inquiries' && isset($segments[2]) && $segments[2] === 'notes') {
            $resourceParts = ['inquiry', 'notes'];
        }

        $resource = $resourceParts ? implode('_', $resourceParts) : 'admin';
        $id = $this->resourceIdFromParams($request, $resource) ?? $this->resourceIdFromResponse($response);

        return [$resource, $id];
    }

    private function resourceIdFromParams(Request $request, string $resource): ?int
    {
        $params = $request->routeParams();

        if ($resource === 'gallery_media' && isset($params['mediaId'])) {
            return (int) $params['mediaId'];
        }

        if ($resource === 'blog_posts' && isset($params['id'])) {
            return (int) $params['id'];
        }

        foreach (['id', 'userId', 'postId', 'revisionId'] as $key) {
            if (isset($params[$key]) && is_numeric($params[$key])) {
                return (int) $params[$key];
            }
        }

        return null;
    }

    private function resourceIdFromResponse(Response $response): ?int
    {
        $body = json_decode($response->getBody(), true);
        if (!is_array($body)) {
            return null;
        }

        $data = $body['data'] ?? null;
        if (is_array($data) && isset($data['id']) && is_numeric($data['id'])) {
            return (int) $data['id'];
        }

        return null;
    }

    private function describe(string $action, ?string $resource, ?int $resourceId): string
    {
        if ($action === 'settings_update') {
            return 'Updated settings.';
        }

        $label = str_replace('_', ' ', $resource ?? 'admin resource');
        $description = ucfirst(str_replace('_', ' ', $action)) . ' ' . $label;

        if ($resourceId) {
            $description .= " #{$resourceId}";
        }

        return $description . '.';
    }

    private function requestPayload(Request $request): array
    {
        $contentType = (string) $request->server('CONTENT_TYPE', '');

        if (str_contains($contentType, 'application/json')) {
            return $request->json();
        }

        return array_merge($_GET, $_POST);
    }
}
