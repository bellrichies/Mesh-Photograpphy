<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\HeroSlide;
use App\Repositories\MediaRepository;
use App\Validators\HeroSlideValidator;
use RuntimeException;

class HeroSlideController
{
    public function index(Request $request, Response $response): Response
    {
        $model = new HeroSlide(app_database());
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $pageNumber = max(1, (int) $request->query('page', 1));
        $limit = 15;
        $offset = ($pageNumber - 1) * $limit;

        $items = $model->search($query, $status, $limit, $offset);
        $total = $model->count($query, $status);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/hero-slides/index', [
            'title' => 'Home Hero Slides',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Home Hero Slides', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => [
                'q' => $query,
                'status' => $status,
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
        return $response->html($this->renderForm(null, [
            'title' => '',
            'subtitle' => '',
            'description' => '',
            'image_media_id' => '',
            'image_alt_text' => '',
            'primary_cta_label' => '',
            'primary_cta_url' => '',
            'secondary_cta_label' => '',
            'secondary_cta_url' => '',
            'sort_order' => (string) (new HeroSlide(app_database()))->nextSortOrder(),
            'status' => 'draft',
        ], 'create'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request);
            $this->validatePayload($payload);

            $slideId = (new HeroSlide(app_database()))->create($payload);
            app_session()->flash('success', 'Hero slide created successfully.');
            return $response->redirect($this->adminPath() . '/hero-slides/edit/' . $slideId, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/hero-slides/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $slideId = (int) ($params['id'] ?? 0);
        $slide = (new HeroSlide(app_database()))->findById($slideId);
        if (! is_array($slide)) {
            throw new HttpException(404, 'Hero slide not found.');
        }

        return $response->html($this->renderForm($slide, $slide, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $slideId = (int) ($params['id'] ?? 0);
        $model = new HeroSlide(app_database());
        $existing = $model->findById($slideId);
        if (! is_array($existing)) {
            throw new HttpException(404, 'Hero slide not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request);
            $this->validatePayload($payload);

            $model->update($slideId, $payload);
            app_session()->flash('success', 'Hero slide updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/hero-slides/edit/' . $slideId, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $slideId = (int) ($params['id'] ?? 0);
        $model = new HeroSlide(app_database());
        $slide = $model->findById($slideId);
        if (! is_array($slide)) {
            throw new HttpException(404, 'Hero slide not found.');
        }

        try {
            $this->verifyCsrf($request);
            $model->softDelete($slideId);
            app_session()->flash('success', 'Hero slide archived successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/hero-slides', 302);
    }

    /**
     * @param array<string, mixed>|null $slide
     * @param array<string, mixed> $values
     */
    private function renderForm(?array $slide, array $values, string $mode): string
    {
        $old = app_session()->getFlash('old_input', []);
        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
        }

        $view = new View(dirname(__DIR__, 3));
        return $view->render('admin/hero-slides/form', [
            'title' => $mode === 'create' ? 'Create Hero Slide' : 'Edit Hero Slide',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Home Hero Slides', 'href' => $this->adminPath() . '/hero-slides'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'mode' => $mode,
            'slide' => $slide,
            'values' => $values,
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request): array
    {
        return [
            'title' => trim((string) $request->post('title', '')),
            'subtitle' => trim((string) $request->post('subtitle', '')),
            'description' => trim((string) $request->post('description', '')),
            'image_media_id' => $this->nullableInt($request->post('image_media_id', '')),
            'image_alt_text' => trim((string) $request->post('image_alt_text', '')),
            'primary_cta_label' => trim((string) $request->post('primary_cta_label', '')),
            'primary_cta_url' => trim((string) $request->post('primary_cta_url', '')),
            'secondary_cta_label' => trim((string) $request->post('secondary_cta_label', '')),
            'secondary_cta_url' => trim((string) $request->post('secondary_cta_url', '')),
            'sort_order' => (int) $request->post('sort_order', 0),
            'status' => trim((string) $request->post('status', 'draft')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new HeroSlideValidator();
        $input = $payload;
        $input['image_media_id'] = $payload['image_media_id'] !== null ? (string) $payload['image_media_id'] : '';
        $input['sort_order'] = (string) ($payload['sort_order'] ?? 0);

        if (! $validator->validate($input)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Hero slide validation failed.');
        }

        $imageMediaId = (int) ($payload['image_media_id'] ?? 0);
        if ($imageMediaId <= 0 || ! is_array((new MediaRepository(app_database()))->findById($imageMediaId))) {
            throw new RuntimeException('Selected slide image was not found.');
        }

        $primaryLabel = trim((string) ($payload['primary_cta_label'] ?? ''));
        $primaryUrl = trim((string) ($payload['primary_cta_url'] ?? ''));
        if (($primaryLabel === '') !== ($primaryUrl === '')) {
            throw new RuntimeException('Primary CTA label and URL must be provided together.');
        }

        $secondaryLabel = trim((string) ($payload['secondary_cta_label'] ?? ''));
        $secondaryUrl = trim((string) ($payload['secondary_cta_url'] ?? ''));
        if (($secondaryLabel === '') !== ($secondaryUrl === '')) {
            throw new RuntimeException('Secondary CTA label and URL must be provided together.');
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

    private function adminPath(): string
    {
        return admin_url();
    }
}
