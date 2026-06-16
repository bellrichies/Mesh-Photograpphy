<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers  = [];
    private string $body    = '';

    public function json(array $data, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json; charset=UTF-8';
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    public function html(string $content, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'text/html; charset=UTF-8';
        $this->body = $content;
        return $this;
    }

    public function csv(string $content, string $filename, int $status = 200): static
    {
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'export.csv';

        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'text/csv; charset=UTF-8';
        $this->headers['Content-Disposition'] = 'attachment; filename="' . $safeFilename . '"';
        $this->headers['X-Content-Type-Options'] = 'nosniff';
        $this->body = $content;

        return $this;
    }

    public function redirect(string $url, int $status = 302): static
    {
        $this->statusCode = $status;
        $this->headers['Location'] = $url;
        $this->body = '';
        return $this;
    }

    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
        echo $this->body;
    }

    public static function apiSuccess(array $data = [], string $message = 'Success', array $meta = [], int $status = 200): static
    {
        return (new static())->json([
            'ok'      => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => (object) [],
            'meta'    => $meta,
        ], $status);
    }

    public static function apiError(string $message, int $status = 400, array $errors = [], mixed $data = null): static
    {
        return (new static())->json([
            'ok'      => false,
            'message' => $message,
            'data'    => $data,
            'errors'  => empty($errors) ? (object) [] : $errors,
            'meta'    => ['status' => $status],
        ], $status);
    }
}
