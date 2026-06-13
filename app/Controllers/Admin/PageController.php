<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Services\PageService;
use App\Validators\PageValidator;
use RuntimeException;

class PageController
{
    use InteractsWithAdminJson;

    /** @var array<int, string> */
    private const TEMPLATES = ['default', 'homepage', 'legal', 'landing'];

    public function index(Request $request, Response $response): Response
    {
        $pageModel = new Page(app_database());
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $template = trim((string) $request->query('template', ''));
        $pageNumber = max(1, (int) $request->query('page', 1));
        $limit = 15;
        $offset = ($pageNumber - 1) * $limit;

        $rows = $pageModel->search($query, $status, $template, $limit, $offset);
        $total = $pageModel->count($query, $status, $template);

        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('admin/pages/index', [
            'title' => 'Pages',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Pages', 'href' => '#'],
            ],
            'adminPath' => admin_url(),
            'items' => $rows,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'template' => $template,
            ],
            'pagination' => [
                'page' => $pageNumber,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
            'templates' => self::TEMPLATES,
        ], 'layouts/admin'));
    }

    public function create(Request $request, Response $response): Response
    {
        return $response->html($this->renderEditor(null, [
            'title' => '',
            'slug' => '',
            'template' => 'default',
            'status' => 'draft',
            'excerpt' => '',
            'body' => '',
            'featured_media_id' => '',
            'parent_id' => '',
            'sort_order' => (string) (new Page(app_database()))->nextSortOrder(),
            'is_system' => '0',
            'published_at' => '',
        ], [
            'meta_title' => '',
            'meta_description' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'canonical_url' => '',
            'robots_index' => '1',
            'robots_follow' => '1',
        ], 'create'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);

            $payload = $this->payloadFromRequest($request, null);
            $this->validatePayload($payload);

            $pageModel = new Page(app_database());
            $pageId = $pageModel->create($payload['page']);
            $this->persistSeo($pageId, $payload['seo']);
            $this->logPageMutation($request, 'created', $pageId, $payload['page']);

            app_session()->flash('success', 'Page created successfully.');
            return $response->redirect($this->adminPath() . '/pages/edit/' . $pageId, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/pages/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $pageId = (int) ($params['id'] ?? 0);
        $page = (new Page(app_database()))->findById($pageId);
        if (! is_array($page)) {
            throw new HttpException(404, 'Page not found.');
        }

        $seo = (new SeoMeta(app_database()))->findFor('page', $pageId) ?? [];

        return $response->html($this->renderEditor($page, $page, $seo, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $pageId = (int) ($params['id'] ?? 0);
        $pageModel = new Page(app_database());
        $existing = $pageModel->findById($pageId);
        if (! is_array($existing)) {
            throw new HttpException(404, 'Page not found.');
        }

        try {
            $this->verifyCsrf($request);

            $payload = $this->payloadFromRequest($request, $pageId);
            $this->validatePayload($payload);

            $pageModel->update($pageId, $payload['page']);
            $this->persistSeo($pageId, $payload['seo']);
            $this->logPageMutation($request, 'updated', $pageId, $payload['page'], $existing);

            app_session()->flash('success', 'Page updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/pages/edit/' . $pageId, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $pageId = (int) ($params['id'] ?? 0);
        $pageModel = new Page(app_database());
        $page = $pageModel->findById($pageId);
        if (! is_array($page)) {
            throw new HttpException(404, 'Page not found.');
        }

        try {
            $this->verifyCsrf($request);

            if ((int) ($page['is_system'] ?? 0) === 1) {
                throw new RuntimeException('System pages cannot be deleted.');
            }

            $pageModel->softDelete($pageId);
            app_security_logger()->log('page.deleted', $request, 'page', $pageId, 'Archived page.', [
                'title' => (string) ($page['title'] ?? ''),
                'slug' => (string) ($page['slug'] ?? ''),
            ]);
            app_session()->flash('success', 'Page deleted successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/pages', 302);
    }

    public function slug(Request $request, Response $response): Response
    {
        $source = trim((string) $request->query('title', (string) $request->query('slug', '')));
        $pageId = (int) $request->query('page_id', 0);
        $service = new PageService(new Page(app_database()));
        $slug = $service->generateUniqueSlug($source !== '' ? $source : 'page', $pageId > 0 ? $pageId : null);

        return $this->jsonSuccess($response, 'Slug generated successfully.', [
            'slug' => $slug,
            'available' => true,
        ]);
    }

    /**
     * @param array<string, mixed>|null $page
     * @param array<string, mixed> $pageValues
     * @param array<string, mixed> $seoValues
     */
    private function renderEditor(?array $page, array $pageValues, array $seoValues, string $mode): string
    {
        $view = new View(dirname(__DIR__, 3));
        $old = app_session()->getFlash('old_input', []);
        $pageId = $page !== null ? (int) ($page['id'] ?? 0) : null;

        if (is_array($old) && $old !== []) {
            $pageValues = array_merge($pageValues, $old);
            $seoValues = array_merge($seoValues, [
                'meta_title' => $old['meta_title'] ?? ($seoValues['meta_title'] ?? ''),
                'meta_description' => $old['meta_description'] ?? ($seoValues['meta_description'] ?? ''),
                'og_title' => $old['og_title'] ?? ($seoValues['og_title'] ?? ''),
                'og_description' => $old['og_description'] ?? ($seoValues['og_description'] ?? ''),
                'og_image' => $old['og_image_media_id'] ?? ($seoValues['og_image'] ?? ''),
                'canonical_url' => $old['canonical_url'] ?? ($seoValues['canonical_url'] ?? ''),
                'robots_index' => $old['robots_index'] ?? ($seoValues['robots_index'] ?? '1'),
                'robots_follow' => $old['robots_follow'] ?? ($seoValues['robots_follow'] ?? '1'),
            ]);
        }

        return $view->render('admin/pages/form', [
            'title' => $mode === 'create' ? 'Create Page' : 'Edit Page',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Pages', 'href' => $this->adminPath() . '/pages'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'mode' => $mode,
            'page' => $page,
            'pageValues' => $pageValues,
            'seoValues' => $seoValues,
            'templates' => self::TEMPLATES,
            'adminPath' => $this->adminPath(),
            'parentOptions' => (new Page(app_database()))->parentOptions($pageId),
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin');
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    /**
     * @return array{page: array<string, mixed>, seo: array<string, mixed>}
     */
    private function payloadFromRequest(Request $request, ?int $pageId): array
    {
        $pageService = new PageService(new Page(app_database()));
        $inputSlug = trim((string) $request->post('slug', ''));
        $slugSource = $inputSlug !== '' ? $inputSlug : trim((string) $request->post('title', 'page'));
        $slug = $pageService->generateUniqueSlug($slugSource, $pageId);

        $page = [
            'title' => trim((string) $request->post('title', '')),
            'slug' => $slug,
            'template' => trim((string) $request->post('template', 'default')),
            'status' => trim((string) $request->post('status', 'draft')),
            'excerpt' => trim((string) $request->post('excerpt', '')),
            'body' => trim((string) $request->post('body', '')),
            'featured_media_id' => $this->nullableInt($request->post('featured_media_id', '')),
            'parent_id' => $this->nullableInt($request->post('parent_id', '')),
            'sort_order' => (int) $request->post('sort_order', 0),
            'is_system' => $request->post('is_system', '0') === '1' ? 1 : 0,
            'published_at' => $pageService->normalizePublishedAt(
                trim((string) $request->post('status', 'draft')),
                trim((string) $request->post('published_at', ''))
            ),
            'updated_by' => app_auth()->id(),
            'created_by' => app_auth()->id(),
        ];

        $seo = [
            'meta_title' => trim((string) $request->post('meta_title', '')),
            'meta_description' => trim((string) $request->post('meta_description', '')),
            'og_title' => trim((string) $request->post('og_title', '')),
            'og_description' => trim((string) $request->post('og_description', '')),
            'og_image' => trim((string) $request->post('og_image_media_id', '')),
            'canonical_url' => trim((string) $request->post('canonical_url', '')),
            'robots_index' => $request->post('robots_index', '1') === '1' ? 1 : 0,
            'robots_follow' => $request->post('robots_follow', '1') === '1' ? 1 : 0,
        ];

        return ['page' => $page, 'seo' => $seo];
    }

    /**
     * @param array{page: array<string, mixed>, seo: array<string, mixed>} $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new PageValidator();
        $input = array_merge($payload['page'], [
            'featured_media_id' => $payload['page']['featured_media_id'] !== null ? (string) $payload['page']['featured_media_id'] : '',
            'parent_id' => $payload['page']['parent_id'] !== null ? (string) $payload['page']['parent_id'] : '',
            'is_system' => (string) $payload['page']['is_system'],
            'sort_order' => (string) $payload['page']['sort_order'],
            'published_at' => $payload['page']['published_at'] ?? '',
            'robots_index' => (string) $payload['seo']['robots_index'],
            'robots_follow' => (string) $payload['seo']['robots_follow'],
            'og_image_media_id' => (string) ($payload['seo']['og_image'] ?? ''),
        ], $payload['seo']);

        if ($validator->validate($input)) {
            return;
        }

        $errors = $validator->errors();
        foreach ($errors as $fieldErrors) {
            if (isset($fieldErrors[0])) {
                throw new RuntimeException((string) $fieldErrors[0]);
            }
        }

        throw new RuntimeException('Page validation failed.');
    }

    /**
     * @param array<string, mixed> $seo
     */
    private function persistSeo(int $pageId, array $seo): void
    {
        (new SeoMeta(app_database()))->upsert('page', $pageId, $seo);
    }

    private function nullableInt(mixed $value): ?int
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' && ctype_digit($stringValue) ? (int) $stringValue : null;
    }

    private function adminPath(): string
    {
        return admin_url();
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed>|null $existing
     */
    private function logPageMutation(Request $request, string $event, int $pageId, array $page, ?array $existing = null): void
    {
        $status = (string) ($page['status'] ?? 'draft');
        $action = $status === 'published' && (string) ($existing['status'] ?? '') !== 'published'
            ? 'page.published'
            : 'page.' . $event;

        app_security_logger()->log($action, $request, 'page', $pageId, 'Page record updated.', [
            'title' => (string) ($page['title'] ?? ''),
            'slug' => (string) ($page['slug'] ?? ''),
            'status' => $status,
            'previous_status' => (string) ($existing['status'] ?? ''),
        ]);
    }
}
