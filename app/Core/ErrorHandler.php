<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use ErrorException;
use Throwable;

class ErrorHandler
{
    private bool $isHandling = false;

    private readonly bool $debug;

    public function __construct(
        private readonly string $basePath,
        private readonly Request $request
    ) {
        $this->debug = (bool) config('app.debug', false);
    }

    public function registerShutdownHandler(): void
    {
        register_shutdown_function(function (): void {
            $error = error_get_last();
            if (! is_array($error) || ! $this->isFatalError($error['type'] ?? 0)) {
                return;
            }

            if ($this->isHandling) {
                return;
            }

            $exception = new ErrorException(
                (string) ($error['message'] ?? 'Fatal application error.'),
                0,
                (int) ($error['type'] ?? E_ERROR),
                (string) ($error['file'] ?? $this->basePath . '/unknown'),
                (int) ($error['line'] ?? 0)
            );

            $response = $this->handle($exception);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $response->send();
        });
    }

    public function handle(Throwable $exception): Response
    {
        if ($this->isHandling) {
            return (new Response())->html('An unexpected error occurred.', 500);
        }

        $this->isHandling = true;

        try {
            $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
            $requestId = $this->resolveRequestId();

            $this->logException($exception, $statusCode, $requestId);

            if ($this->request->expectsJson()) {
                return $this->jsonResponse($exception, $statusCode, $requestId);
            }

            return $this->htmlResponse($exception, $statusCode, $requestId);
        } finally {
            $this->isHandling = false;
        }
    }

    private function isFatalError(mixed $type): bool
    {
        return in_array((int) $type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true);
    }

    private function jsonResponse(Throwable $exception, int $statusCode, string $requestId): Response
    {
        $message = $this->safeMessage($exception, $statusCode);
        $meta = [
            'status' => $statusCode,
            'request_id' => $requestId,
        ];

        if ($this->debug) {
            $meta['exception'] = get_class($exception);
            $meta['file'] = $exception->getFile();
            $meta['line'] = $exception->getLine();
        }

        return (new Response())
            ->setHeader('X-Request-Id', $requestId)
            ->json([
                'ok' => false,
                'message' => $message,
                'data' => [],
                'errors' => [],
                'meta' => $meta,
            ], $statusCode);
    }

    private function htmlResponse(Throwable $exception, int $statusCode, string $requestId): Response
    {
        $viewPath = $this->resolveErrorView($statusCode);
        $payload = [
            'statusCode' => $statusCode,
            'exception' => $exception,
            'requestId' => $requestId,
            'debug' => $this->debug,
            'safeMessage' => $this->safeMessage($exception, $statusCode),
            'isAdmin' => $this->request->isAdminRequest(),
            'requestPath' => $this->request->path(),
        ];

        $content = $this->renderFile($viewPath, $payload);

        return (new Response())
            ->setHeader('X-Request-Id', $requestId)
            ->html($content, $statusCode);
    }

    private function resolveErrorView(int $statusCode): string
    {
        if ($statusCode === 404) {
            return $this->basePath . '/resources/views/errors/404.php';
        }

        if ($statusCode >= 500 && $this->request->isAdminRequest()) {
            return $this->basePath . '/resources/views/errors/500-admin.php';
        }

        if ($statusCode >= 500) {
            return $this->basePath . '/resources/views/errors/500-public.php';
        }

        $candidate = $this->basePath . '/resources/views/errors/' . $statusCode . '.php';
        if (is_file($candidate)) {
            return $candidate;
        }

        return $this->basePath . '/resources/views/errors/500-public.php';
    }

    private function safeMessage(Throwable $exception, int $statusCode): string
    {
        if ($statusCode === 404) {
            return $exception->getMessage() !== '' ? $exception->getMessage() : 'The requested page could not be found.';
        }

        if ($statusCode >= 500) {
            if ($this->debug && $exception->getMessage() !== '') {
                return $exception->getMessage();
            }

            return 'Something went wrong on our side. Please try again shortly.';
        }

        return $exception->getMessage() !== '' ? $exception->getMessage() : 'The request could not be completed.';
    }

    private function resolveRequestId(): string
    {
        $headerValue = trim((string) $this->request->server('HTTP_X_REQUEST_ID', ''));
        if ($headerValue !== '') {
            return substr($headerValue, 0, 64);
        }

        return bin2hex(random_bytes(8));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderFile(string $path, array $payload): string
    {
        extract($payload, EXTR_SKIP);

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    private function logException(Throwable $exception, int $statusCode, string $requestId): void
    {
        $directory = $this->basePath . '/storage/logs';
        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $record = [
            'timestamp' => date('c'),
            'request_id' => $requestId,
            'status' => $statusCode,
            'method' => $this->request->method(),
            'path' => $this->request->path(),
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        @file_put_contents(
            $directory . '/app-' . date('Y-m-d') . '.log',
            json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
}