<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\GalleryCategory;
use App\Validators\GalleryCategoryValidator;
use RuntimeException;

class GalleryCategoryController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $query = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $model = new GalleryCategory(app_database());
        $items = $model->search($query, $status, $limit, $offset);
        $total = $model->count($query, $status);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/galleries/categories', [
            'title' => 'Gallery Categories',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Gallery Categories', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => ['q' => $query, 'status' => $status],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
            'createDefaults' => [
                'name' => '',
                'slug' => '',
                'description' => '',
                'sort_order' => (string) $model->nextSortOrder(),
                'status' => 'published',
            ],
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, null);
            $this->validatePayload($payload);

            (new GalleryCategory(app_database()))->create($payload);
            app_session()->flash('success', 'Gallery category created successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/gallery-categories', 302);
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $categoryId = (int) ($params['id'] ?? 0);
        $category = (new GalleryCategory(app_database()))->findById($categoryId);
        if (! is_array($category)) {
            throw new HttpException(404, 'Gallery category not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, $categoryId);
            $this->validatePayload($payload);

            (new GalleryCategory(app_database()))->update($categoryId, $payload);
            app_session()->flash('success', 'Gallery category updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/gallery-categories', 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $categoryId = (int) ($params['id'] ?? 0);
        $category = (new GalleryCategory(app_database()))->findById($categoryId);
        if (! is_array($category)) {
            throw new HttpException(404, 'Gallery category not found.');
        }

        try {
            $this->verifyCsrf($request);
            (new GalleryCategory(app_database()))->delete($categoryId);
            app_session()->flash('success', 'Gallery category deleted successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/gallery-categories', 302);
    }

    public function slug(Request $request, Response $response): Response
    {
        $source = trim((string) $request->query('name', (string) $request->query('slug', '')));
        $categoryId = (int) $request->query('category_id', 0);
        $slug = $this->uniqueSlug($source !== '' ? $source : 'gallery-category', $categoryId > 0 ? $categoryId : null);

        return $this->jsonSuccess($response, 'Category slug generated successfully.', ['slug' => $slug]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request, ?int $categoryId): array
    {
        $requestedSlug = trim((string) $request->post('slug', ''));
        $source = $requestedSlug !== '' ? $requestedSlug : trim((string) $request->post('name', 'gallery-category'));

        return [
            'name' => trim((string) $request->post('name', '')),
            'slug' => $this->uniqueSlug($source, $categoryId),
            'description' => trim((string) $request->post('description', '')),
            'sort_order' => (int) $request->post('sort_order', 0),
            'status' => trim((string) $request->post('status', 'published')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new GalleryCategoryValidator();
        $input = $payload;
        $input['sort_order'] = (string) ($payload['sort_order'] ?? 0);

        if (! $validator->validate($input)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Gallery category validation failed.');
        }
    }

    private function uniqueSlug(string $source, ?int $ignoreId): string
    {
        $model = new GalleryCategory(app_database());
        $baseSlug = slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($model->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
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
