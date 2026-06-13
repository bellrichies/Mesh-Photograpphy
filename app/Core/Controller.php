<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected View $view
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = [], ?string $layout = null): Response
    {
        return $this->response->html($this->view->render($view, $data, $layout));
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $statusCode = 200): Response
    {
        return $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return $this->response->redirect($url, $statusCode);
    }
}
