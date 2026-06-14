<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use Throwable;

class ErrorHandler
{
    public function __construct(
        private readonly string $basePath,
        private readonly ?Request $request = null
    ) {}

    public function handle(Throwable $e): Response
    {
        $isApi = $this->request?->isApiRequest() ?? true;

        if ($e instanceof HttpException) {
            $status  = $e->getStatusCode();
            $message = $e->getMessage() ?: $this->defaultMessage($status);
            $errors  = $e->getErrors();

            if ($isApi) {
                return (new Response())->json([
                    'ok'      => false,
                    'message' => $message,
                    'data'    => null,
                    'errors'  => empty($errors) ? (object) [] : $errors,
                    'meta'    => ['status' => $status],
                ], $status);
            }

            return (new Response())->html("<h1>{$status} {$message}</h1>", $status);
        }

        $this->logError($e);

        $debug   = (bool) env('APP_DEBUG', false);
        $message = $debug ? $e->getMessage() : 'Something went wrong on our side.';

        if ($isApi) {
            return (new Response())->json([
                'ok'      => false,
                'message' => $message,
                'data'    => null,
                'errors'  => (object) [],
                'meta'    => ['status' => 500],
            ], 500);
        }

        return (new Response())->html("<h1>500 Server Error</h1><p>{$message}</p>", 500);
    }

    private function defaultMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            default => 'Error',
        };
    }

    private function logError(Throwable $e): void
    {
        $logDir  = $this->basePath . '/storage/logs';
        $logFile = $logDir . '/app-' . date('Y-m-d') . '.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $entry = json_encode([
            'timestamp' => date('c'),
            'level'     => 'error',
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'method'    => $this->request?->method(),
            'path'      => $this->request?->path(),
            'ip'        => $this->request?->ip(),
        ], JSON_UNESCAPED_SLASHES) . PHP_EOL;

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
