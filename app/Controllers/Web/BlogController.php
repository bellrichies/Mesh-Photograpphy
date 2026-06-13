<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Page;
use App\Repositories\BlogRepository;
use App\Services\SeoService;

class BlogController
{
    public function index(Request $request, Response $response): Response
    {
        return $this->renderListing($request, $response, [
            'mode' => 'index',
            'headline' => 'Editorial stories, practical insight, and quiet authority.',
            'eyebrow' => 'Journal',
            'description' => 'The public journal pairs refined photography stories with planning advice, creative process notes, and brand-led editorial content from the CMS.',
        ]);
    }

    public function search(Request $request, Response $response): Response
    {
        $query = trim((string) $request->query('q', ''));

        return $this->renderListing($request, $response, [
            'mode' => 'search',
            'headline' => $query !== '' ? 'Search results for "' . $query . '"' : 'Search the journal',
            'eyebrow' => 'Search',
            'description' => $query !== ''
                ? 'Browse published articles matching the current topic, keyword, category, or tag.'
                : 'Use the search field to find published posts by topic, title, category, or keyword.',
            'query' => $query,
        ]);
    }

    public function category(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $category = (new BlogCategory(app_database()))->findBySlug($slug);

        if (! is_array($category)) {
            throw new HttpException(404, 'Blog category not found.');
        }

        return $this->renderListing($request, $response, [
            'mode' => 'category',
            'eyebrow' => 'Category Archive',
            'headline' => (string) ($category['name'] ?? 'Category'),
            'description' => (string) (($category['description'] ?? '') !== ''
                ? $category['description']
                : 'Published articles collected around a shared subject area.'),
            'category' => $category,
        ]);
    }

    public function tag(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $tag = (new BlogTag(app_database()))->findBySlug($slug);

        if (! is_array($tag)) {
            throw new HttpException(404, 'Blog tag not found.');
        }

        return $this->renderListing($request, $response, [
            'mode' => 'tag',
            'eyebrow' => 'Tag Archive',
            'headline' => '#' . (string) ($tag['name'] ?? 'Tag'),
            'description' => (string) (($tag['description'] ?? '') !== ''
                ? $tag['description']
                : 'Published articles connected by a shared editorial theme.'),
            'tag' => $tag,
        ]);
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        $repository = $this->repository();
        $post = $repository->findPublishedBySlug($slug);

        if (! is_array($post)) {
            throw new HttpException(404, 'Blog post not found.');
        }

        $post = $this->normalizePost($post);
        $postId = (int) ($post['id'] ?? 0);
        $session = app_session();
        $viewed = $session->get('blog_viewed_posts', []);
        $viewedIds = is_array($viewed) ? array_map('intval', $viewed) : [];

        if ($postId > 0 && ! in_array($postId, $viewedIds, true)) {
            (new BlogPost(app_database()))->incrementViewCount($postId);
            $viewedIds[] = $postId;
            $session->set('blog_viewed_posts', array_values(array_unique($viewedIds)));
            $post['view_count'] = (int) ($post['view_count'] ?? 0) + 1;
        }

        $postModel = new BlogPost(app_database());
        $seo = $this->seo()->forBlogPost($post);
        $supportingMedia = array_map(fn (array $item): array => $this->normalizeMedia($item), $postModel->media($postId));
        $relatedPosts = array_map(fn (array $item): array => $this->normalizePost($item), $repository->relatedPosts($postId, 3));

        if ($relatedPosts === []) {
            $relatedPosts = array_map(fn (array $item): array => $this->normalizePost($item), $repository->recentPosts(3, [$postId]));
        }

        $recentPosts = array_map(fn (array $item): array => $this->normalizePost($item), $repository->recentPosts(4, [$postId]));

        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('web/blog/show', [
            'title' => (string) ($seo['meta_title'] ?? ($post['title'] ?? config('app.name', 'Mesh Photography'))),
            'post' => $post,
            'seo' => $seo,
            'supportingMedia' => $supportingMedia,
            'relatedPosts' => $relatedPosts,
            'recentPosts' => $recentPosts,
        ], 'layouts/main'));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderListing(Request $request, Response $response, array $context): Response
    {
        $query = isset($context['query']) ? trim((string) $context['query']) : trim((string) $request->query('q', ''));
        $archiveMonth = trim((string) $request->query('archive', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 8;
        $repository = $this->repository();
        $contentPage = $this->page('blog');

        $filters = [];
        if ($query !== '') {
            $filters['query'] = $query;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $archiveMonth) === 1) {
            $filters['archive_month'] = $archiveMonth;
        } else {
            $archiveMonth = '';
        }

        $activeCategory = isset($context['category']) && is_array($context['category']) ? $context['category'] : null;
        $activeTag = isset($context['tag']) && is_array($context['tag']) ? $context['tag'] : null;
        $listingBasePath = '/blog';

        if ($activeCategory !== null) {
            $filters['category_id'] = (int) ($activeCategory['id'] ?? 0);
            $listingBasePath = '/blog/category/' . rawurlencode((string) ($activeCategory['slug'] ?? ''));
        }

        if ($activeTag !== null) {
            $filters['tag_id'] = (int) ($activeTag['id'] ?? 0);
            $listingBasePath = '/blog/tag/' . rawurlencode((string) ($activeTag['slug'] ?? ''));
        }

        if (($context['mode'] ?? 'index') === 'search') {
            $listingBasePath = '/blog/search';
        }

        $featuredPost = null;
        $shouldShowFeatured = ($context['mode'] ?? 'index') === 'index' && $query === '' && $archiveMonth === '' && $page === 1;
        if ($shouldShowFeatured) {
            $featuredPost = $repository->featuredPost($filters);
            if (is_array($featuredPost)) {
                $featuredPost = $this->normalizePost($featuredPost);
                $filters['exclude_id'] = (int) ($featuredPost['id'] ?? 0);
            }
        }

        $total = $repository->countPublic($filters);
        $posts = array_map(
            fn (array $item): array => $this->normalizePost($item),
            $repository->publicIndex($filters, $limit, ($page - 1) * $limit)
        );

        $archives = $repository->archiveCounts();
        $recentPosts = array_map(fn (array $item): array => $this->normalizePost($item), $repository->recentPosts(5));
        $categories = $repository->categoryCounts();
        $tags = array_values(array_filter($repository->tagCounts(), static fn (array $item): bool => (int) ($item['post_count'] ?? 0) > 0));
        $activeArchive = null;

        foreach ($archives as $archive) {
            if ((string) ($archive['archive_month'] ?? '') === $archiveMonth) {
                $activeArchive = $archive;
                break;
            }
        }

        $title = match ($context['mode'] ?? 'index') {
            'search' => $query !== '' ? 'Search: ' . $query : 'Search Blog',
            'category' => (string) ($activeCategory['name'] ?? 'Blog Category'),
            'tag' => '#' . (string) ($activeTag['name'] ?? 'Blog Tag'),
            default => (string) ($contentPage['title'] ?? 'Blog'),
        };

        if ($activeArchive !== null) {
            $title = 'Archive: ' . (string) ($activeArchive['archive_label'] ?? 'Blog Archive');
        }

        $seo = (($context['mode'] ?? 'index') === 'index' && $query === '' && $archiveMonth === '' && $contentPage !== null)
            ? $this->seo()->forPage($contentPage)
            : $this->seo()->forArchive([
                'title' => $title,
                'description' => (string) (($context['description'] ?? '') !== '' ? ($context['description'] ?? '') : ($contentPage['excerpt'] ?? '')),
                'canonical_path' => $listingBasePath,
                'canonical_query' => ($context['mode'] ?? 'index') === 'search' && $query !== '' ? ['q' => $query] : ($archiveMonth !== '' ? ['archive' => $archiveMonth] : []),
                'robots_index' => ($context['mode'] ?? 'index') !== 'search',
                'robots_follow' => true,
            ]);

        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('web/blog/index', [
            'title' => (string) ($seo['meta_title'] ?? $title),
            'headline' => (string) (($context['headline'] ?? '') !== '' ? ($context['headline'] ?? '') : ($contentPage['title'] ?? 'Blog')),
            'eyebrow' => (string) ($context['eyebrow'] ?? 'Journal'),
            'description' => (string) (($context['description'] ?? '') !== '' ? ($context['description'] ?? '') : ($contentPage['excerpt'] ?? '')),
            'mode' => (string) ($context['mode'] ?? 'index'),
            'listingBasePath' => $listingBasePath,
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'query' => $query,
            'archives' => $archives,
            'activeArchive' => $activeArchive,
            'recentPosts' => $recentPosts,
            'categories' => $categories,
            'tags' => $tags,
            'publishedTotal' => $repository->countPublic(),
            'activeCategory' => $activeCategory,
            'activeTag' => $activeTag,
            'page' => $contentPage,
            'seo' => $seo,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) max(1, ceil($total / $limit)),
            ],
        ], 'layouts/main'));
    }

    private function repository(): BlogRepository
    {
        return new BlogRepository(app_database());
    }

    private function page(string $slug): ?array
    {
        return (new Page(app_database()))->findPublishedBySlug($slug);
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizePost(array $post): array
    {
        $post['featured_image_url'] = $this->mediaUrl(
            (string) ($post['featured_image_directory'] ?? ''),
            (string) ($post['featured_image_stored_name'] ?? '')
        );
        $post['featured_image_card_url'] = app_media_preferred_url($post, 'featured_image');
        $post['author_name'] = trim((string) (($post['author_first_name'] ?? '') . ' ' . ($post['author_last_name'] ?? '')));
        $post['categories'] = $this->parseTaxonomyLinks((string) ($post['category_links'] ?? ''));
        $post['tags'] = $this->parseTaxonomyLinks((string) ($post['tag_links'] ?? ''));
        $post['summary'] = (string) (($post['excerpt'] ?? '') !== '' ? ($post['excerpt'] ?? '') : ($post['meta_summary'] ?? ''));
        $post['published_label'] = $this->formatPublishedLabel($post);
        $post['reading_time_label'] = $this->readingTimeLabel($post);

        return $post;
    }

    /**
     * @param array<string, mixed> $media
     * @return array<string, mixed>
     */
    private function normalizeMedia(array $media): array
    {
        $media['url'] = $this->mediaUrl((string) ($media['directory'] ?? ''), (string) ($media['stored_name'] ?? ''));

        return $media;
    }

    /**
     * @return array<int, array{name: string, slug: string}>
     */
    private function parseTaxonomyLinks(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $items = [];
        foreach (explode('||', $raw) as $entry) {
            if ($entry === '') {
                continue;
            }

            [$name, $slug] = array_pad(explode('::', $entry, 2), 2, '');
            if ($name === '' || $slug === '') {
                continue;
            }

            $items[] = [
                'name' => $name,
                'slug' => $slug,
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $post
     */
    private function formatPublishedLabel(array $post): string
    {
        $timestamp = strtotime((string) (($post['published_at'] ?? '') !== '' ? ($post['published_at'] ?? '') : ($post['created_at'] ?? '')));
        if ($timestamp === false) {
            return '';
        }

        return date('F j, Y', $timestamp);
    }

    /**
     * @param array<string, mixed> $post
     */
    private function readingTimeLabel(array $post): string
    {
        $minutes = (int) ($post['reading_time'] ?? 0);
        if ($minutes <= 0) {
            $body = trim(strip_tags((string) ($post['body_long'] ?? '')));
            $minutes = max(1, (int) ceil(str_word_count($body) / 220));
        }

        return $minutes . ' min read';
    }

    private function mediaUrl(string $directory, string $storedName): string
    {
        return app_media_url($directory, $storedName);
    }

    private function seo(): SeoService
    {
        return new SeoService(app_database());
    }
}
