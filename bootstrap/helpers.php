<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Models\ReusableBlock;
use App\Models\Setting;
use App\Services\AuthorizationService;
use App\Services\ReusableBlockService;
use App\Services\SecurityLogger;
use App\Models\User;
use App\Models\ActivityLog;

if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === 'true') {
                return true;
            }
            if ($trimmed === 'false') {
                return false;
            }
            if ($trimmed === 'null') {
                return null;
            }
            if ($trimmed === 'empty') {
                return '';
            }
        }

        return $value;
    }
}

if (! function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (! function_exists('base_url')) {
    function normalize_base_url(string $base): string
    {
        $normalized = trim($base);
        if ($normalized === '') {
            return '';
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:/[^/]#i', $normalized) === 1) {
            $normalized = preg_replace('#^([a-z][a-z0-9+.-]*:)/#i', '$1//', $normalized) ?? $normalized;
        }

        return rtrim($normalized, '/');
    }
}

if (! function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $baseUrl = normalize_base_url((string) config('app.url', ''));
        if ($baseUrl === '') {
            return '';
        }

        $path = (string) parse_url($baseUrl, PHP_URL_PATH);
        $normalized = '/' . trim($path, '/');

        return $normalized === '/' ? '' : $normalized;
    }
}

if (! function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $trimmedBase = normalize_base_url((string) config('app.url', ''));
        $trimmedPath = ltrim($path, '/');

        if ($trimmedPath === '') {
            return $trimmedBase;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $trimmedPath) === 1) {
            return $trimmedPath;
        }

        return $trimmedBase . '/' . $trimmedPath;
    }
}

if (! function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return app_path_url('assets/' . ltrim($path, '/'));
    }
}

if (! function_exists('app_cache_file')) {
    function app_cache_file(string $name): string
    {
        $directory = dirname(__DIR__) . '/storage/cache';
        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        return $directory . '/' . trim($name, '/\\.') . '.php';
    }
}

if (! function_exists('app_media_url')) {
    function app_media_web_path(string $directory = '', string $storedName = ''): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $storedName = trim($storedName);

        if ($directory === '' || $storedName === '') {
            return '';
        }

        foreach (['public/uploads/', 'uploads/', 'public/'] as $prefix) {
            if (str_starts_with($directory, $prefix)) {
                $directory = ltrim(substr($directory, strlen($prefix)), '/');
                break;
            }
        }

        if ($directory === '') {
            return '';
        }

        return trim($directory . '/' . $storedName, '/');
    }
}

if (! function_exists('app_media_url')) {
    function app_media_url(string $directory = '', string $storedName = ''): string
    {
        $path = app_media_web_path($directory, $storedName);
        if ($path === '') {
            return '';
        }

        return upload_url($path);
    }
}

if (! function_exists('app_media_preferred_url')) {
    /**
     * @param array<string, mixed> $record
     */
    function app_media_preferred_url(array $record, string $prefix = ''): string
    {
        $prefix = trim($prefix, '_');
        $thumbDirectoryKey = $prefix !== '' ? $prefix . '_thumb_directory' : 'thumb_directory';
        $thumbStoredNameKey = $prefix !== '' ? $prefix . '_thumb_stored_name' : 'thumb_stored_name';
        $directoryKey = $prefix !== '' ? $prefix . '_directory' : 'directory';
        $storedNameKey = $prefix !== '' ? $prefix . '_stored_name' : 'stored_name';

        $thumbUrl = app_media_url((string) ($record[$thumbDirectoryKey] ?? ''), (string) ($record[$thumbStoredNameKey] ?? ''));
        if ($thumbUrl !== '') {
            return $thumbUrl;
        }

        return app_media_url((string) ($record[$directoryKey] ?? ''), (string) ($record[$storedNameKey] ?? ''));
    }
}

if (! function_exists('redirect_url')) {
    function redirect_url(string $path = ''): string
    {
        $basePath = app_base_path();
        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            return $basePath !== '' ? $basePath : '/';
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $trimmedPath) === 1) {
            return $trimmedPath;
        }

        return ($basePath !== '' ? $basePath : '') . '/' . ltrim($trimmedPath, '/');
    }
}

if (! function_exists('app_path_url')) {
    function app_path_url(string $path = ''): string
    {
        $trimmed = trim($path);

        if ($trimmed === '') {
            $basePath = app_base_path();
            return $basePath !== '' ? $basePath : '/';
        }

        if ($trimmed[0] === '#' || preg_match('#^[a-z][a-z0-9+.-]*:#i', $trimmed) === 1) {
            return $trimmed;
        }

        $basePath = app_base_path();
        return ($basePath !== '' ? $basePath : '') . '/' . ltrim($trimmed, '/');
    }
}

if (! function_exists('app_href')) {
    function app_href(string $path = ''): string
    {
        return app_path_url($path);
    }
}

if (! function_exists('upload_url')) {
    function upload_url(string $path = ''): string
    {
        return app_path_url('uploads/' . ltrim($path, '/'));
    }
}

if (! function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        $adminBase = '/' . trim((string) config('app.admin_path', '/admin'), '/');
        $adminBase = $adminBase === '/' ? '/admin' : $adminBase;
        $trimmedPath = trim($path, '/');

        if ($trimmedPath === '') {
            return app_href($adminBase);
        }

        return app_href($adminBase . '/' . $trimmedPath);
    }
}

if (! function_exists('app_database')) {
    function app_database(): Database
    {
        static $database = null;

        if ($database instanceof Database) {
            return $database;
        }

        $defaultConnection = (string) config('database.default', 'mysql');
        $dbConfig = (array) config('database.connections.' . $defaultConnection, []);
        if ($dbConfig === []) {
            throw new RuntimeException('Database connection config not found: ' . $defaultConnection);
        }

        $database = new Database($dbConfig);
        return $database;
    }
}

if (! function_exists('app_session')) {
    function app_session(): Session
    {
        static $session = null;

        if ($session instanceof Session) {
            return $session;
        }

        $session = new Session();
        return $session;
    }
}

if (! function_exists('app_auth')) {
    function app_auth(): Auth
    {
        static $auth = null;

        if ($auth instanceof Auth) {
            return $auth;
        }

        $auth = new Auth(app_session());
        $auth->setUserResolver(static function (int $id): ?array {
            return (new User(app_database()))->findById($id);
        });

        return $auth;
    }
}

if (! function_exists('app_csrf')) {
    function app_csrf(): CSRF
    {
        static $csrf = null;

        if ($csrf instanceof CSRF) {
            return $csrf;
        }

        $csrf = new CSRF(app_session());
        return $csrf;
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        return app_auth()->currentUser();
    }
}

if (! function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        $oldInput = app_session()->getFlash('old_input', []);
        if (! is_array($oldInput)) {
            return $default;
        }

        return $oldInput[$key] ?? $default;
    }
}

if (! function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return app_csrf()->inputField();
    }
}

if (! function_exists('app_authorization')) {
    function app_authorization(): AuthorizationService
    {
        static $service = null;

        if ($service instanceof AuthorizationService) {
            return $service;
        }

        $service = new AuthorizationService(app_database());
        return $service;
    }
}

if (! function_exists('app_security_logger')) {
    function app_security_logger(): SecurityLogger
    {
        static $service = null;

        if ($service instanceof SecurityLogger) {
            return $service;
        }

        $service = new SecurityLogger(new ActivityLog(app_database()));
        return $service;
    }
}

if (! function_exists('can')) {
    function can(string $permission): bool
    {
        $userId = app_auth()->id();
        if ($userId === null) {
            return false;
        }

        return app_authorization()->userHasPermission($userId, $permission);
    }
}

if (! function_exists('has_role')) {
    function has_role(string $role): bool
    {
        $userId = app_auth()->id();
        if ($userId === null) {
            return false;
        }

        return app_authorization()->userHasRole($userId, $role);
    }
}

if (! function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'page';
    }
}

if (! function_exists('app_settings_model')) {
    function app_settings_model(): Setting
    {
        static $model = null;

        if ($model instanceof Setting) {
            return $model;
        }

        $model = new Setting(app_database());
        return $model;
    }
}

if (! function_exists('app_settings_all')) {
    /**
     * @return array<string, array<string, mixed>>
     */
    function app_settings_all(bool $refresh = false): array
    {
        static $cache = null;

        $ttl = max(0, (int) config('app.settings_cache_ttl', 300));
        $cachePath = app_cache_file('settings');

        if (! $refresh && is_array($cache)) {
            return $cache;
        }

        if (! $refresh && $ttl > 0 && is_file($cachePath) && (time() - (int) filemtime($cachePath)) <= $ttl) {
            $cached = require $cachePath;
            if (is_array($cached)) {
                $cache = $cached;
                return $cache;
            }
        }

        $cache = app_settings_model()->allGrouped();

        if ($ttl > 0) {
            $payload = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($cache, true) . ";\n";
            @file_put_contents($cachePath, $payload, LOCK_EX);
        } elseif (is_file($cachePath)) {
            @unlink($cachePath);
        }

        return $cache;
    }
}

if (! function_exists('app_settings_group')) {
    /**
     * @return array<string, mixed>
     */
    function app_settings_group(string $group, bool $refresh = false): array
    {
        $all = app_settings_all($refresh);
        return isset($all[$group]) && is_array($all[$group]) ? $all[$group] : [];
    }
}

if (! function_exists('app_setting')) {
    function app_setting(string $group, string $key, mixed $default = null, bool $refresh = false): mixed
    {
        $groupValues = app_settings_group($group, $refresh);
        return $groupValues[$key] ?? $default;
    }
}

if (! function_exists('app_settings_refresh_cache')) {
    function app_settings_refresh_cache(): void
    {
        app_settings_all(true);
    }
}

if (! function_exists('app_reusable_blocks_model')) {
    function app_reusable_blocks_model(): ReusableBlock
    {
        static $model = null;

        if ($model instanceof ReusableBlock) {
            return $model;
        }

        $model = new ReusableBlock(app_database());
        return $model;
    }
}

if (! function_exists('app_reusable_block')) {
    function app_reusable_block(string $blockKey): ?array
    {
        static $cache = [];

        if (array_key_exists($blockKey, $cache)) {
            return $cache[$blockKey];
        }

        $cache[$blockKey] = app_reusable_blocks_model()->findByKey($blockKey);
        return $cache[$blockKey];
    }
}

if (! function_exists('app_reusable_block_refresh')) {
    function app_reusable_block_refresh(string $blockKey): ?array
    {
        return app_reusable_blocks_model()->findByKey($blockKey);
    }
}

if (! function_exists('published_reusable_block')) {
    function published_reusable_block(string $blockKey): ?array
    {
        $block = app_reusable_block($blockKey);
        if (! is_array($block) || (string) ($block['status'] ?? 'draft') !== 'published') {
            return null;
        }

        return $block;
    }
}

if (! function_exists('render_reusable_block')) {
    function render_reusable_block(string $blockKey): string
    {
        $block = published_reusable_block($blockKey);
        if (! is_array($block)) {
            return '';
        }

        $service = new ReusableBlockService(app_reusable_blocks_model());
        $view = new View(dirname(__DIR__));

        return $view->partial($service->renderPartialForType((string) ($block['block_type'] ?? '')), [
            'block' => $block,
        ]);
    }
}

if (! function_exists('render_component')) {
    /**
     * @param array<string, mixed> $data
     */
    function render_component(string $view, array $data = []): string
    {
        $renderer = new View(dirname(__DIR__));
        return $renderer->partial($view, $data);
    }
}

if (! function_exists('current_path')) {
    function current_path(): string
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptPath = str_replace('\\', '/', $scriptName);
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        $baseCandidates = [];
        if ($scriptPath !== '' && $scriptPath !== '/') {
            $baseCandidates[] = rtrim($scriptPath, '/');
        }

        if ($scriptDir !== '' && $scriptDir !== '/') {
            $baseCandidates[] = $scriptDir;
            if (basename($scriptDir) === 'public') {
                $parentDir = rtrim(str_replace('\\', '/', dirname($scriptDir)), '/');
                if ($parentDir !== '' && $parentDir !== '/') {
                    $baseCandidates[] = $parentDir;
                }
            }
        }

        $appUrl = (string) ($_SERVER['APP_URL'] ?? $_ENV['APP_URL'] ?? '');
        if ($appUrl !== '') {
            $appBasePath = rtrim((string) parse_url($appUrl, PHP_URL_PATH), '/');
            if ($appBasePath !== '' && $appBasePath !== '/') {
                $baseCandidates[] = $appBasePath;
            }
        }

        foreach (array_unique($baseCandidates) as $candidate) {
            if ($path === $candidate) {
                $path = '/';
                break;
            }

            if (str_starts_with($path, $candidate . '/')) {
                $path = substr($path, strlen($candidate)) ?: '/';
                break;
            }
        }

        $normalized = '/' . ltrim($path, '/');
        return rtrim($normalized, '/') === '' ? '/' : rtrim($normalized, '/');
    }
}

if (! function_exists('is_current_path')) {
    function is_current_path(string $path, bool $exact = false): bool
    {
        $current = current_path();
        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        if ($exact || $path === '/') {
            return $current === $path;
        }

        return $current === $path || str_starts_with($current, $path . '/');
    }
}
