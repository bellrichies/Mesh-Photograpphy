<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use Database\Seeders\AboutPageSeeder;
use Database\Seeders\ContactPageSeeder;
use Database\Seeders\Support\PlaceholderMediaWriter;
use Database\Seeders\SettingsSeeder;
use Throwable;

class DemoContentSeeder
{
    private DemoContentFactory $factory;

    public function __construct(?DemoContentFactory $factory = null)
    {
        $this->factory = $factory ?? new DemoContentFactory();
    }

    public function run(Database $database): void
    {
        (new RolePermissionSeeder())->run($database);
        (new CoreCmsSeeder())->run($database);
        (new MediaSampleSeeder())->run($database);

        $connection = $database->connection();
        $connection->beginTransaction();

        try {
            $userIds = $this->seedUsers($database);
            $this->syncRolePermissions($database);
            $this->syncUserRoles($database, $userIds);

            $mediaIds = $this->seedMedia($database, $userIds);
            $this->materializeDemoMediaAssets();
            (new SettingsSeeder($this->factory))->run($database);
            $pageIds = $this->seedPages($database, $userIds, $mediaIds);
            $this->seedHomeSections($database, $pageIds, $mediaIds);
            (new AboutPageSeeder($this->factory))->run($database);
            $this->seedReusableBlocks($database);
            (new ContactPageSeeder($this->factory))->run($database);
            (new HeroSlideSeeder($this->factory))->run($database);
            $serviceIds = $this->seedServices($database, $mediaIds);
            $categoryIds = $this->seedGalleryCategories($database);
            $galleryIds = $this->seedGalleries($database, $userIds, $mediaIds, $categoryIds);
            $this->seedTestimonials($database, $serviceIds, $galleryIds, $mediaIds);
            $blogCategoryIds = $this->seedBlogCategories($database);
            $blogTagIds = $this->seedBlogTags($database);
            $this->seedBlogPosts($database, $userIds, $mediaIds, $galleryIds, $blogCategoryIds, $blogTagIds);
            $inquiryIds = $this->seedInquiries($database, $userIds);
            $this->seedBookingRequests($database, $inquiryIds, $serviceIds);

            $connection->commit();
            $this->clearSettingsCache();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, int>
     */
    private function seedUsers(Database $database): array
    {
        $users = [];

        foreach ($this->factory->users() as $user) {
            $database->query(
                'INSERT INTO users (
                    first_name, last_name, email, password_hash, status, last_login_at, created_at, updated_at, deleted_at
                ) VALUES (
                    :first_name, :last_name, :email, :password_hash, :status, NULL, NOW(), NOW(), NULL
                ) ON DUPLICATE KEY UPDATE
                    first_name = VALUES(first_name),
                    last_name = VALUES(last_name),
                    password_hash = VALUES(password_hash),
                    status = VALUES(status),
                    deleted_at = NULL,
                    updated_at = NOW()',
                [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'password_hash' => password_hash((string) $user['password'], PASSWORD_DEFAULT),
                    'status' => $user['status'],
                ]
            );

            $users[(string) $user['email']] = $this->lookupId($database, 'users', 'email', (string) $user['email']);
        }

        return $users;
    }

    private function syncRolePermissions(Database $database): void
    {
        foreach ($this->factory->rolePermissions() as $roleSlug => $permissionSlugs) {
            $roleId = $this->lookupId($database, 'roles', 'slug', $roleSlug);
            if ($roleId <= 0) {
                continue;
            }

            $database->query('DELETE FROM permission_role WHERE role_id = :role_id', ['role_id' => $roleId]);

            foreach ($permissionSlugs as $permissionSlug) {
                $permissionId = $this->lookupId($database, 'permissions', 'slug', $permissionSlug);
                if ($permissionId <= 0) {
                    continue;
                }

                $database->query(
                    'INSERT IGNORE INTO permission_role (permission_id, role_id, created_at) VALUES (:permission_id, :role_id, NOW())',
                    [
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]
                );
            }
        }
    }

    /**
     * @param array<string, int> $userIds
     */
    private function syncUserRoles(Database $database, array $userIds): void
    {
        foreach ($this->factory->users() as $user) {
            $userId = $userIds[(string) $user['email']] ?? 0;
            if ($userId <= 0) {
                continue;
            }

            $roleId = $this->lookupId($database, 'roles', 'slug', (string) $user['role']);
            if ($roleId <= 0) {
                continue;
            }

            $database->query('DELETE FROM role_user WHERE user_id = :user_id', ['user_id' => $userId]);
            $database->query(
                'INSERT IGNORE INTO role_user (role_id, user_id, created_at) VALUES (:role_id, :user_id, NOW())',
                [
                    'role_id' => $roleId,
                    'user_id' => $userId,
                ]
            );
        }
    }

    /**
     * @param array<string, int> $userIds
     * @return array<string, int>
     */
    private function seedMedia(Database $database, array $userIds): array
    {
        $mediaIds = [];

        foreach ($this->factory->media() as $media) {
            $uploadedBy = $userIds[(string) ($media['uploaded_by'] ?? '')] ?? null;

            $database->query(
                'INSERT INTO media (
                    uuid, original_name, stored_name, directory, disk, extension, mime_type, file_type, size_bytes,
                    width, height, duration_seconds, alt_text, title, caption, description, checksum, is_public,
                    status, uploaded_by, created_at, updated_at, deleted_at
                ) VALUES (
                    :uuid, :original_name, :stored_name, :directory, "public", :extension, :mime_type, :file_type, :size_bytes,
                    :width, :height, NULL, :alt_text, :title, :caption, :description, :checksum, 1,
                    "active", :uploaded_by, NOW(), NOW(), NULL
                ) ON DUPLICATE KEY UPDATE
                    original_name = VALUES(original_name),
                    stored_name = VALUES(stored_name),
                    directory = VALUES(directory),
                    extension = VALUES(extension),
                    mime_type = VALUES(mime_type),
                    file_type = VALUES(file_type),
                    size_bytes = VALUES(size_bytes),
                    width = VALUES(width),
                    height = VALUES(height),
                    alt_text = VALUES(alt_text),
                    title = VALUES(title),
                    caption = VALUES(caption),
                    description = VALUES(description),
                    checksum = VALUES(checksum),
                    uploaded_by = VALUES(uploaded_by),
                    deleted_at = NULL,
                    updated_at = NOW()',
                [
                    'uuid' => $media['uuid'],
                    'original_name' => $media['original_name'],
                    'stored_name' => $media['stored_name'],
                    'directory' => $media['directory'],
                    'extension' => $media['extension'],
                    'mime_type' => $media['mime_type'],
                    'file_type' => $media['file_type'],
                    'size_bytes' => $media['size_bytes'],
                    'width' => $media['width'],
                    'height' => $media['height'],
                    'alt_text' => $media['alt_text'],
                    'title' => $media['title'],
                    'caption' => $media['caption'],
                    'description' => $media['description'],
                    'checksum' => $media['checksum'],
                    'uploaded_by' => $uploadedBy,
                ]
            );

            $mediaId = $this->lookupId($database, 'media', 'uuid', (string) $media['uuid']);
            $mediaIds[(string) $media['uuid']] = $mediaId;

            foreach ((array) ($media['variants'] ?? []) as $variant) {
                $database->query(
                    'INSERT INTO media_variants (
                        media_id, variant_key, stored_name, directory, disk, mime_type, extension, size_bytes,
                        width, height, created_at, updated_at
                    ) VALUES (
                        :media_id, :variant_key, :stored_name, :directory, "public", :mime_type, :extension, :size_bytes,
                        :width, :height, NOW(), NOW()
                    ) ON DUPLICATE KEY UPDATE
                        stored_name = VALUES(stored_name),
                        directory = VALUES(directory),
                        mime_type = VALUES(mime_type),
                        extension = VALUES(extension),
                        size_bytes = VALUES(size_bytes),
                        width = VALUES(width),
                        height = VALUES(height),
                        updated_at = NOW()',
                    [
                        'media_id' => $mediaId,
                        'variant_key' => $variant['variant_key'],
                        'stored_name' => $variant['stored_name'],
                        'directory' => $variant['directory'],
                        'mime_type' => $variant['mime_type'],
                        'extension' => $variant['extension'],
                        'size_bytes' => $variant['size_bytes'],
                        'width' => $variant['width'],
                        'height' => $variant['height'],
                    ]
                );
            }
        }

        return $mediaIds;
    }

    /**
     * @param array<string, int> $userIds
     * @param array<string, int> $mediaIds
     * @return array<string, int>
     */
    private function seedPages(Database $database, array $userIds, array $mediaIds): array
    {
        $pageIds = [];
        $sortOrder = 1;

        foreach ($this->factory->pages() as $page) {
            $featuredMediaId = $page['featured_media_uuid'] !== null
                ? ($mediaIds[(string) $page['featured_media_uuid']] ?? null)
                : null;

            $database->query(
                'INSERT INTO pages (
                    title, slug, template, status, excerpt, body, featured_media_id, parent_id, sort_order,
                    is_system, published_at, created_by, updated_by, created_at, updated_at, deleted_at
                ) VALUES (
                    :title, :slug, :template, :status, :excerpt, :body, :featured_media_id, NULL, :sort_order,
                    :is_system, NOW(), :created_by, :updated_by, NOW(), NOW(), NULL
                ) ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    template = VALUES(template),
                    status = VALUES(status),
                    excerpt = VALUES(excerpt),
                    body = VALUES(body),
                    featured_media_id = VALUES(featured_media_id),
                    sort_order = VALUES(sort_order),
                    is_system = VALUES(is_system),
                    created_by = VALUES(created_by),
                    updated_by = VALUES(updated_by),
                    deleted_at = NULL,
                    updated_at = NOW()',
                [
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'template' => $page['template'],
                    'status' => $page['status'],
                    'excerpt' => $page['excerpt'],
                    'body' => $page['body'],
                    'featured_media_id' => $featuredMediaId,
                    'sort_order' => $sortOrder,
                    'is_system' => $page['is_system'],
                    'created_by' => $userIds['mia.carter@mesh.local'] ?? null,
                    'updated_by' => $userIds['mia.carter@mesh.local'] ?? null,
                ]
            );

            $pageIds[(string) $page['slug']] = $this->lookupId($database, 'pages', 'slug', (string) $page['slug']);
            $sortOrder++;
        }

        return $pageIds;
    }

    /**
     * @param array<string, int> $pageIds
     * @param array<string, int> $mediaIds
     */
    private function seedHomeSections(Database $database, array $pageIds, array $mediaIds): void
    {
        $homePageId = $pageIds['home'] ?? 0;
        if ($homePageId <= 0) {
            return;
        }

        foreach ($this->factory->homeSections() as $section) {
            $mediaId = null;
            if (isset($section['media_uuid']) && $section['media_uuid'] !== null) {
                $mediaId = $mediaIds[(string) $section['media_uuid']] ?? null;
            }

            $database->query(
                'INSERT INTO page_sections (
                    page_id, section_key, section_type, title, subtitle, body, cta_label, cta_url,
                    media_id, json_payload, sort_order, status, created_at, updated_at
                ) VALUES (
                    :page_id, :section_key, :section_type, :title, :subtitle, :body, :cta_label, :cta_url,
                    :media_id, :json_payload, :sort_order, "published", NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    section_type = VALUES(section_type),
                    title = VALUES(title),
                    subtitle = VALUES(subtitle),
                    body = VALUES(body),
                    cta_label = VALUES(cta_label),
                    cta_url = VALUES(cta_url),
                    media_id = VALUES(media_id),
                    json_payload = VALUES(json_payload),
                    sort_order = VALUES(sort_order),
                    status = VALUES(status),
                    updated_at = NOW()',
                [
                    'page_id' => $homePageId,
                    'section_key' => $section['section_key'],
                    'section_type' => $section['section_type'],
                    'title' => $section['title'] ?? null,
                    'subtitle' => $section['subtitle'] ?? null,
                    'body' => $section['body'] ?? null,
                    'cta_label' => $section['cta_label'] ?? null,
                    'cta_url' => $section['cta_url'] ?? null,
                    'media_id' => $mediaId,
                    'json_payload' => $this->jsonEncode($section['json_payload'] ?? null),
                    'sort_order' => $section['sort_order'] ?? 0,
                ]
            );
        }
    }

    private function seedReusableBlocks(Database $database): void
    {
        foreach ($this->factory->reusableBlocks() as $block) {
            $database->query(
                'INSERT INTO reusable_blocks (
                    name, block_key, block_type, title, body, json_payload, status, created_at, updated_at
                ) VALUES (
                    :name, :block_key, :block_type, :title, :body, :json_payload, "published", NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    block_type = VALUES(block_type),
                    title = VALUES(title),
                    body = VALUES(body),
                    json_payload = VALUES(json_payload),
                    status = VALUES(status),
                    updated_at = NOW()',
                [
                    'name' => $block['name'],
                    'block_key' => $block['block_key'],
                    'block_type' => $block['block_type'],
                    'title' => $block['title'] ?? null,
                    'body' => $block['body'] ?? null,
                    'json_payload' => $this->jsonEncode($block['json_payload'] ?? null),
                ]
            );
        }
    }

    /**
     * @param array<string, int> $mediaIds
     * @return array<string, int>
     */
    private function seedServices(Database $database, array $mediaIds): array
    {
        $serviceIds = [];

        foreach ($this->factory->services() as $service) {
            $database->query(
                'INSERT INTO services (
                    title, slug, short_description, full_description, cover_media_id, sort_order, featured,
                    status, price_display, created_at, updated_at, deleted_at
                ) VALUES (
                    :title, :slug, :short_description, :full_description, :cover_media_id, :sort_order, :featured,
                    :status, :price_display, NOW(), NOW(), NULL
                ) ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    short_description = VALUES(short_description),
                    full_description = VALUES(full_description),
                    cover_media_id = VALUES(cover_media_id),
                    sort_order = VALUES(sort_order),
                    featured = VALUES(featured),
                    status = VALUES(status),
                    price_display = VALUES(price_display),
                    deleted_at = NULL,
                    updated_at = NOW()',
                [
                    'title' => $service['title'],
                    'slug' => $service['slug'],
                    'short_description' => $service['short_description'],
                    'full_description' => $service['full_description'],
                    'cover_media_id' => $mediaIds[(string) $service['cover_media_uuid']] ?? null,
                    'sort_order' => $service['sort_order'],
                    'featured' => $service['featured'],
                    'status' => $service['status'],
                    'price_display' => $service['price_display'],
                ]
            );

            $serviceIds[(string) $service['slug']] = $this->lookupId($database, 'services', 'slug', (string) $service['slug']);
        }

        return $serviceIds;
    }

    /**
     * @return array<string, int>
     */
    private function seedGalleryCategories(Database $database): array
    {
        $categoryIds = [];

        foreach ($this->factory->galleryCategories() as $category) {
            $database->query(
                'INSERT INTO gallery_categories (name, slug, description, sort_order, status, created_at, updated_at)
                 VALUES (:name, :slug, :description, :sort_order, :status, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    description = VALUES(description),
                    sort_order = VALUES(sort_order),
                    status = VALUES(status),
                    updated_at = NOW()',
                [
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'status' => $category['status'],
                ]
            );

            $categoryIds[(string) $category['slug']] = $this->lookupId($database, 'gallery_categories', 'slug', (string) $category['slug']);
        }

        return $categoryIds;
    }

    /**
     * @param array<string, int> $userIds
     * @param array<string, int> $mediaIds
     * @param array<string, int> $categoryIds
     * @return array<string, int>
     */
    private function seedGalleries(Database $database, array $userIds, array $mediaIds, array $categoryIds): array
    {
        $galleryIds = [];

        foreach ($this->factory->galleries() as $gallery) {
            $database->query(
                'INSERT INTO galleries (
                    title, slug, excerpt, story_intro, category_primary_id, cover_media_id, featured, status,
                    location, event_date, client_name, sort_order, published_at, created_by, updated_by,
                    created_at, updated_at, deleted_at
                ) VALUES (
                    :title, :slug, :excerpt, :story_intro, :category_primary_id, :cover_media_id, :featured, :status,
                    :location, :event_date, :client_name, :sort_order, :published_at, :created_by, :updated_by,
                    NOW(), NOW(), NULL
                ) ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    excerpt = VALUES(excerpt),
                    story_intro = VALUES(story_intro),
                    category_primary_id = VALUES(category_primary_id),
                    cover_media_id = VALUES(cover_media_id),
                    featured = VALUES(featured),
                    status = VALUES(status),
                    location = VALUES(location),
                    event_date = VALUES(event_date),
                    client_name = VALUES(client_name),
                    sort_order = VALUES(sort_order),
                    published_at = VALUES(published_at),
                    created_by = VALUES(created_by),
                    updated_by = VALUES(updated_by),
                    deleted_at = NULL,
                    updated_at = NOW()',
                [
                    'title' => $gallery['title'],
                    'slug' => $gallery['slug'],
                    'excerpt' => $gallery['excerpt'],
                    'story_intro' => $gallery['story_intro'],
                    'category_primary_id' => $categoryIds[(string) $gallery['primary_category_slug']] ?? null,
                    'cover_media_id' => $mediaIds[(string) $gallery['cover_media_uuid']] ?? null,
                    'featured' => $gallery['featured'],
                    'status' => $gallery['status'],
                    'location' => $gallery['location'],
                    'event_date' => $gallery['event_date'],
                    'client_name' => $gallery['client_name'],
                    'sort_order' => $gallery['sort_order'],
                    'published_at' => $gallery['published_at'],
                    'created_by' => $userIds[(string) $gallery['created_by']] ?? null,
                    'updated_by' => $userIds[(string) $gallery['updated_by']] ?? null,
                ]
            );

            $galleryId = $this->lookupId($database, 'galleries', 'slug', (string) $gallery['slug']);
            $galleryIds[(string) $gallery['slug']] = $galleryId;

            $database->query('DELETE FROM gallery_category_map WHERE gallery_id = :gallery_id', ['gallery_id' => $galleryId]);
            foreach ((array) $gallery['category_slugs'] as $index => $slug) {
                $categoryId = $categoryIds[(string) $slug] ?? 0;
                if ($categoryId <= 0) {
                    continue;
                }

                $database->query(
                    'INSERT IGNORE INTO gallery_category_map (gallery_id, category_id, created_at) VALUES (:gallery_id, :category_id, NOW())',
                    [
                        'gallery_id' => $galleryId,
                        'category_id' => $categoryId,
                    ]
                );
            }

            $database->query('DELETE FROM gallery_media WHERE gallery_id = :gallery_id', ['gallery_id' => $galleryId]);
            foreach ((array) $gallery['media_uuids'] as $index => $uuid) {
                $mediaId = $mediaIds[(string) $uuid] ?? 0;
                if ($mediaId <= 0) {
                    continue;
                }

                $database->query(
                    'INSERT INTO gallery_media (gallery_id, media_id, caption, sort_order, is_featured, created_at, updated_at)
                     VALUES (:gallery_id, :media_id, :caption, :sort_order, :is_featured, NOW(), NOW())',
                    [
                        'gallery_id' => $galleryId,
                        'media_id' => $mediaId,
                        'caption' => $gallery['excerpt'],
                        'sort_order' => $index + 1,
                        'is_featured' => $index === 0 ? 1 : 0,
                    ]
                );
            }
        }

        return $galleryIds;
    }

    /**
     * @param array<string, int> $serviceIds
     * @param array<string, int> $galleryIds
     * @param array<string, int> $mediaIds
     */
    private function seedTestimonials(Database $database, array $serviceIds, array $galleryIds, array $mediaIds): void
    {
        $names = array_map(static fn (array $row): string => (string) $row['client_name'], $this->factory->testimonials());
        if ($names !== []) {
            $placeholders = implode(', ', array_fill(0, count($names), '?'));
            $database->query('DELETE FROM testimonials WHERE client_name IN (' . $placeholders . ')', $names);
        }

        foreach ($this->factory->testimonials() as $testimonial) {
            $database->query(
                'INSERT INTO testimonials (
                    client_name, client_label, quote, long_form_story, rating, featured, service_id, gallery_id,
                    portrait_media_id, event_date, location, status, sort_order, created_at, updated_at, deleted_at
                ) VALUES (
                    :client_name, :client_label, :quote, :long_form_story, :rating, :featured, :service_id, :gallery_id,
                    :portrait_media_id, :event_date, :location, :status, :sort_order, NOW(), NOW(), NULL
                )',
                [
                    'client_name' => $testimonial['client_name'],
                    'client_label' => $testimonial['client_label'],
                    'quote' => $testimonial['quote'],
                    'long_form_story' => $testimonial['long_form_story'],
                    'rating' => $testimonial['rating'],
                    'featured' => $testimonial['featured'],
                    'service_id' => $serviceIds[(string) $testimonial['service_slug']] ?? null,
                    'gallery_id' => $galleryIds[(string) $testimonial['gallery_slug']] ?? null,
                    'portrait_media_id' => $mediaIds[(string) $testimonial['portrait_media_uuid']] ?? null,
                    'event_date' => $testimonial['event_date'],
                    'location' => $testimonial['location'],
                    'status' => $testimonial['status'],
                    'sort_order' => $testimonial['sort_order'],
                ]
            );
        }
    }

    /**
     * @return array<string, int>
     */
    private function seedBlogCategories(Database $database): array
    {
        $categoryIds = [];

        foreach ($this->factory->blogCategories() as $category) {
            $database->query(
                'INSERT INTO blog_categories (name, slug, description, sort_order, created_at, updated_at)
                 VALUES (:name, :slug, :description, :sort_order, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    description = VALUES(description),
                    sort_order = VALUES(sort_order),
                    updated_at = NOW()',
                [
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                ]
            );

            $categoryIds[(string) $category['slug']] = $this->lookupId($database, 'blog_categories', 'slug', (string) $category['slug']);
        }

        return $categoryIds;
    }

    /**
     * @return array<string, int>
     */
    private function seedBlogTags(Database $database): array
    {
        $tagIds = [];

        foreach ($this->factory->blogTags() as $tag) {
            $database->query(
                'INSERT INTO blog_tags (name, slug, description, created_at, updated_at)
                 VALUES (:name, :slug, :description, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    description = VALUES(description),
                    updated_at = NOW()',
                [
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'description' => $tag['description'],
                ]
            );

            $tagIds[(string) $tag['slug']] = $this->lookupId($database, 'blog_tags', 'slug', (string) $tag['slug']);
        }

        return $tagIds;
    }

    /**
     * @param array<string, int> $userIds
     * @param array<string, int> $mediaIds
     * @param array<string, int> $galleryIds
     * @param array<string, int> $categoryIds
     * @param array<string, int> $tagIds
     */
    private function seedBlogPosts(
        Database $database,
        array $userIds,
        array $mediaIds,
        array $galleryIds,
        array $categoryIds,
        array $tagIds
    ): void {
        foreach ($this->factory->blogPosts() as $post) {
            $database->query(
                'INSERT INTO blog_posts (
                    author_id, title, slug, excerpt, body_long, featured_image_id, cover_gallery_id, status,
                    visibility, is_featured, allow_comments, published_at, scheduled_at, archived_at, reading_time,
                    meta_summary, canonical_url, view_count, created_at, updated_at, deleted_at
                ) VALUES (
                    :author_id, :title, :slug, :excerpt, :body_long, :featured_image_id, :cover_gallery_id, :status,
                    :visibility, :is_featured, :allow_comments, :published_at, :scheduled_at, :archived_at, :reading_time,
                    :meta_summary, :canonical_url, :view_count, NOW(), NOW(), NULL
                ) ON DUPLICATE KEY UPDATE
                    author_id = VALUES(author_id),
                    title = VALUES(title),
                    excerpt = VALUES(excerpt),
                    body_long = VALUES(body_long),
                    featured_image_id = VALUES(featured_image_id),
                    cover_gallery_id = VALUES(cover_gallery_id),
                    status = VALUES(status),
                    visibility = VALUES(visibility),
                    is_featured = VALUES(is_featured),
                    allow_comments = VALUES(allow_comments),
                    published_at = VALUES(published_at),
                    scheduled_at = VALUES(scheduled_at),
                    archived_at = VALUES(archived_at),
                    reading_time = VALUES(reading_time),
                    meta_summary = VALUES(meta_summary),
                    canonical_url = VALUES(canonical_url),
                    view_count = VALUES(view_count),
                    deleted_at = NULL,
                    updated_at = NOW()',
                [
                    'author_id' => $userIds[(string) $post['author_email']] ?? null,
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'excerpt' => $post['excerpt'],
                    'body_long' => $post['body_long'],
                    'featured_image_id' => $mediaIds[(string) $post['featured_image_uuid']] ?? null,
                    'cover_gallery_id' => $galleryIds[(string) $post['cover_gallery_slug']] ?? null,
                    'status' => $post['status'],
                    'visibility' => $post['visibility'],
                    'is_featured' => $post['is_featured'],
                    'allow_comments' => $post['allow_comments'],
                    'published_at' => $post['published_at'],
                    'scheduled_at' => $post['scheduled_at'],
                    'archived_at' => $post['archived_at'],
                    'reading_time' => $post['reading_time'],
                    'meta_summary' => $post['meta_summary'],
                    'canonical_url' => $post['canonical_url'],
                    'view_count' => $post['view_count'],
                ]
            );

            $postId = $this->lookupId($database, 'blog_posts', 'slug', (string) $post['slug']);

            $database->query('DELETE FROM blog_post_categories WHERE post_id = :post_id', ['post_id' => $postId]);
            foreach ((array) $post['category_slugs'] as $slug) {
                $categoryId = $categoryIds[(string) $slug] ?? 0;
                if ($categoryId <= 0) {
                    continue;
                }

                $database->query(
                    'INSERT IGNORE INTO blog_post_categories (post_id, category_id, created_at) VALUES (:post_id, :category_id, NOW())',
                    [
                        'post_id' => $postId,
                        'category_id' => $categoryId,
                    ]
                );
            }

            $database->query('DELETE FROM blog_post_tags WHERE post_id = :post_id', ['post_id' => $postId]);
            foreach ((array) $post['tag_slugs'] as $slug) {
                $tagId = $tagIds[(string) $slug] ?? 0;
                if ($tagId <= 0) {
                    continue;
                }

                $database->query(
                    'INSERT IGNORE INTO blog_post_tags (post_id, tag_id, created_at) VALUES (:post_id, :tag_id, NOW())',
                    [
                        'post_id' => $postId,
                        'tag_id' => $tagId,
                    ]
                );
            }

            $database->query('DELETE FROM blog_post_media WHERE post_id = :post_id', ['post_id' => $postId]);
            foreach ((array) $post['media_uuids'] as $index => $uuid) {
                $mediaId = $mediaIds[(string) $uuid] ?? 0;
                if ($mediaId <= 0) {
                    continue;
                }

                $database->query(
                    'INSERT INTO blog_post_media (post_id, media_id, caption, sort_order, created_at, updated_at)
                     VALUES (:post_id, :media_id, :caption, :sort_order, NOW(), NOW())',
                    [
                        'post_id' => $postId,
                        'media_id' => $mediaId,
                        'caption' => $post['excerpt'],
                        'sort_order' => $index + 1,
                    ]
                );
            }

            $database->query('DELETE FROM blog_post_revisions WHERE post_id = :post_id', ['post_id' => $postId]);
            $database->query(
                'INSERT INTO blog_post_revisions (post_id, edited_by, title, excerpt, body_long, created_at)
                 VALUES (:post_id, :edited_by, :title, :excerpt, :body_long, NOW())',
                [
                    'post_id' => $postId,
                    'edited_by' => $userIds[(string) $post['author_email']] ?? null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body_long' => $post['body_long'],
                ]
            );
        }
    }

    /**
     * @param array<string, int> $userIds
     * @return array<string, int>
     */
    private function seedInquiries(Database $database, array $userIds): array
    {
        $inquiryIds = [];
        $emails = array_map(static fn (array $row): string => (string) $row['email'], $this->factory->inquiries());

        if ($emails !== []) {
            $placeholders = implode(', ', array_fill(0, count($emails), '?'));
            $database->query('DELETE FROM booking_requests WHERE email IN (' . $placeholders . ')', $emails);
            $database->query('DELETE FROM inquiries WHERE email IN (' . $placeholders . ')', $emails);
        }

        foreach ($this->factory->inquiries() as $inquiry) {
            $database->query(
                'INSERT INTO inquiries (
                    first_name, last_name, email, phone, company_name, service_interest, preferred_date,
                    budget_range, location, referral_source, message, status, source_ip, user_agent,
                    created_at, updated_at
                ) VALUES (
                    :first_name, :last_name, :email, :phone, :company_name, :service_interest, :preferred_date,
                    :budget_range, :location, :referral_source, :message, :status, :source_ip, :user_agent,
                    NOW(), NOW()
                )',
                [
                    'first_name' => $inquiry['first_name'],
                    'last_name' => $inquiry['last_name'],
                    'email' => $inquiry['email'],
                    'phone' => $inquiry['phone'],
                    'company_name' => $inquiry['company_name'],
                    'service_interest' => $inquiry['service_interest'],
                    'preferred_date' => $inquiry['preferred_date'],
                    'budget_range' => $inquiry['budget_range'],
                    'location' => $inquiry['location'],
                    'referral_source' => $inquiry['referral_source'],
                    'message' => $inquiry['message'],
                    'status' => $inquiry['status'],
                    'source_ip' => $inquiry['source_ip'],
                    'user_agent' => $inquiry['user_agent'],
                ]
            );

            $inquiryId = (int) $database->connection()->lastInsertId();
            $inquiryIds[(string) $inquiry['email']] = $inquiryId;

            foreach ((array) ($inquiry['notes'] ?? []) as $note) {
                $database->query(
                    'INSERT INTO inquiry_notes (inquiry_id, user_id, note, created_at, updated_at)
                     VALUES (:inquiry_id, :user_id, :note, NOW(), NOW())',
                    [
                        'inquiry_id' => $inquiryId,
                        'user_id' => $userIds['ava.stone@mesh.local'] ?? null,
                        'note' => $note,
                    ]
                );
            }
        }

        return $inquiryIds;
    }

    /**
     * @param array<string, int> $inquiryIds
     * @param array<string, int> $serviceIds
     */
    private function seedBookingRequests(Database $database, array $inquiryIds, array $serviceIds): void
    {
        foreach ($this->factory->bookingRequests() as $booking) {
            $database->query(
                'INSERT INTO booking_requests (
                    inquiry_id, service_id, first_name, last_name, email, phone, requested_date, requested_time,
                    event_type, location, hours_needed, guest_count, notes, status, source_ip, user_agent,
                    created_at, updated_at
                ) VALUES (
                    :inquiry_id, :service_id, :first_name, :last_name, :email, :phone, :requested_date, :requested_time,
                    :event_type, :location, :hours_needed, :guest_count, :notes, :status, :source_ip, :user_agent,
                    NOW(), NOW()
                )',
                [
                    'inquiry_id' => $inquiryIds[(string) $booking['inquiry_email']] ?? null,
                    'service_id' => $serviceIds[(string) $booking['service_slug']] ?? null,
                    'first_name' => $booking['first_name'],
                    'last_name' => $booking['last_name'],
                    'email' => $booking['email'],
                    'phone' => $booking['phone'],
                    'requested_date' => $booking['requested_date'],
                    'requested_time' => $booking['requested_time'],
                    'event_type' => $booking['event_type'],
                    'location' => $booking['location'],
                    'hours_needed' => $booking['hours_needed'],
                    'guest_count' => $booking['guest_count'],
                    'notes' => $booking['notes'],
                    'status' => $booking['status'],
                    'source_ip' => $booking['source_ip'],
                    'user_agent' => $booking['user_agent'],
                ]
            );
        }
    }

    private function lookupId(Database $database, string $table, string $column, string $value): int
    {
        $statement = $database->query(
            sprintf('SELECT id FROM %s WHERE %s = :value LIMIT 1', $table, $column),
            ['value' => $value]
        );

        return (int) $statement->fetchColumn();
    }

    private function jsonEncode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function clearSettingsCache(): void
    {
        $cacheFile = dirname(__DIR__, 2) . '/storage/cache/settings.php';
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    private function materializeDemoMediaAssets(): void
    {
        $writer = new PlaceholderMediaWriter(dirname(__DIR__, 2));
        $writer->materialize($this->factory->media());
    }
}
