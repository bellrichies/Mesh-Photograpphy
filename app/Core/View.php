<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = [], ?string $layout = null): string
    {
        $content = $this->renderFile($this->resolvePath($view), $data);

        if ($layout === null) {
            return $content;
        }

        $layoutData = array_merge($data, ['content' => $content]);
        return $this->renderFile($this->resolvePath($layout), $layoutData);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function partial(string $view, array $data = []): string
    {
        return $this->renderFile($this->resolvePath($view), $data);
    }

    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function resolvePath(string $view): string
    {
        $relative = str_replace(['.', '\\'], '/', $view) . '.php';
        $path = $this->basePath . '/resources/views/' . ltrim($relative, '/');

        if (! is_file($path)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        return $path;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderFile(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
