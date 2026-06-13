<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\InteractsWithAdminJson;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryCategoryMap;
use App\Models\GalleryMedia;
use App\Models\MediaUsageMap;
use App\Repositories\GalleryRepository;
use App\Repositories\MediaRepository;
use App\Services\GalleryService;
use App\Services\MediaUsageService;
use App\Validators\GalleryValidator;
use RuntimeException;

class GalleryController
{
    use InteractsWithAdminJson;

    public function index(Request $request, Response $response): Response
    {
        $filters = [
            'query' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'category_id' => (int) $request->query('category_id', 0),
            'featured' => (string) $request->query('featured', ''),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $repository = new GalleryRepository(app_database());
        $items = $repository->adminIndex($filters, $limit, $offset);
        $total = $repository->countAdmin($filters);

        $view = new View(dirname(__DIR__, 3));
        return $response->html($view->render('admin/galleries/index', [
            'title' => 'Portfolio Galleries',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Portfolio Galleries', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'items' => $items,
            'filters' => [
                'q' => $filters['query'],
                'status' => $filters['status'],
                'category_id' => $filters['category_id'] > 0 ? (string) $filters['category_id'] : '',
                'featured' => $filters['featured'],
            ],
            'categories' => (new GalleryCategory(app_database()))->allForAdmin(),
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
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'story_intro' => '',
            'category_primary_id' => '',
            'cover_media_id' => '',
            'featured' => '0',
            'status' => 'draft',
            'location' => '',
            'event_date' => '',
            'client_name' => '',
            'sort_order' => (string) (new Gallery(app_database()))->nextSortOrder(),
            'published_at' => '',
            'category_ids' => [],
        ], 'create'));
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, null);
            $this->validatePayload($payload);

            $galleryId = $this->service()->createGallery($payload['gallery'], $payload['category_ids']);
            app_session()->flash('success', 'Gallery created successfully.');

            return $response->redirect($this->adminPath() . '/galleries/edit/' . $galleryId, 302);
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
            return $response->redirect($this->adminPath() . '/galleries/create', 302);
        }
    }

    public function edit(Request $request, Response $response, array $params): Response
    {
        $galleryId = (int) ($params['id'] ?? 0);
        $gallery = (new Gallery(app_database()))->findById($galleryId);
        if (! is_array($gallery)) {
            throw new HttpException(404, 'Gallery not found.');
        }

        $gallery['category_ids'] = array_map(
            static fn (array $row): string => (string) ($row['id'] ?? ''),
            (new Gallery(app_database()))->categories($galleryId)
        );

        return $response->html($this->renderForm($gallery, $gallery, 'edit'));
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $galleryId = (int) ($params['id'] ?? 0);
        $gallery = (new Gallery(app_database()))->findById($galleryId);
        if (! is_array($gallery)) {
            throw new HttpException(404, 'Gallery not found.');
        }

        try {
            $this->verifyCsrf($request);
            $payload = $this->payloadFromRequest($request, $galleryId);
            $this->validatePayload($payload);

            $this->service()->updateGallery($galleryId, $payload['gallery'], $payload['category_ids']);
            app_session()->flash('success', 'Gallery updated successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', $request->all());
        }

        return $response->redirect($this->adminPath() . '/galleries/edit/' . $galleryId, 302);
    }

    public function delete(Request $request, Response $response, array $params): Response
    {
        $galleryId = (int) ($params['id'] ?? 0);
        $gallery = (new Gallery(app_database()))->findById($galleryId);
        if (! is_array($gallery)) {
            throw new HttpException(404, 'Gallery not found.');
        }

        try {
            $this->verifyCsrf($request);
            $this->service()->deleteGallery($galleryId);
            app_session()->flash('success', 'Gallery archived successfully.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
        }

        return $response->redirect($this->adminPath() . '/galleries', 302);
    }

    public function slug(Request $request, Response $response): Response
    {
        $source = trim((string) $request->query('title', (string) $request->query('slug', '')));
        $galleryId = (int) $request->query('gallery_id', 0);
        $slug = $this->service()->generateUniqueSlug($source !== '' ? $source : 'gallery', $galleryId > 0 ? $galleryId : null);

        return $this->jsonSuccess($response, 'Slug generated successfully.', ['slug' => $slug]);
    }

    public function attachMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $galleryId = (int) $request->post('gallery_id', 0);
            $mediaId = (int) $request->post('media_id', 0);
            $caption = trim((string) $request->post('caption', ''));
            $isFeatured = (string) $request->post('is_featured', '0') === '1';

            $this->assertGalleryExists($galleryId);
            $this->assertMediaExists($mediaId);

            $this->service()->attachMedia($galleryId, $mediaId, $caption, $isFeatured);

            return $this->jsonSuccess($response, 'Gallery media attached successfully.', ['html' => $this->renderMediaList($galleryId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function updateMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $galleryId = (int) $request->post('gallery_id', 0);
            $mediaId = (int) $request->post('media_id', 0);
            $caption = trim((string) $request->post('caption', ''));
            $isFeatured = (string) $request->post('is_featured', '0') === '1';

            $this->assertGalleryExists($galleryId);
            $this->assertMediaExists($mediaId);
            $this->service()->updateGalleryMedia($galleryId, $mediaId, $caption, $isFeatured);

            return $this->jsonSuccess($response, 'Gallery media updated successfully.', ['html' => $this->renderMediaList($galleryId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function reorderMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $galleryId = (int) $request->post('gallery_id', 0);
            $mediaIds = $request->post('media_ids', []);
            $ordered = is_array($mediaIds) ? array_map(static fn (mixed $value): int => (int) $value, $mediaIds) : [];

            $this->assertGalleryExists($galleryId);
            $this->service()->reorderMedia($galleryId, $ordered);

            return $this->jsonSuccess($response, 'Gallery media reordered successfully.', ['html' => $this->renderMediaList($galleryId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    public function removeMedia(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);
            $galleryId = (int) $request->post('gallery_id', 0);
            $mediaId = (int) $request->post('media_id', 0);

            $this->assertGalleryExists($galleryId);
            $this->service()->removeMedia($galleryId, $mediaId);

            return $this->jsonSuccess($response, 'Gallery media removed successfully.', ['html' => $this->renderMediaList($galleryId)]);
        } catch (RuntimeException|HttpException $exception) {
            return $this->jsonError($response, $exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed>|null $gallery
     * @param array<string, mixed> $values
     */
    private function renderForm(?array $gallery, array $values, string $mode): string
    {
        $old = app_session()->getFlash('old_input', []);
        if (is_array($old) && $old !== []) {
            $values = array_merge($values, $old);
            if (isset($old['category_ids']) && is_array($old['category_ids'])) {
                $values['category_ids'] = $old['category_ids'];
            }
        }

        $galleryId = $gallery !== null ? (int) ($gallery['id'] ?? 0) : 0;
        $view = new View(dirname(__DIR__, 3));

        return $view->render('admin/galleries/form', [
            'title' => $mode === 'create' ? 'Create Gallery' : 'Edit Gallery',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Portfolio Galleries', 'href' => $this->adminPath() . '/galleries'],
                ['label' => $mode === 'create' ? 'Create' : 'Edit', 'href' => '#'],
            ],
            'adminPath' => $this->adminPath(),
            'mode' => $mode,
            'gallery' => $gallery,
            'values' => $values,
            'categories' => (new GalleryCategory(app_database()))->allForAdmin(),
            'galleryMediaHtml' => $galleryId > 0 ? $this->renderMediaList($galleryId) : '',
            'tokenKey' => (string) config('app.csrf_token_name', '_token'),
        ], 'layouts/admin');
    }

    /**
     * @return array{gallery: array<string, mixed>, category_ids: array<int, int>}
     */
    private function payloadFromRequest(Request $request, ?int $galleryId): array
    {
        $rawCategoryIds = $request->post('category_ids', []);
        $categoryIds = is_array($rawCategoryIds) ? $rawCategoryIds : [$rawCategoryIds];
        $normalizedCategoryIds = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): int => ctype_digit((string) $value) ? (int) $value : 0,
            $categoryIds
        ))));

        $service = $this->service();
        $inputSlug = trim((string) $request->post('slug', ''));
        $slugSource = $inputSlug !== '' ? $inputSlug : trim((string) $request->post('title', 'gallery'));
        $primaryCategoryId = $this->nullableInt($request->post('category_primary_id', ''));

        if ($primaryCategoryId !== null && ! in_array($primaryCategoryId, $normalizedCategoryIds, true)) {
            $normalizedCategoryIds[] = $primaryCategoryId;
        }

        if ($primaryCategoryId === null && $normalizedCategoryIds !== []) {
            $primaryCategoryId = (int) $normalizedCategoryIds[0];
        }

        return [
            'gallery' => [
                'title' => trim((string) $request->post('title', '')),
                'slug' => $service->generateUniqueSlug($slugSource, $galleryId),
                'excerpt' => trim((string) $request->post('excerpt', '')),
                'story_intro' => trim((string) $request->post('story_intro', '')),
                'category_primary_id' => $primaryCategoryId,
                'cover_media_id' => $this->nullableInt($request->post('cover_media_id', '')),
                'featured' => $request->post('featured', '0') === '1' ? 1 : 0,
                'status' => trim((string) $request->post('status', 'draft')),
                'location' => trim((string) $request->post('location', '')),
                'event_date' => $this->nullableDate($request->post('event_date', '')),
                'client_name' => trim((string) $request->post('client_name', '')),
                'sort_order' => (int) $request->post('sort_order', 0),
                'published_at' => $service->normalizePublishedAt(
                    trim((string) $request->post('status', 'draft')),
                    trim((string) $request->post('published_at', ''))
                ),
                'updated_by' => app_auth()->id(),
                'created_by' => app_auth()->id(),
            ],
            'category_ids' => array_map(static fn (int $value): int => $value, $normalizedCategoryIds),
        ];
    }

    /**
     * @param array{gallery: array<string, mixed>, category_ids: array<int, int>} $payload
     */
    private function validatePayload(array $payload): void
    {
        $validator = new GalleryValidator();
        $input = $payload['gallery'];
        $input['category_primary_id'] = $input['category_primary_id'] !== null ? (string) $input['category_primary_id'] : '';
        $input['cover_media_id'] = $input['cover_media_id'] !== null ? (string) $input['cover_media_id'] : '';
        $input['featured'] = (string) ($input['featured'] ?? 0);
        $input['sort_order'] = (string) ($input['sort_order'] ?? 0);

        if (! $validator->validate($input)) {
            foreach ($validator->errors() as $messages) {
                if (isset($messages[0])) {
                    throw new RuntimeException((string) $messages[0]);
                }
            }

            throw new RuntimeException('Gallery validation failed.');
        }

        if ($payload['category_ids'] === []) {
            throw new RuntimeException('Select at least one gallery category.');
        }

        if (($payload['gallery']['category_primary_id'] ?? null) === null) {
            throw new RuntimeException('Choose a primary gallery category.');
        }

        if (($payload['gallery']['cover_media_id'] ?? null) !== null) {
            $this->assertMediaExists((int) $payload['gallery']['cover_media_id']);
        }
    }

    private function renderMediaList(int $galleryId): string
    {
        $items = (new GalleryMedia(app_database()))->detailedByGalleryId($galleryId);
        foreach ($items as &$item) {
            $item['url'] = app_media_url((string) ($item['directory'] ?? ''), (string) ($item['stored_name'] ?? ''));
        }
        unset($item);

        $view = new View(dirname(__DIR__, 3));
        return $view->partial('admin/galleries/media-list', [
            'items' => $items,
            'galleryId' => $galleryId,
        ]);
    }

    private function assertGalleryExists(int $galleryId): void
    {
        if ($galleryId <= 0 || ! is_array((new Gallery(app_database()))->findById($galleryId))) {
            throw new RuntimeException('Gallery not found.');
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

    private function nullableDate(mixed $value): ?string
    {
        $stringValue = trim((string) $value);
        return $stringValue !== '' ? $stringValue : null;
    }

    private function adminPath(): string
    {
        return admin_url();
    }

    private function service(): GalleryService
    {
        return new GalleryService(
            new GalleryRepository(app_database()),
            new Gallery(app_database()),
            new GalleryCategoryMap(app_database()),
            new GalleryMedia(app_database()),
            new MediaUsageService(new MediaUsageMap(app_database()))
        );
    }
}
