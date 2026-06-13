<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\SeoMeta;
use App\Models\Service;
use App\Repositories\MediaRepository;
use App\Services\ServiceService;
use App\Validators\ServiceValidator;
use RuntimeException;

class ServiceController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $serviceModel = new Service(app_database());
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $featured = trim((string) $request->query('featured', ''));
        $pageNumber = max(1, (int) $request->query('page', 1));
        $limit = 15;
        $offset = ($pageNumber - 1) * $limit;

        $items = $serviceModel->search($query, $status, $featured, $limit, $offset);
        $total = $serviceModel->count($query, $status, $featured);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/services/index', [
            'title' => 'Services',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Services', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'featured' => $featured,
            ],
            'pagination' => [
                'page' => $pageNumber,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
        ], 'layouts/admin'));
    }

    public function create(Request $request, Response $response): Response
    {
        return $response->html($this->renderEditor(null, [
            'title' => '',
            'slug' => '',
            'short_description' => '',
            'full_description' => '',
            'cover_media_id' => '',
            'sort_order' => (string) (new Service(app_database()))->nextSortOrder(),
            'featured' => '0',
            'status' => 'draft',
            'price_display' => '',
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

            $serviceId = (new Service(app_database()))->create($payload['service']);
            $this->persistSeo($serviceId, $payload['seo']);
            $this->logServiceMutation($request, 'created', $serviceId, $payload['service']);

            app_session()->flash('success', 'Service created successfully.');
            return $response->redirect($this->adminPath() . '/services/edit/' . $serviceId, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/services/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $serviceId = (int) ($params['id'] ?? 0);
        $service = (new Service(app_database()))->findById($serviceId);
        if (! is_array($service)) {
            throw new HttpException(404, 'Service not found.');
        }

        $seo = (new SeoMeta(app_database()))->findFor('service', $serviceId) ?? [];

        return $response->html($this->renderEditor($service, $service, $seo, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $serviceId = (int) ($params['id'] ?? 0);
        $serviceModel = new Service(app_database());
        $existing = $serviceModel->findById($serviceId);
        if (! is_array($existing)) {
            throw new HttpException(404, 'Service not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, $serviceId);
            $this->validatePayload($payload);

            $serviceModel->update($serviceId, $payload['service']);
            $this->persistSeo($serviceId, $payload['seo']);
            $this->logServiceMutation($request, 'updated', $serviceId, $payload['service'], $existing);

            app_session()->flash('success', 'Service updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/services/edit/' . $serviceId, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $serviceId = (int) ($params['id'] ?? 0);
        $serviceModel = new Service(app_database());
        $service = $serviceModel->findById($serviceId);
        if (! is_array($service)) {
            throw new HttpException(404, 'Service not found.');
        }

        try {
            $this->verifyCsrf($request);
            $serviceModel->softDelete($serviceId);
            app_security_logger()->log('service.deleted', $request, 'service', $serviceId, 'Archived service.', [
                'title' => (string) ($service['title'] ?? ''),
                'slug' => (string) ($service['slug'] ?? ''),
            ]);
            app_session()->flash('success', 'Service archived successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/services', 302);
    }

    public function slug(Request $request, Response $response): Response
    {
        $source = trim((string) $request->query('title', (string) $request->query('slug', '')));
        $serviceId = (int) $request->query('service_id', 0);
        $slug = (new ServiceService(new Service(app_database())))->generateUniqueSlug($source !== '' ? $source : 'service', $serviceId > 0 ? $serviceId : null);

        return $this->jsonSuccess($response, 'Slug generated successfully.', ['slug' => $slug, 'available' => true]);
    }

    /**
     * @param array<string, mixed>|null $service
     * @param array<string, mixed> $serviceValues
     * @param array<string, mixed> $seoValues
     */
    private function renderEditor(?array $service, array $serviceValues, array $seoValues, string $mode): string
    {
        $view = new View(dirname(__DIR__, 3));
        $old = app_session()->getFlash('old_input', []);
        $serviceId = $service !== null ? (int) ($service['id'] ?? 0) : null;

        if (is_array($old) && $old !== []) {
            $serviceValues = array_merge($serviceValues, $old);
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

        return $view->render('admin/services/form', [
            'title' => $mode === 'create' ? 'Create Service' : 'Edit Service',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Services', 'href' => $this->adminPath() . '/services'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'mode' => $mode,
            'service' => $service,
            'serviceValues' => $serviceValues,
            'seoValues' => $seoValues,
            'adminPath' => $this->adminPath(),
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin');
    }

    /**
     * @return array{service: array<string, mixed>, seo: array<string, mixed>}
     */
    private function payloadFromRequest(Request $request, ?int $serviceId): array
    {
        $serviceHelper = new ServiceService(new Service(app_database()));
        $inputSlug = trim((string) $request->post('slug', ''));
        $slugSource = $inputSlug !== '' ? $inputSlug : trim((string) $request->post('title', 'service'));

        $service = [
            'title' => trim((string) $request->post('title', '')),
            'slug' => $serviceHelper->generateUniqueSlug($slugSource, $serviceId),
            'short_description' => trim((string) $request->post('short_description', '')),
            'full_description' => trim((string) $request->post('full_description', '')),
            'cover_media_id' => $this->nullableInt($request->post('cover_media_id', '')),
            'sort_order' => (int) $request->post('sort_order', 0),
            'featured' => $request->post('featured', '0') === '1' ? 1 : 0,
            'status' => trim((string) $request->post('status', 'draft')),
            'price_display' => trim((string) $request->post('price_display', '')),
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

        return ['service' => $service, 'seo' => $seo];
    }

    /**
     * @param array{service: array<string, mixed>, seo: array<string, mixed>} $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new ServiceValidator();
        $input = array_merge($payload['service'], [
            'cover_media_id' => $payload['service']['cover_media_id'] !== null ? (string) $payload['service']['cover_media_id'] : '',
            'sort_order' => (string) $payload['service']['sort_order'],
            'featured' => (string) $payload['service']['featured'],
            'robots_index' => (string) $payload['seo']['robots_index'],
            'robots_follow' => (string) $payload['seo']['robots_follow'],
            'og_image_media_id' => (string) ($payload['seo']['og_image'] ?? ''),
        ], $payload['seo']);

        if ($validator->validate($input)) {
            if ($payload['service']['cover_media_id'] !== null && ! is_array((new MediaRepository(app_database()))->findById((int) $payload['service']['cover_media_id']))) {
                throw new RuntimeException('Selected cover media was not found.');
            }

            if (($payload['seo']['og_image'] ?? '') !== '') {
                $ogImageId = (int) $payload['seo']['og_image'];
                if ($ogImageId > 0 && ! is_array((new MediaRepository(app_database()))->findById($ogImageId))) {
                    throw new RuntimeException('Selected OG image was not found.');
                }
            }

            return;
        }

        foreach ($validator->errors() as $fieldErrors) {
            if (isset($fieldErrors[0])) {
                throw new RuntimeException((string) $fieldErrors[0]);
            }
        }

        throw new RuntimeException('Service validation failed.');
    }

    /**
     * @param array<string, mixed> $seo
     */
    private function persistSeo(int $serviceId, array $seo): void
    {
        (new SeoMeta(app_database()))->upsert('service', $serviceId, $seo);
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
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
     * @param array<string, mixed> $service
     * @param array<string, mixed>|null $existing
     */
    private function logServiceMutation(Request $request, string $event, int $serviceId, array $service, ?array $existing = null): void
    {
        $status = (string) ($service['status'] ?? 'draft');
        $action = $status === 'published' && (string) ($existing['status'] ?? '') !== 'published'
            ? 'service.published'
            : 'service.' . $event;

        app_security_logger()->log($action, $request, 'service', $serviceId, 'Service record updated.', [
            'title' => (string) ($service['title'] ?? ''),
            'slug' => (string) ($service['slug'] ?? ''),
            'status' => $status,
            'previous_status' => (string) ($existing['status'] ?? ''),
        ]);
    }
}
