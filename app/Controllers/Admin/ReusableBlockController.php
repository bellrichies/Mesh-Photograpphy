<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\ReusableBlock;
use App\Services\ReusableBlockService;
use App\Validators\ReusableBlockValidator;
use RuntimeException;

class ReusableBlockController
{
    public function index(Request $request, Response $response): Response
    {
        $blockModel = new ReusableBlock(app_database());
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $type = trim((string) $request->query('type', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $service = new ReusableBlockService($blockModel);
        $items = $blockModel->search($query, $status, $type, $limit, $offset);
        $total = $blockModel->count($query, $status, $type);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/reusable-blocks/index', [
            'title' => 'Reusable Blocks',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Reusable Blocks', 'href' => '#'],
            ],
            'adminPath' => admin_url(),
            'items' => $items,
            'filters' => ['q' => $query, 'status' => $status, 'type' => $type],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
            'types' => $service->availableTypes(),
        ], 'layouts/admin'));
    }

    public function create(Request $request, Response $response): Response
    {
        return $response->html($this->renderForm(null, [
            'name' => '',
            'block_key' => '',
            'block_type' => 'snippet',
            'title' => '',
            'body' => '',
            'json_payload' => '',
            'status' => 'draft',
        ], 'create'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, null);
            $this->validatePayload($payload);

            $id = (new ReusableBlock(app_database()))->create($payload);
            app_session()->flash('success', 'Reusable block created successfully.');
            return $response->redirect($this->adminPath() . '/blocks/edit/' . $id, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/blocks/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $block = (new ReusableBlock(app_database()))->findById($id);
        if (! is_array($block)) {
            throw new HttpException(404, 'Reusable block not found.');
        }

        return $response->html($this->renderForm($block, $block, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $blockModel = new ReusableBlock(app_database());
        $block = $blockModel->findById($id);
        if (! is_array($block)) {
            throw new HttpException(404, 'Reusable block not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, $id);
            $this->validatePayload($payload);

            $blockModel->update($id, $payload);
            app_session()->flash('success', 'Reusable block updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/blocks/edit/' . $id, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $blockModel = new ReusableBlock(app_database());
        $block = $blockModel->findById($id);
        if (! is_array($block)) {
            throw new HttpException(404, 'Reusable block not found.');
        }

        try {
            $this->verifyCsrf($request);
            $blockModel->delete($id);
            app_session()->flash('success', 'Reusable block deleted successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/blocks', 302);
    }

    /**
     * @param array<string, mixed>|null $block
     * @param array<string, mixed> $values
     */
    private function renderForm(?array $block, array $values, string $mode): string
    {
        $old = app_session()->getFlash('old_input', []);
        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
        }

        if (isset($values['json_payload']) && is_string($values['json_payload']) && $values['json_payload'] !== '') {
            $decoded = json_decode($values['json_payload'], true);
            if (is_array($decoded)) {
                $values['json_payload'] = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        $service = new ReusableBlockService(new ReusableBlock(app_database()));
        $view = new View(dirname(__DIR__, 3));
        return $view->render('admin/reusable-blocks/form', [
            'title' => $mode === 'create' ? 'Create Reusable Block' : 'Edit Reusable Block',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Reusable Blocks', 'href' => $this->adminPath() . '/blocks'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'mode' => $mode,
            'block' => $block,
            'values' => $values,
            'types' => $service->availableTypes(),
        ], 'layouts/admin');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request, ?int $ignoreId): array
    {
        $service = new ReusableBlockService(new ReusableBlock(app_database()));
        $requestedKey = trim((string) $request->post('block_key', ''));

        return [
            'name' => trim((string) $request->post('name', '')),
            'block_key' => $service->uniqueBlockKey($requestedKey !== '' ? $requestedKey : trim((string) $request->post('name', 'block')), $ignoreId),
            'block_type' => trim((string) $request->post('block_type', 'snippet')),
            'title' => trim((string) $request->post('title', '')),
            'body' => trim((string) $request->post('body', '')),
            'json_payload' => ($json = trim((string) $request->post('json_payload', ''))) !== '' ? $json : null,
            'status' => trim((string) $request->post('status', 'draft')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new ReusableBlockValidator();
        $input = $payload;
        $input['json_payload'] = $payload['json_payload'] !== null ? (string) $payload['json_payload'] : '';

        if ($validator->validate($input)) {
            return;
        }

        foreach ($validator->errors() as $messages) {
            if (isset($messages[0])) {
                throw new RuntimeException((string) $messages[0]);
            }
        }

        throw new RuntimeException('Reusable block validation failed.');
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    private function adminPath(): string
    {
        return admin_url();
    }
}
