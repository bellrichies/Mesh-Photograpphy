<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\HeroSlide;
use App\Models\SeoMeta;
use App\Models\Service;
use App\Models\Testimonial;
use App\Repositories\BlogRepository;
use App\Repositories\GalleryRepository;
use App\Services\PageSectionService;
use App\Services\SeoService;

class HomeController
{
    public function index(Request $request, Response $response): Response
    {
        $page = $this->homepage();
        $sectionService = new PageSectionService(new PageSection(app_database()));
        $sections = $this->sections((int) $page['id']);
        $seo = $this->seo()->forPage($page);
        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('web/home', [
            'title' => (string) ($seo['meta_title'] ?? ($page['title'] ?? config('app.name', 'Mesh Photograph'))),
            'page' => $page,
            'sections' => $sections,
            'sectionService' => $sectionService,
            'homeContent' => $this->homeContent(),
            'seo' => $seo,
        ], 'layouts/main'));
    }

    /**
     * @return array<string, mixed>
     */
    private function homepage(): array
    {
        $page = (new Page(app_database()))->findBySlug('home');

        if (! is_array($page) || (string) ($page['status'] ?? 'draft') !== 'published') {
            throw new HttpException(404, 'Homepage is not configured.');
        }

        return $page;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sections(int $pageId): array
    {
        $rows = (new Page(app_database()))->sections($pageId);
        $mediaModel = new Media(app_database());
        $sections = [];

        foreach ($rows as $section) {
            if ((string) ($section['status'] ?? 'draft') !== 'published') {
                continue;
            }

            $payload = json_decode((string) ($section['json_payload'] ?? ''), true);
            $section['payload'] = is_array($payload) ? $payload : [];

            $mediaId = (int) ($section['media_id'] ?? 0);
            if ($mediaId > 0) {
                $media = $mediaModel->findById($mediaId);
                if (is_array($media)) {
                    $section['media_url'] = $this->mediaUrl((string) ($media['directory'] ?? ''), (string) ($media['stored_name'] ?? ''));
                    $section['media_alt'] = (string) (($media['alt_text'] ?? '') !== '' ? ($media['alt_text'] ?? '') : ($media['title'] ?? $section['title'] ?? ''));
                }
            }

            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * @return array<string, mixed>
     */
    private function homeContent(): array
    {
        $aboutPage = (new Page(app_database()))->findBySlug('about');
        if (! is_array($aboutPage) || (string) ($aboutPage['status'] ?? 'draft') !== 'published') {
            $aboutPage = null;
        }

        $heroSlides = $this->heroSlides();

        return [
            'siteName' => (string) app_setting('general', 'site_name', config('app.name', 'Mesh Photograph')),
            'tagline' => (string) app_setting('general', 'tagline', ''),
            'aboutPage' => $aboutPage,
            'heroSlides' => $heroSlides,
            'heroImages' => $heroSlides,
            'featuredGalleries' => $this->featuredGalleries(),
            'services' => $this->services(),
            'testimonials' => $this->testimonials(),
            'featuredPosts' => $this->featuredPosts(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function featuredGalleries(): array
    {
        $items = (new GalleryRepository(app_database()))->published(['featured' => 1], 6, 0);

        foreach ($items as &$item) {
            $item['cover_url'] = app_media_url((string) ($item['cover_directory'] ?? ''), (string) ($item['cover_stored_name'] ?? ''));
            $item['cover_card_url'] = app_media_preferred_url($item, 'cover');
        }
        unset($item);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function heroSlides(): array
    {
        $items = (new HeroSlide(app_database()))->allPublished();

        foreach ($items as &$item) {
            $imageUrl = $this->mediaUrl(
                (string) ($item['image_directory'] ?? ''),
                (string) ($item['image_stored_name'] ?? '')
            );

            $altText = trim((string) ($item['image_alt_text'] ?? ''));
            if ($altText === '') {
                $altText = trim((string) (($item['media_alt_text'] ?? '') !== '' ? ($item['media_alt_text'] ?? '') : ($item['media_title'] ?? $item['title'] ?? 'Hero slide image')));
            }

            $item['image_url'] = $imageUrl;
            $item['url'] = $imageUrl;
            $item['image_alt'] = $altText;
            $item['alt'] = $altText;
            $item['cta_label'] = (string) ($item['primary_cta_label'] ?? '');
            $item['cta_url'] = (string) ($item['primary_cta_url'] ?? '');
            $item['secondary_cta_label'] = (string) ($item['secondary_cta_label'] ?? '');
            $item['secondary_cta_url'] = (string) ($item['secondary_cta_url'] ?? '');
        }
        unset($item);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function services(): array
    {
        $items = (new Service(app_database()))->allPublished(6);

        foreach ($items as &$item) {
            $item['cover_url'] = $this->mediaUrl((string) ($item['cover_directory'] ?? ''), (string) ($item['cover_stored_name'] ?? ''));
        }
        unset($item);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function testimonials(): array
    {
        $model = new Testimonial(app_database());
        $items = $model->featuredPublished(6);
        if ($items === []) {
            $items = $model->allPublished(6);
        }

        foreach ($items as &$item) {
            $item['portrait_url'] = $this->mediaUrl((string) ($item['portrait_directory'] ?? ''), (string) ($item['portrait_stored_name'] ?? ''));
        }
        unset($item);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function featuredPosts(): array
    {
        $repository = new BlogRepository(app_database());
        $posts = $repository->publicIndex([], 4, 0);

        foreach ($posts as &$post) {
            $post['featured_image_url'] = $this->mediaUrl(
                (string) ($post['featured_image_directory'] ?? ''),
                (string) ($post['featured_image_stored_name'] ?? '')
            );
            $post['featured_image_card_url'] = app_media_preferred_url($post, 'featured_image');
            $post['categories'] = $this->categoryLinks((string) ($post['category_links'] ?? ''));
            $post['author_name'] = trim((string) ($post['author_first_name'] ?? '') . ' ' . (string) ($post['author_last_name'] ?? ''));
        }
        unset($post);

        return $posts;
    }

    /**
     * @return array<int, array{name: string, slug: string}>
     */
    private function categoryLinks(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $links = [];
        foreach (explode('||', $raw) as $chunk) {
            [$name, $slug] = array_pad(explode('::', $chunk, 2), 2, '');
            $name = trim($name);
            $slug = trim($slug);

            if ($name === '' || $slug === '') {
                continue;
            }

            $links[] = ['name' => $name, 'slug' => $slug];
        }

        return $links;
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
