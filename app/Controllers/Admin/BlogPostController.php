<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostCategory;
use App\Models\BlogPostMedia;
use App\Models\BlogPostTag;
use App\Models\BlogTag;
use App\Models\Gallery;
use App\Models\SeoMeta;
use App\Models\User;
use App\Repositories\BlogRepository;
use App\Repositories\MediaRepository;
use App\Services\BlogPostService;
use App\Services\BlogPublishingService;
use App\Services\BlogRevisionService;
use App\Services\MediaUsageService;
use App\Validators\BlogPostValidator;
use RuntimeException;

class BlogPostController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $filters = [
            'query' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'author_id' => (int) $request->query('author_id', 0),
            'category_id' => (int) $request->query('category_id', 0),
            'featured' => trim((string) $request->query('featured', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $repository = new BlogRepository(app_database());
        $items = $repository->adminIndex($filters, $limit, $offset);
        $total = $repository->countAdmin($filters);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/blog/posts/index', [
            'title' => 'Blog Posts',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Blog Posts', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => [
                'q' => $filters['query'],
                'status' => $filters['status'],
                'author_id' => $filters['author_id'] > 0 ? (string) $filters['author_id'] : '',
                'category_id' => $filters['category_id'] > 0 ? (string) $filters['category_id'] : '',
                'featured' => $filters['featured'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
            ],
            'authors' => (new User(app_database()))->allActive(),
            'categories' => (new BlogCategory(app_database()))->all(),
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
        return $response->html($this->renderEditor(null, [
            'author_id' => (string) (app_auth()->id() ?? 0),
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'body_long' => '',
            'featured_image_id' => '',
            'cover_gallery_id' => '',
            'status' => 'draft',
            'visibility' => 'public',
            'is_featured' => '0',
            'allow_comments' => '0',
            'published_at' => '',
            'scheduled_at' => '',
            'meta_summary' => '',
            'canonical_url' => '',
            'reading_time' => '',
            'category_ids' => [],
            'tag_ids' => [],
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
            $payload = $this->payloadFromRequest($request, null, null);
            $this->validatePayload($payload);

            $postId = $this->publishingService()->createPost($payload['post'], $payload['category_ids'], $payload['tag_ids'], $payload['seo']);
            $this->logPostMutation($request, 'created', $postId, $payload['post']);
            app_session()->flash('success', 'Blog post created successfully.');
            return $response->redirect($this->adminPath() . '/blog/posts/edit/' . $postId, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/blog/posts/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $postId = (int) ($params['id'] ?? 0);
        $post = (new BlogPost(app_database()))->findById($postId);
        if (! is_array($post)) {
            throw new HttpException(404, 'Blog post not found.');
        }

        $post['category_ids'] = array_map(static fn (array $item): string => (string) ($item['id'] ?? ''), (new BlogPost(app_database()))->categories($postId));
        $post['tag_ids'] = array_map(static fn (array $item): string => (string) ($item['id'] ?? ''), (new BlogPost(app_database()))->tags($postId));

        $seo = (new SeoMeta(app_database()))->findFor('blog_post', $postId) ?? [];

        return $response->html($this->renderEditor($post, $post, $seo, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $postId = (int) ($params['id'] ?? 0);
        $postModel = new BlogPost(app_database());
        $existing = $postModel->findById($postId);
        if (! is_array($existing)) {
            throw new HttpException(404, 'Blog post not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, $postId, $existing);
            $this->validatePayload($payload);

            $this->publishingService()->updatePost($postId, $existing, $payload['post'], $payload['category_ids'], $payload['tag_ids'], $payload['seo'], app_auth()->id());
            $this->logPostMutation($request, 'updated', $postId, $payload['post'], $existing);
            app_session()->flash('success', 'Blog post updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/blog/posts/edit/' . $postId, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $postId = (int) ($params['id'] ?? 0);
        $post = (new BlogPost(app_database()))->findById($postId);
        if (! is_array($post)) {
            throw new HttpException(404, 'Blog post not found.');
        }

        try {
            $this->verifyCsrf($request);
            $this->publishingService()->deletePost($postId);
            app_security_logger()->log('blog_post.deleted', $request, 'blog_post', $postId, 'Archived blog post.', [
                'title' => (string) ($post['title'] ?? ''),
                'slug' => (string) ($post['slug'] ?? ''),
            ]);
            app_session()->flash('success', 'Blog post archived successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/blog/posts', 302);
    }

    public function slug(Request $request, Response $response): Response
    {
        $source = trim((string) $request->query('title', (string) $request->query('slug', '')));
        $postId = (int) $request->query('post_id', 0);
        $slug = $this->postService()->generateUniquePostSlug($source !== '' ? $source : 'blog-post', $postId > 0 ? $postId : null);

        return $this->jsonSuccess($response, 'Post slug generated successfully.', ['slug' => $slug, 'available' => true]);
    }

    public function autosave(Request $request, Response $response, array $params): Response
    {
        $postId = (int) ($params['id'] ?? 0);
        $postModel = new BlogPost(app_database());
        $existing = $postModel->findById($postId);

        if (! is_array($existing)) {
            return $this->jsonError($response, 'Blog post not found.', [], [], 404);
        }

        try {
            $this->verifyCsrf($request);

            $title = trim((string) $request->post('title', (string) ($existing['title'] ?? '')));
            $inputSlug = trim((string) $request->post('slug', ''));
            $slugSource = $inputSlug !== '' ? $inputSlug : ($title !== '' ? $title : (string) ($existing['slug'] ?? 'blog-post'));

            $updated = array_merge($existing, [
                'title' => $title,
                'slug' => $this->postService()->generateUniquePostSlug($slugSource, $postId),
                'excerpt' => trim((string) $request->post('excerpt', (string) ($existing['excerpt'] ?? ''))),
                'body_long' => trim((string) $request->post('body_long', (string) ($existing['body_long'] ?? ''))),
                'meta_summary' => trim((string) $request->post('meta_summary', (string) ($existing['meta_summary'] ?? ''))),
            ]);

            $updated['reading_time'] = $this->postService()->calculateReadingTime((string) ($updated['body_long'] ?? ''));

            if ($this->postService()->hasMeaningfulRevisionChange($existing, $updated)) {
                (new BlogRevisionService(new \App\Models\BlogPostRevision(app_database())))->createSnapshot($postId, $existing, app_auth()->id());
            }

            $postModel->update($postId, $updated);

            return $this->jsonSuccess($response, 'Draft autosaved.', [
                'slug' => (string) ($updated['slug'] ?? ''),
                'reading_time' => $updated['reading_time'],
                'saved_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (RuntimeException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function attachMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $postId = (int) $request->post('post_id', 0);
            $mediaId = (int) $request->post('media_id', 0);
            $caption = trim((string) $request->post('caption', ''));

            $this->assertPostExists($postId);
            $this->assertMediaExists($mediaId);
            $this->publishingService()->attachMedia($postId, $mediaId, $caption);

            return $this->jsonSuccess($response, 'Blog media attached successfully.', ['html' => $this->renderMediaList($postId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function updateMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $postId = (int) $request->post('post_id', 0);
            $mediaId = (int) $request->post('media_id', 0);
            $caption = trim((string) $request->post('caption', ''));

            $this->assertPostExists($postId);
            $this->assertMediaExists($mediaId);
            $this->publishingService()->updateMedia($postId, $mediaId, $caption);

            return $this->jsonSuccess($response, 'Blog media updated successfully.', ['html' => $this->renderMediaList($postId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function reorderMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $postId = (int) $request->post('post_id', 0);
            $mediaIds = $request->post('media_ids', []);
            $ordered = is_array($mediaIds) ? array_map(static fn (mixed $value): int => (int) $value, $mediaIds) : [];

            $this->assertPostExists($postId);
            $this->publishingService()->reorderMedia($postId, $ordered);

            return $this->jsonSuccess($response, 'Blog media reordered successfully.', ['html' => $this->renderMediaList($postId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function removeMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $postId = (int) $request->post('post_id', 0);
            $mediaId = (int) $request->post('media_id', 0);

            $this->assertPostExists($postId);
            $this->publishingService()->removeMedia($postId, $mediaId);

            return $this->jsonSuccess($response, 'Blog media removed successfully.', ['html' => $this->renderMediaList($postId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed>|null $post
     * @param array<string, mixed> $values
     * @param array<string, mixed> $seoValues
     */
    private function renderEditor(?array $post, array $values, array $seoValues, string $mode): string
    {
        $old = app_session()->getFlash('old_input', []);
        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
            if (isset($old['category_ids']) && is_array($old['category_ids'])) {
                $values['category_ids'] = $old['category_ids'];
            }
            if (isset($old['tag_ids']) && is_array($old['tag_ids'])) {
                $values['tag_ids'] = $old['tag_ids'];
            }
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

        $postId = $post !== null ? (int) ($post['id'] ?? 0) : 0;
        $view = new View(dirname(__DIR__, 3));
        return $view->render('admin/blog/posts/form', [
            'title' => $mode === 'create' ? 'Create Blog Post' : 'Edit Blog Post',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Blog Posts', 'href' => $this->adminPath() . '/blog/posts'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'mode' => $mode,
            'post' => $post,
            'values' => $values,
            'seoValues' => $seoValues,
            'authors' => (new User(app_database()))->allActive(),
            'categories' => (new BlogCategory(app_database()))->all(),
            'tags' => (new BlogTag(app_database()))->all(),
            'galleries' => (new Gallery(app_database()))->searchableOptions(),
            'postMediaHtml' => $postId > 0 ? $this->renderMediaList($postId) : '',
            'revisions' => $postId > 0 ? (new BlogPost(app_database()))->revisions($postId) : [],
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin');
    }

    /**
     * @param array<string, mixed>|null $existing
     * @return array{post: array<string, mixed>, seo: array<string, mixed>, category_ids: array<int, int>, tag_ids: array<int, int>}
     */
    private function payloadFromRequest(Request $request, ?int $postId, ?array $existing): array
    {
        $publishAction = trim((string) $request->post('publish_action', ''));
        $selectedStatus = trim((string) $request->post('status', 'draft'));
        $status = $this->publishingService()->resolveStatusFromAction($selectedStatus, $publishAction);
        $scheduledAtInput = trim((string) $request->post('scheduled_at', ''));
        $publishedAtInput = trim((string) $request->post('published_at', ''));
        $inputSlug = trim((string) $request->post('slug', ''));
        $slugSource = $inputSlug !== '' ? $inputSlug : trim((string) $request->post('title', 'blog-post'));

        $rawCategoryIds = $request->post('category_ids', []);
        $categoryIds = is_array($rawCategoryIds) ? $rawCategoryIds : [$rawCategoryIds];
        $normalizedCategoryIds = array_values(array_unique(array_filter(array_map(static fn (mixed $value): int => ctype_digit((string) $value) ? (int) $value : 0, $categoryIds))));

        $rawTagIds = $request->post('tag_ids', []);
        $tagIds = is_array($rawTagIds) ? $rawTagIds : [$rawTagIds];
        $normalizedTagIds = array_values(array_unique(array_filter(array_map(static fn (mixed $value): int => ctype_digit((string) $value) ? (int) $value : 0, $tagIds))));

        $post = [
            'author_id' => $this->nullableInt($request->post('author_id', '')) ?? (app_auth()->id() ?? 0),
            'title' => trim((string) $request->post('title', '')),
            'slug' => $this->postService()->generateUniquePostSlug($slugSource, $postId),
            'excerpt' => trim((string) $request->post('excerpt', '')),
            'body_long' => trim((string) $request->post('body_long', '')),
            'featured_image_id' => $this->nullableInt($request->post('featured_image_id', '')),
            'cover_gallery_id' => $this->nullableInt($request->post('cover_gallery_id', '')),
            'status' => $status,
            'visibility' => trim((string) $request->post('visibility', 'public')),
            'is_featured' => $request->post('is_featured', '0') === '1' ? 1 : 0,
            'allow_comments' => $request->post('allow_comments', '0') === '1' ? 1 : 0,
            'published_at' => $this->publishingService()->normalizePublishedAt($status, $publishedAtInput, $scheduledAtInput),
            'scheduled_at' => $this->publishingService()->normalizeScheduledAt($status, $scheduledAtInput),
            'archived_at' => $this->publishingService()->normalizeArchivedAt($status, is_string($existing['archived_at'] ?? null) ? (string) $existing['archived_at'] : null),
            'reading_time' => $this->postService()->calculateReadingTime(trim((string) $request->post('body_long', ''))),
            'meta_summary' => trim((string) $request->post('meta_summary', '')),
            'canonical_url' => trim((string) $request->post('canonical_url', '')),
            'view_count' => (int) ($existing['view_count'] ?? 0),
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

        return [
            'post' => $post,
            'seo' => $seo,
            'category_ids' => $normalizedCategoryIds,
            'tag_ids' => $normalizedTagIds,
        ];
    }

    /**
     * @param array{post: array<string, mixed>, seo: array<string, mixed>, category_ids: array<int, int>, tag_ids: array<int, int>} $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new BlogPostValidator();
        $input = array_merge($payload['post'], [
            'author_id' => (string) ($payload['post']['author_id'] ?? ''),
            'featured_image_id' => $payload['post']['featured_image_id'] !== null ? (string) $payload['post']['featured_image_id'] : '',
            'cover_gallery_id' => $payload['post']['cover_gallery_id'] !== null ? (string) $payload['post']['cover_gallery_id'] : '',
            'is_featured' => (string) ($payload['post']['is_featured'] ?? 0),
            'allow_comments' => (string) ($payload['post']['allow_comments'] ?? 0),
            'reading_time' => $payload['post']['reading_time'] !== null ? (string) $payload['post']['reading_time'] : '',
            'og_image_media_id' => (string) ($payload['seo']['og_image'] ?? ''),
            'robots_index' => (string) ($payload['seo']['robots_index'] ?? 1),
            'robots_follow' => (string) ($payload['seo']['robots_follow'] ?? 1),
        ], $payload['seo']);

        if (! $validator->validate($input)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Blog post validation failed.');
        }

        if (! is_array((new User(app_database()))->findById((int) ($payload['post']['author_id'] ?? 0)))) {
            throw new RuntimeException('Selected author was not found.');
        }

        if (($payload['post']['featured_image_id'] ?? null) !== null) {
            $this->assertMediaExists((int) $payload['post']['featured_image_id']);
        }

        if (($payload['post']['cover_gallery_id'] ?? null) !== null && ! is_array((new Gallery(app_database()))->findById((int) $payload['post']['cover_gallery_id']))) {
            throw new RuntimeException('Selected cover gallery was not found.');
        }

        if (($payload['seo']['og_image'] ?? '') !== '') {
            $this->assertMediaExists((int) $payload['seo']['og_image']);
        }

        if ($payload['category_ids'] === []) {
            throw new RuntimeException('Select at least one blog category.');
        }

        foreach ($payload['category_ids'] as $categoryId) {
            if (! is_array((new BlogCategory(app_database()))->findById($categoryId))) {
                throw new RuntimeException('One or more selected categories were not found.');
            }
        }

        foreach ($payload['tag_ids'] as $tagId) {
            if (! is_array((new BlogTag(app_database()))->findById($tagId))) {
                throw new RuntimeException('One or more selected tags were not found.');
            }
        }

        if (($payload['post']['status'] ?? '') === 'scheduled' && ($payload['post']['scheduled_at'] ?? null) === null) {
            throw new RuntimeException('Scheduled posts require a valid schedule date and time.');
        }
    }

    private function renderMediaList(int $postId): string
    {
        $items = (new BlogPostMedia(app_database()))->detailedByPostId($postId);
        foreach ($items as &$item) {
            $directory = trim((string) ($item['directory'] ?? ''), '/');
            $storedName = (string) ($item['stored_name'] ?? '');
            $item['url'] = app_media_url($directory, $storedName);
            $item['media_id'] = (int) ($item['media_id'] ?? $item['id'] ?? 0);
        }
        unset($item);

        $view = new View(dirname(__DIR__, 3));
        return $view->partial('admin/blog/posts/media-list', [
            'items' => $items,
            'postId' => $postId,
        ]);
    }

    private function assertPostExists(int $postId): void
    {
        if ($postId <= 0 || ! is_array((new BlogPost(app_database()))->findById($postId))) {
            throw new RuntimeException('Blog post not found.');
        }
    }

    private function assertMediaExists(int $mediaId): void
    {
        if ($mediaId <= 0 || ! is_array((new MediaRepository(app_database()))->findById($mediaId))) {
            throw new RuntimeException('Media item not found.');
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

    private function postService(): BlogPostService
    {
        return new BlogPostService(new BlogRepository(app_database()), new BlogPost(app_database()));
    }

    private function publishingService(): BlogPublishingService
    {
        return new BlogPublishingService(
            new BlogPost(app_database()),
            new BlogPostCategory(app_database()),
            new BlogPostTag(app_database()),
            new BlogPostMedia(app_database()),
            new SeoMeta(app_database()),
            new BlogRevisionService(new \App\Models\BlogPostRevision(app_database())),
            new MediaUsageService(new \App\Models\MediaUsageMap(app_database())),
            $this->postService(),
        );
    }

    private function adminPath(): string
    {
        return admin_url();
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed>|null $existing
     */
    private function logPostMutation(Request $request, string $event, int $postId, array $post, ?array $existing = null): void
    {
        $status = (string) ($post['status'] ?? 'draft');
        $action = $status === 'published' && (string) ($existing['status'] ?? '') !== 'published'
            ? 'blog_post.published'
            : 'blog_post.' . $event;

        app_security_logger()->log($action, $request, 'blog_post', $postId, 'Blog post record updated.', [
            'title' => (string) ($post['title'] ?? ''),
            'slug' => (string) ($post['slug'] ?? ''),
            'status' => $status,
            'previous_status' => (string) ($existing['status'] ?? ''),
        ]);
    }
}
