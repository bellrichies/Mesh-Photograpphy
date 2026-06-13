<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\BlogTag;
use App\Services\BlogTaxonomyService;
use App\Validators\BlogTagValidator;
use RuntimeException;

class BlogTagController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $query = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $model = new BlogTag(app_database());
        $items = $model->search($query, $limit, $offset);
        $total = $model->count($query);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/blog/tags', [
            'title' => 'Blog Tags',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Blog Tags', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => ['q' => $query],
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
            ],
        ], 'layouts/admin'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, null);
            $this->validatePayload($payload);

            (new BlogTag(app_database()))->create($payload);
            app_session()->flash('success', 'Blog tag created successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/blog/tags', 302);
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $tagId = (int) ($params['id'] ?? 0);
        $tag = (new BlogTag(app_database()))->findById($tagId);
        if (! is_array($tag)) {
            throw new HttpException(404, 'Blog tag not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, $tagId);
            $this->validatePayload($payload);

            (new BlogTag(app_database()))->update($tagId, $payload);
            app_session()->flash('success', 'Blog tag updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/blog/tags', 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $tagId = (int) ($params['id'] ?? 0);
        $tag = (new BlogTag(app_database()))->findById($tagId);
        if (! is_array($tag)) {
            throw new HttpException(404, 'Blog tag not found.');
        }

        try {
            $this->verifyCsrf($request);
            (new BlogTag(app_database()))->delete($tagId);
            app_session()->flash('success', 'Blog tag deleted successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/blog/tags', 302);
    }

    public function slug(Request $request, Response $response): Response
    {
        $source = trim((string) $request->query('name', (string) $request->query('slug', '')));
        $tagId = (int) $request->query('tag_id', 0);
        $slug = $this->service()->generateUniqueTagSlug($source !== '' ? $source : 'blog-tag', $tagId > 0 ? $tagId : null);

        return $this->jsonSuccess($response, 'Tag slug generated successfully.', ['slug' => $slug]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request, ?int $tagId): array
    {
        $requestedSlug = trim((string) $request->post('slug', ''));
        $source = $requestedSlug !== '' ? $requestedSlug : trim((string) $request->post('name', 'blog-tag'));

        return [
            'name' => trim((string) $request->post('name', '')),
            'slug' => $this->service()->generateUniqueTagSlug($source, $tagId),
            'description' => trim((string) $request->post('description', '')),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new BlogTagValidator();
        if (! $validator->validate($payload)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Blog tag validation failed.');
        }
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    private function service(): BlogTaxonomyService
    {
        return new BlogTaxonomyService(new \App\Repositories\BlogRepository(app_database()));
    }

    private function adminPath(): string
    {
        return admin_url();
    }
}
