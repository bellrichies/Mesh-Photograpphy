<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\Testimonial;
use App\Repositories\MediaRepository;
use App\Validators\TestimonialValidator;
use RuntimeException;

class TestimonialController
{
    public function index(Request $request, Response $response): Response
    {
        $filters = [
            'query' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'featured' => trim((string) $request->query('featured', '')),
            'service_id' => (int) $request->query('service_id', 0),
            'gallery_id' => (int) $request->query('gallery_id', 0),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $model = new Testimonial(app_database());
        $items = $model->search($filters, $limit, $offset);
        $total = $model->count($filters);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/testimonials/index', [
            'title' => 'Testimonials',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Testimonials', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => [
                'q' => $filters['query'],
                'status' => $filters['status'],
                'featured' => $filters['featured'],
                'service_id' => $filters['service_id'] > 0 ? (string) $filters['service_id'] : '',
                'gallery_id' => $filters['gallery_id'] > 0 ? (string) $filters['gallery_id'] : '',
            ],
            'services' => (new Service(app_database()))->allPublished(100),
            'galleries' => (new Gallery(app_database()))->searchableOptions(),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
        ], 'layouts/admin'));
    }

    public function create(Request $request, Response $response): Response
    {
        return $response->html($this->renderForm(null, [
            'client_name' => '',
            'client_label' => '',
            'quote' => '',
            'long_form_story' => '',
            'rating' => '',
            'featured' => '0',
            'service_id' => '',
            'gallery_id' => '',
            'portrait_media_id' => '',
            'event_date' => '',
            'location' => '',
            'status' => 'draft',
            'sort_order' => (string) (new Testimonial(app_database()))->nextSortOrder(),
        ], 'create'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request);
            $this->validatePayload($payload);

            $id = (new Testimonial(app_database()))->create($payload);
            app_session()->flash('success', 'Testimonial created successfully.');
            return $response->redirect($this->adminPath() . '/testimonials/edit/' . $id, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/testimonials/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $testimonial = (new Testimonial(app_database()))->findById($id);
        if (! is_array($testimonial)) {
            throw new HttpException(404, 'Testimonial not found.');
        }

        return $response->html($this->renderForm($testimonial, $testimonial, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $model = new Testimonial(app_database());
        $existing = $model->findById($id);
        if (! is_array($existing)) {
            throw new HttpException(404, 'Testimonial not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request);
            $this->validatePayload($payload);

            $model->update($id, $payload);
            app_session()->flash('success', 'Testimonial updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/testimonials/edit/' . $id, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $model = new Testimonial(app_database());
        $existing = $model->findById($id);
        if (! is_array($existing)) {
            throw new HttpException(404, 'Testimonial not found.');
        }

        try {
            $this->verifyCsrf($request);
            $model->softDelete($id);
            app_session()->flash('success', 'Testimonial archived successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/testimonials', 302);
    }

    /**
     * @param array<string, mixed>|null $testimonial
     * @param array<string, mixed> $values
     */
    private function renderForm(?array $testimonial, array $values, string $mode): string
    {
        $old = app_session()->getFlash('old_input', []);
        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
        }

        $view = new View(dirname(__DIR__, 3));
        return $view->render('admin/testimonials/form', [
            'title' => $mode === 'create' ? 'Create Testimonial' : 'Edit Testimonial',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Testimonials', 'href' => $this->adminPath() . '/testimonials'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'mode' => $mode,
            'testimonial' => $testimonial,
            'values' => $values,
            'services' => (new Service(app_database()))->allPublished(100),
            'galleries' => (new Gallery(app_database()))->searchableOptions(),
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request): array
    {
        return [
            'client_name' => trim((string) $request->post('client_name', '')),
            'client_label' => trim((string) $request->post('client_label', '')),
            'quote' => trim((string) $request->post('quote', '')),
            'long_form_story' => trim((string) $request->post('long_form_story', '')),
            'rating' => $this->nullableInt($request->post('rating', '')),
            'featured' => $request->post('featured', '0') === '1' ? 1 : 0,
            'service_id' => $this->nullableInt($request->post('service_id', '')),
            'gallery_id' => $this->nullableInt($request->post('gallery_id', '')),
            'portrait_media_id' => $this->nullableInt($request->post('portrait_media_id', '')),
            'event_date' => $this->nullableDate($request->post('event_date', '')),
            'location' => trim((string) $request->post('location', '')),
            'status' => trim((string) $request->post('status', 'draft')),
            'sort_order' => (int) $request->post('sort_order', 0),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new TestimonialValidator();
        $input = $payload;
        $input['rating'] = $payload['rating'] !== null ? (string) $payload['rating'] : '';
        $input['featured'] = (string) ($payload['featured'] ?? 0);
        $input['service_id'] = $payload['service_id'] !== null ? (string) $payload['service_id'] : '';
        $input['gallery_id'] = $payload['gallery_id'] !== null ? (string) $payload['gallery_id'] : '';
        $input['portrait_media_id'] = $payload['portrait_media_id'] !== null ? (string) $payload['portrait_media_id'] : '';
        $input['sort_order'] = (string) ($payload['sort_order'] ?? 0);

        if (! $validator->validate($input)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Testimonial validation failed.');
        }

        if (($payload['service_id'] ?? null) !== null && ! is_array((new Service(app_database()))->findById((int) $payload['service_id']))) {
            throw new RuntimeException('Selected service was not found.');
        }

        if (($payload['gallery_id'] ?? null) !== null && ! is_array((new Gallery(app_database()))->findById((int) $payload['gallery_id']))) {
            throw new RuntimeException('Selected gallery was not found.');
        }

        if (($payload['portrait_media_id'] ?? null) !== null && ! is_array((new MediaRepository(app_database()))->findById((int) $payload['portrait_media_id']))) {
            throw new RuntimeException('Selected portrait media was not found.');
        }
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

    private function nullableDate(mixed $value): ?string
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' ? $stringValue : null;
    }

    private function adminPath(): string
    {
        return admin_url();
    }
}
