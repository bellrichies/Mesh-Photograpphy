<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\View;
use RuntimeException;

class SettingsController
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const GROUPS = [
        'general' => ['label' => 'General'],
        'contact' => ['label' => 'Contact'],
        'social' => ['label' => 'Social'],
        'branding' => ['label' => 'Branding'],
        'email' => ['label' => 'Email'],
        'uploads' => ['label' => 'Uploads'],
        'seo' => ['label' => 'SEO Defaults'],
    ];

    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    private const FIELD_DEFINITIONS = [
        'general' => [
            'site_name' => ['label' => 'Site Name', 'type' => 'text', 'required' => true, 'max' => 150, 'public' => true],
            'tagline' => ['label' => 'Tagline', 'type' => 'text', 'max' => 255, 'public' => true],
            'footer_text' => ['label' => 'Footer Text', 'type' => 'textarea', 'max' => 500, 'public' => true],
            'site_status' => ['label' => 'Site Status', 'type' => 'select', 'options' => ['live' => 'Live', 'maintenance' => 'Maintenance'], 'public' => true],
            'show_author_credit' => ['label' => 'Show Author Credit', 'type' => 'boolean', 'public' => true],
        ],
        'contact' => [
            'contact_email' => ['label' => 'Primary Contact Email', 'type' => 'email', 'required' => true, 'max' => 190, 'public' => true],
            'phone' => ['label' => 'Phone Number', 'type' => 'text', 'max' => 60, 'public' => true],
            'address' => ['label' => 'Address', 'type' => 'textarea', 'max' => 500, 'public' => true],
            'business_hours' => ['label' => 'Business Hours (JSON)', 'type' => 'json-textarea', 'public' => true],
        ],
        'social' => [
            'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url', 'max' => 255, 'public' => true],
            'behance_url' => ['label' => 'Behance URL', 'type' => 'url', 'max' => 255, 'public' => true],
            'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url', 'max' => 255, 'public' => true],
            'social_links' => ['label' => 'Additional Social Links (JSON)', 'type' => 'json-textarea', 'public' => true],
        ],
        'branding' => [
            'brand_site_title' => ['label' => 'Brand Site Title', 'type' => 'text', 'max' => 150, 'public' => true],
            'logo_media_id' => ['label' => 'Logo Media', 'type' => 'media-picker', 'public' => true],
            'logo_alt_text' => ['label' => 'Logo Alt Text', 'type' => 'text', 'max' => 255, 'public' => true],
            'brand_primary_color' => ['label' => 'Primary Brand Color', 'type' => 'text', 'max' => 20, 'public' => true],
            'brand_secondary_color' => ['label' => 'Secondary Brand Color', 'type' => 'text', 'max' => 20, 'public' => true],
        ],
        'email' => [
            'mailer_from_name' => ['label' => 'Mailer From Name', 'type' => 'text', 'max' => 150, 'public' => false],
            'mailer_from_email' => ['label' => 'Mailer From Email', 'type' => 'email', 'max' => 190, 'public' => false],
            'mailer_reply_to' => ['label' => 'Mailer Reply-To', 'type' => 'email', 'max' => 190, 'public' => false],
            'inquiry_recipients' => ['label' => 'Inquiry Recipients (JSON array)', 'type' => 'json-textarea', 'public' => false],
            'mail_enabled' => ['label' => 'Enable Mail Delivery', 'type' => 'boolean', 'public' => false],
        ],
        'uploads' => [
            'max_file_size_mb' => ['label' => 'Max Upload Size (MB)', 'type' => 'number', 'public' => false],
            'allowed_mime_groups' => ['label' => 'Allowed MIME Groups', 'type' => 'multiselect', 'options' => ['images' => 'Images', 'documents' => 'Documents', 'videos' => 'Videos'], 'public' => false],
            'max_image_width' => ['label' => 'Max Image Width', 'type' => 'number', 'public' => false],
            'max_image_height' => ['label' => 'Max Image Height', 'type' => 'number', 'public' => false],
        ],
        'seo' => [
            'default_meta_title_pattern' => ['label' => 'Default Meta Title Pattern', 'type' => 'text', 'max' => 255, 'public' => true],
            'default_meta_description' => ['label' => 'Default Meta Description', 'type' => 'textarea', 'max' => 320, 'public' => true],
            'default_og_image_media_id' => ['label' => 'Default OG Image', 'type' => 'media-picker', 'public' => true],
            'default_robots_index' => ['label' => 'Default Robots Index', 'type' => 'boolean', 'public' => true],
            'default_meta_keywords' => ['label' => 'Default Meta Keywords (JSON array)', 'type' => 'json-textarea', 'public' => true],
        ],
    ];

    public function index(Request $request, Response $response): Response
    {
        $activeGroup = $this->normalizeGroup((string) $request->query('group', 'general'));
        $values = app_settings_group($activeGroup);

        $oldInput = app_session()->getFlash('old_input', []);
        if (is_array($oldInput) && isset($oldInput['group'], $oldInput['values']) && $oldInput['group'] === $activeGroup && is_array($oldInput['values'])) {
            $values = array_merge($values, $oldInput['values']);
        }

        $view = new View(dirname(__DIR__, 3));

        return $response->html($view->render('admin/settings', [
            'title' => 'Settings',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => '#'],
                ['label' => 'Settings', 'href' => '#'],
            ],
            'adminPath' => admin_url(),
            'groups' => self::GROUPS,
            'activeGroup' => $activeGroup,
            'fields' => self::FIELD_DEFINITIONS[$activeGroup],
            'values' => $values,
            'uploadDefaults' => [
                'max_file_size_mb' => (int) config('uploads.max_file_size_mb', 10),
                'max_image_width' => (int) config('uploads.max_image_width', 6000),
                'max_image_height' => (int) config('uploads.max_image_height', 6000),
                'allowed' => (array) config('uploads.allowed', []),
            ],
        ], 'layouts/admin'));
    }

    public function update(Request $request, Response $response): Response
    {
        try {
            $this->verifyCsrf($request);

            $group = $this->normalizeGroup((string) $request->post('group', 'general'));
            $definitions = self::FIELD_DEFINITIONS[$group] ?? [];
            $existingValues = app_settings_group($group);

            $preparedValues = $this->prepareInputValues($group, $request, $definitions);
            $this->validateValues($preparedValues, $definitions);
            $this->persistGroupValues($group, $preparedValues, $definitions);

            app_settings_refresh_cache();
            app_security_logger()->log('settings.updated', $request, 'settings_group', null, 'Updated settings group.', [
                'group' => $group,
                'changed_keys' => $this->changedKeys($existingValues, $preparedValues),
            ]);
            app_session()->flash('success', 'Settings updated for ' . self::GROUPS[$group]['label'] . '.');
        } catch (RuntimeException $exception) {
            app_session()->flash('error', $exception->getMessage());
            app_session()->flash('old_input', [
                'group' => $this->normalizeGroup((string) $request->post('group', 'general')),
                'values' => $this->collectRawInput($request),
            ]);
        }

        $adminPath = '/' . trim((string) config('app.admin_path', '/admin'), '/');
        $targetGroup = $this->normalizeGroup((string) $request->post('group', 'general'));

        return $response->redirect($adminPath . '/settings?group=' . rawurlencode($targetGroup));
    }

    /**
     * @param array<string, mixed> $existingValues
     * @param array<string, mixed> $preparedValues
     * @return array<int, string>
     */
    private function changedKeys(array $existingValues, array $preparedValues): array
    {
        $changed = [];

        foreach ($preparedValues as $key => $value) {
            if (($existingValues[$key] ?? null) !== $value) {
                $changed[] = (string) $key;
            }
        }

        return $changed;
    }

    private function verifyCsrf(Request $request): void
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            throw new RuntimeException('Invalid CSRF token. Please refresh and try again.');
        }
    }

    private function normalizeGroup(string $group): string
    {
        $trimmed = trim($group);
        return array_key_exists($trimmed, self::GROUPS) ? $trimmed : 'general';
    }

    /**
     * @param array<string, array<string, mixed>> $definitions
     * @return array<string, mixed>
     */
    private function prepareInputValues(string $group, Request $request, array $definitions): array
    {
        $values = [];

        foreach ($definitions as $key => $meta) {
            $type = (string) ($meta['type'] ?? 'text');

            if ($type === 'boolean') {
                $values[$key] = $request->post($key) === '1' ? '1' : '0';
                continue;
            }

            if ($type === 'multiselect') {
                $raw = $request->post($key, []);
                $rawArray = is_array($raw) ? $raw : [];
                $allowed = isset($meta['options']) && is_array($meta['options']) ? array_keys($meta['options']) : [];
                $clean = array_values(array_filter(array_map('strval', $rawArray), static fn (string $item): bool => in_array($item, $allowed, true)));
                $values[$key] = $clean;
                continue;
            }

            $raw = trim((string) $request->post($key, ''));

            if ($group === 'uploads' && $raw === '') {
                $fallback = match ($key) {
                    'max_file_size_mb' => (string) config('uploads.max_file_size_mb', 10),
                    'max_image_width' => (string) config('uploads.max_image_width', 6000),
                    'max_image_height' => (string) config('uploads.max_image_height', 6000),
                    default => '',
                };
                $raw = $fallback;
            }

            $values[$key] = $raw;
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, array<string, mixed>> $definitions
     */
    private function validateValues(array $values, array $definitions): void
    {
        $rules = [];

        foreach ($definitions as $key => $meta) {
            $type = (string) ($meta['type'] ?? 'text');
            $fieldRules = [];

            if (($meta['required'] ?? false) === true) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if (isset($meta['max']) && is_numeric($meta['max'])) {
                $fieldRules[] = 'max:' . (int) $meta['max'];
            }

            if ($type === 'email') {
                $fieldRules[] = static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return filter_var((string) $value, FILTER_VALIDATE_EMAIL) !== false ? true : 'Please provide a valid email address.';
                };
            }

            if ($type === 'url') {
                $fieldRules[] = static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return filter_var((string) $value, FILTER_VALIDATE_URL) !== false ? true : 'Please provide a valid URL.';
                };
            }

            if ($type === 'json-textarea') {
                $fieldRules[] = static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    json_decode((string) $value, true);
                    return json_last_error() === JSON_ERROR_NONE ? true : 'Please provide valid JSON.';
                };
            }

            if ($type === 'number') {
                $fieldRules[] = static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    if (! is_numeric($value)) {
                        return 'Please provide a valid number.';
                    }

                    $intValue = (int) $value;
                    return ($intValue >= 1 && $intValue <= 20000) ? true : 'Number out of allowed range.';
                };
            }

            if ($type === 'media-picker') {
                $fieldRules[] = static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return ctype_digit((string) $value) ? true : 'Selected media value is invalid.';
                };
            }

            if ($type === 'select') {
                $allowed = isset($meta['options']) && is_array($meta['options']) ? array_keys($meta['options']) : [];
                $fieldRules[] = static function (mixed $value) use ($allowed): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return in_array((string) $value, $allowed, true) ? true : 'Invalid selection.';
                };
            }

            if ($type === 'multiselect') {
                $allowed = isset($meta['options']) && is_array($meta['options']) ? array_keys($meta['options']) : [];
                $fieldRules[] = static function (mixed $value) use ($allowed): bool|string {
                    if (! is_array($value)) {
                        return 'Invalid multi-select value.';
                    }

                    foreach ($value as $item) {
                        if (! in_array((string) $item, $allowed, true)) {
                            return 'Invalid option selected.';
                        }
                    }

                    return true;
                };
            }

            $rules[$key] = $fieldRules;
        }

        $validator = new Validator();
        if ($validator->validate($values, $rules)) {
            return;
        }

        $errors = $validator->errors();
        $firstField = array_key_first($errors);
        $firstMessage = ($firstField !== null && isset($errors[$firstField][0])) ? (string) $errors[$firstField][0] : 'Invalid input.';

        throw new RuntimeException($firstMessage);
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, array<string, mixed>> $definitions
     */
    private function persistGroupValues(string $group, array $values, array $definitions): void
    {
        $model = app_settings_model();

        foreach ($definitions as $key => $meta) {
            $type = (string) ($meta['type'] ?? 'text');
            $isPublic = (bool) ($meta['public'] ?? false);
            $value = $values[$key] ?? null;

            if ($type === 'json-textarea') {
                $json = null;
                if (is_string($value) && trim($value) !== '') {
                    $json = json_decode($value, true);
                }
                $model->set($group, $key, null, $json, $isPublic);
                continue;
            }

            if ($type === 'multiselect') {
                $json = is_array($value) ? array_values($value) : [];
                $model->set($group, $key, null, $json, $isPublic);
                continue;
            }

            $stringValue = is_scalar($value) ? trim((string) $value) : '';
            $model->set($group, $key, $stringValue === '' ? null : $stringValue, null, $isPublic);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function collectRawInput(Request $request): array
    {
        $all = $request->all();
        if (! is_array($all)) {
            return [];
        }

        unset($all['_token'], $all['group']);
        return $all;
    }
}
