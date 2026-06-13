<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\PageSectionService;
use App\Validators\PageSectionValidator;
use RuntimeException;

class PageSectionController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response, array $params): Response
    {
        $page = $this->findPage((int) ($params['pageId'] ?? 0));
        $sections = (new PageSection(app_database()))->byPageId((int) $page['id']);
        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('admin/page-sections/index', [
            'title' => 'Page Sections',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Pages', 'href' => '/' . trim((string) config('app.admin_path', '/admin'), '/') . '/pages'],
                ['label' => 'Sections', 'href' => '#'],
            ],
            'adminPath' => admin_url(),
            'page' => $page,
            'sections' => $sections,
            'sectionTypes' => PageSectionService::TYPES,
            'editingSectionId' => (int) $request->query('edit', 0),
        ], 'layouts/admin'));
    }

    public function store(Request $request, Response $response, array $params): Response
    {
        $page = $this->findPage((int) ($params['pageId'] ?? 0));

        try {
            $this->verifyCsrf($request);

            $payload = $this->payloadFromRequest($request, (int) $page['id'], null);
            $this->validatePayload($payload);

            (new PageSection(app_database()))->create($payload);
            app_session()->flash('success', 'Section created successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->basePath((int) $page['id']), 302);
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $page = $this->findPage((int) ($params['pageId'] ?? 0));
        $section = $this->findSection((int) ($params['sectionId'] ?? 0), (int) $page['id']);

        try {
            $this->verifyCsrf($request);

            $payload = $this->payloadFromRequest($request, (int) $page['id'], (int) $section['id']);
            $this->validatePayload($payload);

            (new PageSection(app_database()))->update((int) $section['id'], $payload);
            app_session()->flash('success', 'Section updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->basePath((int) $page['id']) . '?edit=' . (int) $section['id'], 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $page = $this->findPage((int) ($params['pageId'] ?? 0));
        $section = $this->findSection((int) ($params['sectionId'] ?? 0), (int) $page['id']);

        try {
            $this->verifyCsrf($request);
            (new PageSection(app_database()))->delete((int) $section['id']);
            app_session()->flash('success', 'Section removed successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->basePath((int) $page['id']), 302);
    }

    public function reorder(Request $request, Response $response, array $params): Response
    {
        try {
            $this->verifyCsrf($request);
            $page = $this->findPage((int) ($params['pageId'] ?? 0));
            $section = $this->findSection((int) ($params['sectionId'] ?? 0), (int) $page['id']);
            $direction = trim((string) $request->post('direction', ''));

            if (! in_array($direction, ['up', 'down'], true)) {
                throw new RuntimeException('Invalid reorder direction.');
            }

            $items = (new PageSectionService(new PageSection(app_database())))->reorder((int) $page['id'], (int) $section['id'], $direction);

            return $this->jsonSuccess($response, 'Section order updated.', ['items' => $items]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function toggleStatus(Request $request, Response $response, array $params): Response
    {
        try {
            $this->verifyCsrf($request);
            $page = $this->findPage((int) ($params['pageId'] ?? 0));
            $section = $this->findSection((int) ($params['sectionId'] ?? 0), (int) $page['id']);
            $status = trim((string) $request->post('status', ''));

            if (! in_array($status, ['draft', 'published', 'hidden'], true)) {
                throw new RuntimeException('Invalid section status.');
            }

            (new PageSection(app_database()))->setStatus((int) $section['id'], $status);

            return $this->jsonSuccess($response, 'Section status updated.', ['status' => $status]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request, int $pageId, ?int $sectionId): array
    {
        $service = new PageSectionService(new PageSection(app_database()));
        $requestedKey = trim((string) $request->post('section_key', ''));
        $sectionKey = $service->uniqueSectionKey(
            $pageId,
            $requestedKey !== '' ? $requestedKey : trim((string) $request->post('title', 'section')),
            $sectionId
        );

        $jsonPayload = trim((string) $request->post('json_payload', ''));

        return [
            'page_id' => $pageId,
            'section_key' => $sectionKey,
            'section_type' => trim((string) $request->post('section_type', 'hero')),
            'title' => trim((string) $request->post('title', '')),
            'subtitle' => trim((string) $request->post('subtitle', '')),
            'body' => trim((string) $request->post('body', '')),
            'cta_label' => trim((string) $request->post('cta_label', '')),
            'cta_url' => trim((string) $request->post('cta_url', '')),
            'media_id' => $this->nullableInt($request->post('media_id', '')),
            'json_payload' => $jsonPayload !== '' ? $jsonPayload : null,
            'sort_order' => (int) $request->post('sort_order', (new PageSection(app_database()))->nextSortOrder($pageId)),
            'status' => trim((string) $request->post('status', 'draft')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new PageSectionValidator();
        $input = $payload;
        $input['media_id'] = $payload['media_id'] !== null ? (string) $payload['media_id'] : '';
        $input['json_payload'] = $payload['json_payload'] !== null ? (string) $payload['json_payload'] : '';
        $input['sort_order'] = (string) ($payload['sort_order'] ?? '0');

        if ($validator->validate($input)) {
            return;
        }

        foreach ($validator->errors() as $messages) {
            if (isset($messages[0])) {
                throw new RuntimeException((string) $messages[0]);
            }
        }

        throw new RuntimeException('Section validation failed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function findPage(int $pageId): array
    {
        $page = (new Page(app_database()))->findById($pageId);
        if (! is_array($page)) {
            throw new HttpException(404, 'Page not found.');
        }

        return $page;
    }

    /**
     * @return array<string, mixed>
     */
    private function findSection(int $sectionId, int $pageId): array
    {
        $section = (new PageSection(app_database()))->findById($sectionId);
        if (! is_array($section) || (int) ($section['page_id'] ?? 0) !== $pageId) {
            throw new HttpException(404, 'Section not found.');
        }

        return $section;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = trim((string) $value);
        return $value !== '' && ctype_digit($value) ? (int) $value : null;
    }

    private function basePath(int $pageId): string
    {
        return admin_url('pages/' . $pageId . '/sections');
    }
}
