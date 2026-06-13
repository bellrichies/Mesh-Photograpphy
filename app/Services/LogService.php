<?php

declare(strict_types=1);

namespace App\Services;

class LogService
{
    public function __construct(private readonly string $basePath = '')
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string $channel, string $message, array $context = []): void
    {
        $this->write('ERROR', $channel, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $channel, string $message, array $context = []): void
    {
        $this->write('INFO', $channel, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function write(string $level, string $channel, string $message, array $context): void
    {
        $basePath = $this->basePath !== '' ? $this->basePath : dirname(__DIR__, 2);
        $logDir = $basePath . '/storage/logs';
        if (! is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s %s %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $channel,
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
        );

        @file_put_contents($logDir . '/' . preg_replace('/[^a-z0-9_-]+/i', '-', $channel) . '.log', $line, FILE_APPEND);
    }
}