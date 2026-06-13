<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Media;
use App\Models\SeoMeta;

class SeoService
{
    public function __construct(
        private readonly Database $database,
    ) {
    }

    /**
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    public function forPage(array $page): array
    {
        $pageId = (int) ($page['id'] ?? 0);
        $meta = $this->metaFor('page', $pageId);

        return $this->compose([
            'title' => (string) ($page['title'] ?? ''),
            'description' => (string) (($page['excerpt'] ?? '') !== '' ? ($page['excerpt'] ?? '') : ($page['body'] ?? '')),
            'canonical_path' => '/' . trim((string) ($page['slug'] ?? ''), '/'),
            'meta' => $meta,
            'fallback_image_id' => $this->nullableInt($page['featured_media_id'] ?? null),
            'og_type' => 'website',
        ]);
    }

    /**
     * @param array<string, mixed> $gallery
     * @return array<string, mixed>
     */
    public function forGallery(array $gallery): array
    {
        $galleryId = (int) ($gallery['id'] ?? 0);
        $meta = $this->metaFor('gallery', $galleryId);

        return $this->compose([
            'title' => (string) ($gallery['title'] ?? ''),
            'description' => (string) (($gallery['excerpt'] ?? '') !== '' ? ($gallery['excerpt'] ?? '') : ($gallery['story_intro'] ?? '')),
            'canonical_path' => '/portfolio/' . trim((string) ($gallery['slug'] ?? ''), '/'),
            'meta' => $meta,
            'fallback_image_id' => $this->nullableInt($gallery['cover_media_id'] ?? null),
            'fallback_image_url' => $this->mediaUrl((string) ($gallery['cover_directory'] ?? ''), (string) ($gallery['cover_stored_name'] ?? '')),
            'og_type' => 'article',
        ]);
    }

    /**
     * @param array<string, mixed> $service
     * @return array<string, mixed>
     */
    public function forService(array $service): array
    {
        $serviceId = (int) ($service['id'] ?? 0);
        $meta = $this->metaFor('service', $serviceId);

        return $this->compose([
            'title' => (string) ($service['title'] ?? ''),
            'description' => (string) (($service['short_description'] ?? '') !== '' ? ($service['short_description'] ?? '') : ($service['full_description'] ?? '')),
            'canonical_path' => '/services/' . trim((string) ($service['slug'] ?? ''), '/'),
            'meta' => $meta,
            'fallback_image_id' => $this->nullableInt($service['cover_media_id'] ?? null),
            'fallback_image_url' => $this->mediaUrl((string) ($service['cover_directory'] ?? ''), (string) ($service['cover_stored_name'] ?? '')),
            'og_type' => 'website',
        ]);
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public function forBlogPost(array $post): array
    {
        $postId = (int) ($post['id'] ?? 0);
        $meta = $this->metaFor('blog_post', $postId);

        return $this->compose([
            'title' => (string) ($post['title'] ?? ''),
            'description' => (string) (($post['meta_summary'] ?? '') !== '' ? ($post['meta_summary'] ?? '') : (($post['excerpt'] ?? '') !== '' ? ($post['excerpt'] ?? '') : ($post['body_long'] ?? ''))),
            'canonical_path' => '/blog/' . trim((string) ($post['slug'] ?? ''), '/'),
            'meta' => $meta,
            'fallback_image_id' => $this->nullableInt($post['featured_image_id'] ?? null),
            'fallback_image_url' => $this->mediaUrl((string) ($post['featured_image_directory'] ?? ''), (string) ($post['featured_image_stored_name'] ?? '')),
            'og_type' => 'article',
        ]);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function forArchive(array $context): array
    {
        return $this->compose([
            'title' => (string) ($context['title'] ?? ''),
            'description' => (string) ($context['description'] ?? ''),
            'canonical_path' => (string) ($context['canonical_path'] ?? '/'),
            'canonical_query' => isset($context['canonical_query']) && is_array($context['canonical_query']) ? $context['canonical_query'] : [],
            'meta' => isset($context['meta']) && is_array($context['meta']) ? $context['meta'] : [],
            'robots_index' => array_key_exists('robots_index', $context) ? (bool) $context['robots_index'] : null,
            'robots_follow' => array_key_exists('robots_follow', $context) ? (bool) $context['robots_follow'] : null,
            'fallback_image_id' => $this->nullableInt($context['fallback_image_id'] ?? null),
            'fallback_image_url' => (string) ($context['fallback_image_url'] ?? ''),
            'og_type' => (string) ($context['og_type'] ?? 'website'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        $siteName = $this->siteName();
        $robotsIndex = $this->boolSetting('seo', 'default_robots_index', true);

        return [
            'site_name' => $siteName,
            'default_description' => $this->defaultDescription(),
            'default_robots_index' => $robotsIndex,
            'default_robots_follow' => true,
            'default_og_image' => $this->defaultOgImage(),
            'structured_data' => [],
            'legacy_paths' => [],
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function compose(array $input): array
    {
        $meta = isset($input['meta']) && is_array($input['meta']) ? $input['meta'] : [];
        $rawTitle = trim((string) ($input['title'] ?? ''));
        $siteName = $this->siteName();
        $description = $this->summarize((string) ($meta['meta_description'] ?? ($input['description'] ?? $this->defaultDescription())));
        $metaTitle = trim((string) ($meta['meta_title'] ?? ''));
        $canonicalUrl = trim((string) ($meta['canonical_url'] ?? ''));

        if ($canonicalUrl === '') {
            $canonicalUrl = $this->absoluteUrl((string) ($input['canonical_path'] ?? '/'), isset($input['canonical_query']) && is_array($input['canonical_query']) ? $input['canonical_query'] : []);
        }

        $robotsIndex = array_key_exists('robots_index', $input) && $input['robots_index'] !== null
            ? (bool) $input['robots_index']
            : ((int) ($meta['robots_index'] ?? ($this->boolSetting('seo', 'default_robots_index', true) ? 1 : 0)) === 1);
        $robotsFollow = array_key_exists('robots_follow', $input) && $input['robots_follow'] !== null
            ? (bool) $input['robots_follow']
            : ((int) ($meta['robots_follow'] ?? 1) === 1);
        $ogTitle = trim((string) ($meta['og_title'] ?? ''));
        $ogDescription = $this->summarize((string) ($meta['og_description'] ?? $description));
        $ogImage = trim((string) ($meta['og_image'] ?? ''));

        if ($metaTitle === '') {
            $metaTitle = $this->applyTitlePattern($rawTitle !== '' ? $rawTitle : $siteName, $siteName);
        }

        if ($ogTitle === '') {
            $ogTitle = $metaTitle;
        }

        if ($ogImage === '') {
            $ogImage = $this->resolveFallbackImage($input);
        } elseif (ctype_digit($ogImage)) {
            $ogImage = $this->mediaUrlById((int) $ogImage);
        }

        return [
            'site_name' => $siteName,
            'title' => $metaTitle,
            'meta_title' => $metaTitle,
            'meta_description' => $description,
            'canonical_url' => $canonicalUrl,
            'robots_index' => $robotsIndex,
            'robots_follow' => $robotsFollow,
            'robots_content' => ($robotsIndex ? 'index' : 'noindex') . ',' . ($robotsFollow ? 'follow' : 'nofollow'),
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_type' => (string) ($input['og_type'] ?? 'website'),
            'structured_data' => [],
            'legacy_paths' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metaFor(string $entityType, int $entityId): array
    {
        if ($entityId <= 0) {
            return [];
        }

        return (new SeoMeta($this->database))->findFor($entityType, $entityId) ?? [];
    }

    private function applyTitlePattern(string $title, string $siteName): string
    {
        $pattern = trim((string) app_setting('seo', 'default_meta_title_pattern', '{{title}} | {{site_name}}'));
        if ($pattern === '') {
            $pattern = '{{title}} | {{site_name}}';
        }

        $resolved = strtr($pattern, [
            '{{title}}' => $title,
            '{{site_name}}' => $siteName,
        ]);

        return trim($resolved) !== '' ? trim($resolved) : $title;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function absoluteUrl(string $path, array $query = []): string
    {
        $url = base_url(trim($path, '/') === '' ? '' : ltrim($path, '/'));

        $query = array_filter($query, static fn (mixed $value): bool => $value !== null && $value !== '');
        if ($query === []) {
            return $url;
        }

        return $url . '?' . http_build_query($query);
    }

    private function summarize(string $value): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');
        if ($clean === '') {
            return $this->defaultDescription();
        }

        return mb_substr($clean, 0, 320);
    }

    private function defaultDescription(): string
    {
        $description = trim((string) app_setting('seo', 'default_meta_description', ''));
        if ($description !== '') {
            return $description;
        }

        $tagline = trim((string) app_setting('general', 'tagline', ''));
        if ($tagline !== '') {
            return $tagline;
        }

        return (string) config('app.name', 'Mesh Photography');
    }

    private function siteName(): string
    {
        $brandingTitle = trim((string) app_setting('branding', 'brand_site_title', ''));
        if ($brandingTitle !== '') {
            return $brandingTitle;
        }

        $siteName = trim((string) app_setting('general', 'site_name', ''));
        return $siteName !== '' ? $siteName : (string) config('app.name', 'Mesh Photography');
    }

    private function defaultOgImage(): string
    {
        return $this->mediaUrlById((int) app_setting('seo', 'default_og_image_media_id', 0));
    }

    /**
     * @param array<string, mixed> $input
     */
    private function resolveFallbackImage(array $input): string
    {
        $fallbackImageId = $this->nullableInt($input['fallback_image_id'] ?? null);
        if ($fallbackImageId !== null) {
            $url = $this->mediaUrlById($fallbackImageId);
            if ($url !== '') {
                return $url;
            }
        }

        $fallbackImageUrl = trim((string) ($input['fallback_image_url'] ?? ''));
        if ($fallbackImageUrl !== '') {
            return $fallbackImageUrl;
        }

        return $this->defaultOgImage();
    }

    private function mediaUrlById(int $mediaId): string
    {
        if ($mediaId <= 0) {
            return '';
        }

        $media = (new Media($this->database))->findById($mediaId);
        if (! is_array($media)) {
            return '';
        }

        return $this->mediaUrl((string) ($media['directory'] ?? ''), (string) ($media['stored_name'] ?? ''));
    }

    private function mediaUrl(string $directory, string $storedName): string
    {
        return app_media_url($directory, $storedName);
    }

    private function boolSetting(string $group, string $key, bool $default): bool
    {
        $value = app_setting($group, $key, $default ? '1' : '0');
        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        return $stringValue !== '' && ctype_digit($stringValue) ? (int) $stringValue : null;
    }
}
