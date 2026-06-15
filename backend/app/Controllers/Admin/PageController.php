<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Page;
use App\Services\MediaFormatter;
use Throwable;

class PageController extends Controller
{
    private Page $pageModel;
    private MediaFormatter $mediaFormatter;

    private const SECTION_TYPES = ['text', 'rich_text', 'image', 'json'];
    private const ROBOTS_VALUES = ['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'];

    public function __construct()
    {
        $this->pageModel = new Page(app_database());
        $this->mediaFormatter = new MediaFormatter();
    }

    public function index(Request $request, Response $response): Response
    {
        $rows = app_database()->query(
            'SELECT id, title, slug, template, is_published, created_at, updated_at
             FROM pages WHERE deleted_at IS NULL ORDER BY title ASC'
        )->fetchAll();

        return $this->success(array_map([$this, 'formatRow'], $rows));
    }

    public function show(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        $row = $db->query(
            'SELECT p.*,
                    m.id AS og_image_id, m.uuid AS og_image_uuid, m.path AS og_image_path,
                    m.alt_text AS og_image_alt, m.original_name AS og_image_original,
                    m.file_name AS og_image_file, m.mime_type AS og_image_mime,
                    m.file_size AS og_image_size, m.width AS og_image_width,
                    m.height AS og_image_height
             FROM pages p
             LEFT JOIN media m ON p.og_image_id = m.id AND m.deleted_at IS NULL
             WHERE p.id = ? AND p.deleted_at IS NULL
             LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) {
            throw new HttpException(404, 'Page not found.');
        }

        $sections = $this->pageModel->findSectionsByPageId($id);
        $data = $this->formatRow($row);
        $data['body'] = $row['body'] ?? null;
        $data['sections'] = empty($sections)
            ? [[
                'id' => 1,
                'section_key' => 'body',
                'section_type' => 'rich_text',
                'title' => null,
                'content' => $row['body'] ?? null,
                'media_id' => null,
                'media' => null,
                'settings' => (object) [],
                'sort_order' => 0,
            ]]
            : array_map(fn (array $section): array => $this->formatSection($section), $sections);

        return $this->success($data);
    }

    public function store(Request $request, Response $response): Response
    {
        $db = app_database();
        $data = $request->json();
        $payload = $this->normalizePayload($data);

        if ($payload['errors']) {
            return $this->validationError($payload['errors']);
        }

        if ($db->query('SELECT id FROM pages WHERE slug = ? AND deleted_at IS NULL', [$payload['slug']])->fetch()) {
            return $this->validationError(['slug' => ['Slug already in use.']]);
        }

        try {
            $db->beginTransaction();

            $db->query(
                'INSERT INTO pages
                    (title, slug, body, template, is_published, seo_title, seo_description,
                     canonical_url, og_title, og_description, og_image_id, seo_robots, schema_markup,
                     created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                [
                    $payload['title'],
                    $payload['slug'],
                    $payload['body'],
                    $payload['template'],
                    $payload['is_published'],
                    $payload['seo_title'],
                    $payload['seo_description'],
                    $payload['canonical_url'],
                    $payload['og_title'],
                    $payload['og_description'],
                    $payload['og_image_id'],
                    $payload['seo_robots'],
                    $payload['schema_markup'],
                ]
            );

            $id = (int) $db->lastInsertId();
            if ($payload['sections'] !== null) {
                $this->replaceSections($id, $payload['sections']);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $row = $db->query('SELECT * FROM pages WHERE id = ?', [$id])->fetch();
        return $this->created($this->formatRow($row));
    }

    public function update(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        $data = $request->json();
        $payload = $this->normalizePayload($data);

        if ($payload['errors']) {
            return $this->validationError($payload['errors']);
        }

        if (!$db->query('SELECT id FROM pages WHERE id = ? AND deleted_at IS NULL', [$id])->fetch()) {
            throw new HttpException(404, 'Page not found.');
        }

        if ($db->query('SELECT id FROM pages WHERE slug = ? AND id != ? AND deleted_at IS NULL', [$payload['slug'], $id])->fetch()) {
            return $this->validationError(['slug' => ['Slug already in use.']]);
        }

        try {
            $db->beginTransaction();

            $db->query(
                'UPDATE pages
                 SET title = ?, slug = ?, body = ?, template = ?, is_published = ?,
                     seo_title = ?, seo_description = ?, canonical_url = ?, og_title = ?,
                     og_description = ?, og_image_id = ?, seo_robots = ?, schema_markup = ?,
                     updated_at = NOW()
                 WHERE id = ?',
                [
                    $payload['title'],
                    $payload['slug'],
                    $payload['body'],
                    $payload['template'],
                    $payload['is_published'],
                    $payload['seo_title'],
                    $payload['seo_description'],
                    $payload['canonical_url'],
                    $payload['og_title'],
                    $payload['og_description'],
                    $payload['og_image_id'],
                    $payload['seo_robots'],
                    $payload['schema_markup'],
                    $id,
                ]
            );

            if ($payload['sections'] !== null) {
                $this->replaceSections($id, $payload['sections']);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $row = $db->query('SELECT * FROM pages WHERE id = ?', [$id])->fetch();
        return $this->success($this->formatRow($row));
    }

    public function destroy(Request $request, Response $response): Response
    {
        $db = app_database();
        $id = (int) $request->param('id');
        if (!$db->query('SELECT id FROM pages WHERE id = ? AND deleted_at IS NULL', [$id])->fetch()) {
            throw new HttpException(404, 'Page not found.');
        }
        $db->query('UPDATE pages SET deleted_at = NOW() WHERE id = ?', [$id]);
        return $this->noContent();
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $title = $this->cleanText($data['title'] ?? null, 255);
        $slug = $this->cleanSlug($data['slug'] ?? null);

        if ($title === null) {
            $errors['title'][] = 'Title is required.';
        }

        if ($slug === null) {
            $errors['slug'][] = 'Slug is required.';
        }

        $sections = null;
        if (array_key_exists('sections', $data)) {
            if (!is_array($data['sections'])) {
                $errors['sections'][] = 'Sections must be an array.';
            } else {
                [$sections, $sectionErrors] = $this->normalizeSections($data['sections']);
                $errors = array_merge($errors, $sectionErrors);
            }
        }

        $body = $this->sanitizeRichText($data['body'] ?? null);
        if (($body === null || $body === '') && is_array($sections)) {
            $body = $this->deriveLegacyBody($sections);
        }

        $canonicalUrl = $this->cleanUrl($data['canonical_url'] ?? null);
        if (!empty($data['canonical_url']) && $canonicalUrl === null) {
            $errors['canonical_url'][] = 'Canonical URL must be a valid URL.';
        }

        $ogImageId = $this->cleanInteger($data['og_image_id'] ?? null);
        if ($ogImageId !== null && !$this->mediaExists($ogImageId)) {
            $errors['og_image_id'][] = 'Open Graph image was not found in the media library.';
        }

        $robots = $this->cleanText($data['seo_robots'] ?? null, 100) ?? 'index, follow';
        if (!in_array($robots, self::ROBOTS_VALUES, true)) {
            $errors['seo_robots'][] = 'Robots value is invalid.';
        }

        $schemaMarkup = $this->cleanNullableString($data['schema_markup'] ?? null);
        if ($schemaMarkup !== null) {
            json_decode($schemaMarkup, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['schema_markup'][] = 'Schema markup must be valid JSON-LD.';
            }
        }

        return [
            'errors' => $errors,
            'title' => $title,
            'slug' => $slug,
            'body' => $body,
            'template' => $this->cleanText($data['template'] ?? null, 100),
            'is_published' => !empty($data['is_published']) ? 1 : 0,
            'seo_title' => $this->cleanText($data['seo_title'] ?? null, 255),
            'seo_description' => $this->cleanText($data['seo_description'] ?? null, 500),
            'canonical_url' => $canonicalUrl,
            'og_title' => $this->cleanText($data['og_title'] ?? null, 255),
            'og_description' => $this->cleanText($data['og_description'] ?? null, 500),
            'og_image_id' => $ogImageId,
            'seo_robots' => $robots,
            'schema_markup' => $schemaMarkup,
            'sections' => $sections,
        ];
    }

    private function normalizeSections(array $sections): array
    {
        $normalized = [];
        $errors = [];
        $seen = [];

        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                $errors["sections.{$index}"][] = 'Section must be an object.';
                continue;
            }

            $key = $this->cleanSectionKey($section['section_key'] ?? null);
            if ($key === null) {
                $errors["sections.{$index}.section_key"][] = 'Section key is required.';
                continue;
            }

            if (isset($seen[$key])) {
                $errors["sections.{$index}.section_key"][] = 'Section key must be unique per page.';
                continue;
            }
            $seen[$key] = true;

            $type = (string) ($section['section_type'] ?? 'text');
            if (!in_array($type, self::SECTION_TYPES, true)) {
                $errors["sections.{$index}.section_type"][] = 'Section type is invalid.';
                continue;
            }

            $content = $section['content'] ?? null;
            if ($type === 'rich_text') {
                $content = $this->sanitizeRichText($content);
            } elseif ($type === 'json') {
                $content = $this->normalizeJsonContent($content);
                if ($content === null && !empty($section['content'])) {
                    $errors["sections.{$index}.content"][] = 'JSON content is invalid.';
                }
            } else {
                $content = $this->cleanNullableString($content);
            }

            $mediaId = $this->cleanInteger($section['media_id'] ?? null);
            if ($mediaId !== null && !$this->mediaExists($mediaId)) {
                $errors["sections.{$index}.media_id"][] = 'Selected media was not found.';
            }

            $settingsJson = null;
            if (isset($section['settings']) && is_array($section['settings'])) {
                $settingsJson = json_encode($section['settings'], JSON_THROW_ON_ERROR);
            }

            $normalized[] = [
                'section_key' => $key,
                'section_type' => $type,
                'title' => $this->cleanText($section['title'] ?? null, 255),
                'content' => $content,
                'media_id' => $mediaId,
                'settings_json' => $settingsJson,
                'sort_order' => (int) ($section['sort_order'] ?? $index),
            ];
        }

        usort($normalized, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

        return [$normalized, $errors];
    }

    private function replaceSections(int $pageId, array $sections): void
    {
        $this->pageModel->deleteSectionsForPage($pageId);

        foreach ($sections as $section) {
            $this->pageModel->insertSection(
                $pageId,
                $section['section_key'],
                $section['section_type'],
                $section['title'],
                $section['content'],
                $section['media_id'],
                $section['settings_json'],
                $section['sort_order']
            );
        }
    }

    private function deriveLegacyBody(array $sections): ?string
    {
        foreach ($sections as $section) {
            if (in_array($section['section_key'], ['body', 'story_body', 'policy_content'], true)) {
                return $section['content'] ?: null;
            }
        }

        return null;
    }

    private function formatSection(array $section): array
    {
        $settings = null;
        if (!empty($section['settings_json'])) {
            $decoded = json_decode((string) $section['settings_json'], true);
            $settings = is_array($decoded) ? $decoded : null;
        }

        return [
            'id' => (int) $section['id'],
            'section_key' => $section['section_key'],
            'section_type' => $section['section_type'],
            'title' => $section['label'],
            'content' => $section['content'],
            'media_id' => isset($section['media_id']) ? (int) $section['media_id'] : null,
            'media' => !empty($section['media_path'])
                ? $this->mediaFormatter->formatCover($section, 'media')
                : null,
            'settings' => $settings ?? (object) [],
            'sort_order' => (int) $section['sort_order'],
        ];
    }

    private function formatRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'template' => $row['template'] ?? null,
            'status' => ($row['is_published'] ?? 0) ? 'published' : 'draft',
            'is_published' => !empty($row['is_published']),
            'seo' => [
                'meta_title' => $row['seo_title'] ?? null,
                'meta_description' => $row['seo_description'] ?? null,
                'og_title' => $row['og_title'] ?? null,
                'og_description' => $row['og_description'] ?? null,
                'og_image_url' => !empty($row['og_image_path'])
                    ? $this->mediaFormatter->formatCover($row, 'og_image')['url']
                    : null,
                'canonical_url' => $row['canonical_url'] ?? null,
                'robots' => $row['seo_robots'] ?? 'index, follow',
                'schema_markup' => $row['schema_markup'] ?? null,
            ],
            'og_image_id' => isset($row['og_image_id']) ? (int) $row['og_image_id'] : null,
            'og_image' => !empty($row['og_image_path'])
                ? $this->mediaFormatter->formatCover($row, 'og_image')
                : null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    private function cleanText(mixed $value, int $max): ?string
    {
        $value = $this->cleanNullableString($value);
        if ($value === null) {
            return null;
        }

        return mb_substr(strip_tags($value), 0, $max);
    }

    private function cleanNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function cleanSlug(mixed $value): ?string
    {
        $value = $this->cleanNullableString($value);
        if ($value === null) {
            return null;
        }

        $slug = slugify($value);
        return $slug === '' ? null : mb_substr($slug, 0, 255);
    }

    private function cleanSectionKey(mixed $value): ?string
    {
        $value = $this->cleanNullableString($value);
        if ($value === null || !preg_match('/^[a-z0-9_:-]{1,100}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function cleanUrl(mixed $value): ?string
    {
        $value = $this->cleanNullableString($value);
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? mb_substr($value, 0, 500) : null;
    }

    private function cleanInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : null;
    }

    private function normalizeJsonContent(mixed $value): ?string
    {
        $value = $this->cleanNullableString($value);
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return json_encode($decoded, JSON_THROW_ON_ERROR);
    }

    private function sanitizeRichText(mixed $html): ?string
    {
        $html = $this->cleanNullableString($html);
        if ($html === null) {
            return null;
        }

        $html = preg_replace('#<(script|style|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><s><blockquote><ul><ol><li><h2><h3><hr><a>');
        $html = preg_replace('/\s(on[a-z]+|style)\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*(javascript:|data:)[^"\']*\2/i', '', $html) ?? '';

        return trim($html) === '' ? null : trim($html);
    }

    private function mediaExists(int $id): bool
    {
        return (bool) app_database()
            ->query('SELECT id FROM media WHERE id = ? AND deleted_at IS NULL LIMIT 1', [$id])
            ->fetch();
    }
}
