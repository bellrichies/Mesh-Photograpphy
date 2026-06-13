<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;

    /** @var array<string, string> */
    private array $headers = [];

    private string $body = '';

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function html(string $content, int $statusCode = 200): self
    {
        $this->statusCode = $statusCode;
        $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->body = $content;
        return $this;
    }

    public function raw(string $content, string $contentType = 'text/plain; charset=utf-8', int $statusCode = 200): self
    {
        $this->statusCode = $statusCode;
        $this->headers['Content-Type'] = $contentType;
        $this->body = $content;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function json(array $data, int $statusCode = 200): self
    {
        $this->statusCode = $statusCode;
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->body = (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->statusCode = $statusCode;
        $this->headers['Location'] = $this->normalizeRedirectUrl($url);
        return $this;
    }

    private function normalizeRedirectUrl(string $url): string
    {
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $url) === 1) {
            return $url;
        }

        if (function_exists('redirect_url')) {
            $basePath = function_exists('app_base_path') ? app_base_path() : '';
            if ($basePath !== '' && (str_starts_with($url, $basePath . '/') || $url === $basePath)) {
                return $url;
            }

            return redirect_url(ltrim($url, '/'));
        }

        return $url;
    }

    public function send(): void
    {
        if (! headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $this->body;
    }
}
