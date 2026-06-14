# Mesh Photography — Backend Architecture

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Stack:** PHP 8.2+ Custom MVC · MySQL 8.0+ · REST JSON API

---

## Table of Contents

1. [Framework Overview](#1-framework-overview)
2. [Application Bootstrap](#2-application-bootstrap)
3. [Core Components](#3-core-components)
4. [JWT Authentication](#4-jwt-authentication)
5. [Middleware Stack](#5-middleware-stack)
6. [API Route Structure](#6-api-route-structure)
7. [Controller Layer](#7-controller-layer)
8. [Service Layer](#8-service-layer)
9. [Repository Layer](#9-repository-layer)
10. [Model Layer](#10-model-layer)
11. [Database Schema](#11-database-schema)
12. [Media Upload Pipeline](#12-media-upload-pipeline)
13. [Email System](#13-email-system)
14. [Settings and Caching](#14-settings-and-caching)
15. [Error Handling and Logging](#15-error-handling-and-logging)
16. [Validation](#16-validation)
17. [Security Implementation](#17-security-implementation)
18. [Testing Strategy](#18-testing-strategy)

---

## 1. Framework Overview

The backend is a **custom PHP 8.2+ MVC framework** with no dependency on Laravel, Symfony, or other full-stack frameworks. The architecture follows clean layering:

```
HTTP Request
    │
    ▼
public/index.php (Single Entry Point)
    │
    ▼
bootstrap/app.php → bootstrapApplication()
    │
    ▼
Application::run()
    │
    ▼
Router::dispatch(Request) → Middleware Chain → Controller
    │                                               │
    ▼                                               ▼
Response::send()                        Service → Repository → Model → Database
```

**Design principles:**
- **Thin controllers:** validate input, delegate to services, return response
- **Services:** all business logic; orchestrate multiple models
- **Repositories:** complex multi-join queries, separate from thin models
- **Models:** simple data-access operations (CRUD + specific queries)
- **No active record pattern** — all models receive `Database` via constructor injection

---

## 2. Application Bootstrap

### Entry Point (`public/index.php`)

```php
declare(strict_types=1);

$basePath = dirname(__DIR__);
$app = bootstrapApplication($basePath);
$app->run();
```

### Bootstrap Sequence (`bootstrap/app.php`)

```
bootstrapApplication(string $basePath): Application
  1. require vendor/autoload.php        (Composer PSR-4)
  2. require bootstrap/helpers.php      (50+ global helpers)
  3. Guard: .env file exists
  4. Dotenv::createImmutable()->safeLoad()
  5. validateRequiredEnvKeys([...])      (throws if missing)
  6. Config::load($basePath . '/config')
  7. error_reporting(E_ALL)
  8. display_errors = APP_DEBUG
  9. date_default_timezone_set(config('app.timezone'))
 10. return new Application($basePath)
```

### Required ENV Keys

`APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `SESSION_DRIVER`, `SESSION_LIFETIME`, `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM_ADDRESS`, `ADMIN_PATH`, `CSRF_TOKEN_NAME`, `JWT_SECRET`

---

## 3. Core Components

### Config (`App\Core\Config`)

- Singleton loaded at bootstrap
- Reads all `*.php` files in `config/` directory
- Dot-notation access: `config('database.connections.mysql.host')`
- `Config::get(string $key, mixed $default = null): mixed`

### Request (`App\Core\Request`)

- Wraps `$_SERVER`, `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`
- `method()`, `path()`, `input(key)`, `query(key)`, `file(key)`, `ip()`, `userAgent()`
- `expectsJson(): bool` — true if `Accept: application/json` or AJAX header
- `bearerToken(): ?string` — extracts `Authorization: Bearer <token>` value
- `setRouteParams(array)` / `routeParams()`

### Response (`App\Core\Response`)

Builder pattern:
- `json(array $data, int $status = 200): self`
- `html(string $content, int $status = 200): self`
- `redirect(string $url, int $status = 302): self`
- `setHeader(string $name, string $value): self`
- `send(): void` — emits headers and body (called once)

### JWT (`App\Core\JWT`)

Custom HS256 JWT implementation:

```php
class JWT
{
    public function __construct(private readonly string $secret) {}

    public function encode(array $payload, int $ttl): string
    {
        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;
        $body    = $this->base64url(json_encode($payload));
        $sig     = $this->base64url(hash_hmac('sha256', "$header.$body", $this->secret, true));
        return "$header.$body.$sig";
    }

    public function decode(string $token): array
    {
        [$header, $body, $sig] = explode('.', $token);
        $expectedSig = $this->base64url(
            hash_hmac('sha256', "$header.$body", $this->secret, true)
        );
        if (!hash_equals($expectedSig, $sig)) {
            throw new HttpException(401, 'Invalid token signature');
        }
        $payload = json_decode($this->base64urlDecode($body), true);
        if ($payload['exp'] < time()) {
            throw new HttpException(401, 'Token expired');
        }
        return $payload;
    }
}
```

### Database (`App\Core\Database`)

- PDO wrapper, singleton via `app_database()` helper
- `ATTR_EMULATE_PREPARES = false` (true server-side prepared statements)
- `ATTR_ERRMODE = ERRMODE_EXCEPTION`
- `query(string $sql, array $params = []): PDOStatement`
- `lastInsertId(): string`

### Validator (`App\Core\Validator`)

```php
$validator = new Validator($request->all(), [
    'name'    => 'required|min:2|max:255',
    'email'   => 'required|email',
    'message' => 'required|min:10',
    'phone'   => 'nullable|max:50',
]);

if ($validator->fails()) {
    return $response->json([
        'ok'     => false,
        'errors' => $validator->errors(),
    ], 422);
}
$data = $validator->validated();
```

Built-in rules: `required`, `email`, `min:N`, `max:N`, `date`, `in:a,b,c`, `nullable`, custom callable.

### Router (`App\Core\Router`)

- Routes registered as flat arrays with compiled regex patterns
- `{param}` syntax compiled to named capture groups
- Middleware applied as onion chain (reversed, wrapping handler)
- `dispatch(Request): Response` iterates routes, matches method + regex
- Throws `HttpException(404)` on no match

---

## 4. JWT Authentication

### Config (`config/jwt.php`)

```php
return [
    'secret'          => env('JWT_SECRET'),
    'access_ttl'      => (int) env('JWT_ACCESS_TTL', 900),     // 15 min
    'refresh_ttl'     => (int) env('JWT_REFRESH_TTL', 604800), // 7 days
    'refresh_cookie'  => env('JWT_REFRESH_COOKIE', 'mesh_refresh_token'),
    'algorithm'       => 'HS256',
];
```

### JwtService (`App\Services\JwtService`)

```php
class JwtService
{
    public function __construct(
        private readonly JWT $jwt,
        private readonly User $userModel
    ) {}

    public function issueTokens(array $user): array
    {
        $permissions = $this->resolvePermissions($user['id']);
        $roles       = $this->resolveRoles($user['id']);

        $accessToken = $this->jwt->encode([
            'sub'         => $user['id'],
            'email'       => $user['email'],
            'roles'       => $roles,
            'permissions' => $permissions,
        ], config('jwt.access_ttl'));

        $refreshToken = bin2hex(random_bytes(64));
        // Store hashed refresh token in DB: user_refresh_tokens table
        $this->storeRefreshToken($user['id'], $refreshToken);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => config('jwt.access_ttl'),
        ];
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $record = $this->findRefreshToken($refreshToken);
        if (!$record || $record['expires_at'] < date('Y-m-d H:i:s')) {
            throw new HttpException(401, 'Invalid or expired refresh token');
        }
        $user = $this->userModel->findById($record['user_id']);
        return $this->issueTokens($user);
    }

    public function revokeRefreshToken(string $refreshToken): void
    {
        // DELETE FROM user_refresh_tokens WHERE token_hash = hash('sha256', $refreshToken)
    }
}
```

### Additional DB Table: `user_refresh_tokens`

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT FK(users) | |
| `token_hash` | CHAR(64) | SHA-256 of refresh token |
| `expires_at` | TIMESTAMP | `created_at + JWT_REFRESH_TTL` |
| `created_at` | TIMESTAMP | |
| `revoked_at` | TIMESTAMP NULL | Set on logout |

---

## 5. Middleware Stack

All middleware implements `App\Core\Middleware\MiddlewareInterface`:

```php
interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed;
}
```

### CorsMiddleware

Runs on **every request** (registered globally in Application):

```php
public function handle(Request $request, callable $next): mixed
{
    $origin = $request->server('HTTP_ORIGIN', '');
    $allowedOrigins = config('cors.allowed_origins');

    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-Id');
    }

    if ($request->method() === 'OPTIONS') {
        return (new Response())->json([], 204);
    }

    return $next($request);
}
```

### JwtMiddleware

Applied to all `/api/v1/admin/*` routes:

```php
public function handle(Request $request, callable $next): mixed
{
    $token = $request->bearerToken();
    if (!$token) {
        throw new HttpException(401, 'Authentication required');
    }
    $payload = app_jwt()->decode($token);
    $request->setAuthPayload($payload); // stores decoded JWT in request
    return $next($request);
}
```

### PermissionMiddleware

Applied per-route with required permission slug:

```php
public function handle(Request $request, callable $next): mixed
{
    $payload = $request->authPayload();
    if (!in_array($this->permission, $payload['permissions'] ?? [], true)) {
        throw new HttpException(403, 'You do not have permission to access this resource');
    }
    return $next($request);
}
```

### RateLimitMiddleware

Applied to auth endpoints (`/api/v1/auth/login`):

```php
// Tracks login attempts in DB or Redis
// Max 10 attempts per IP per 15 minutes
// Returns 429 with Retry-After header on lockout
```

---

## 6. API Route Structure

### Route Files

```php
// routes/api.php — Public routes (no auth required)
return function (Router $router): void {
    $router->get('/api/v1/health',                   'Api\HealthController@index');
    $router->get('/api/v1/settings/public',           'Api\SettingController@public');
    $router->get('/api/v1/galleries',                 'Api\GalleryController@index');
    $router->get('/api/v1/galleries/categories',      'Api\GalleryController@categories');
    $router->get('/api/v1/galleries/{slug}',          'Api\GalleryController@show');
    $router->get('/api/v1/services',                  'Api\ServiceController@index');
    $router->get('/api/v1/services/{slug}',           'Api\ServiceController@show');
    $router->get('/api/v1/testimonials',              'Api\TestimonialController@index');
    $router->get('/api/v1/blog/posts',                'Api\BlogController@index');
    $router->get('/api/v1/blog/posts/search',         'Api\BlogController@search');
    $router->get('/api/v1/blog/categories',           'Api\BlogController@categories');
    $router->get('/api/v1/blog/categories/{slug}',    'Api\BlogController@byCategory');
    $router->get('/api/v1/blog/tags',                 'Api\BlogController@tags');
    $router->get('/api/v1/blog/tags/{slug}',          'Api\BlogController@byTag');
    $router->get('/api/v1/blog/posts/{slug}',         'Api\BlogController@show');
    $router->get('/api/v1/pages/{slug}',              'Api\PageController@show');
    $router->get('/api/v1/hero-slides',               'Api\HeroSlideController@index');
    $router->post('/api/v1/contact',                  'Api\ContactController@store');
    $router->post('/api/v1/booking',                  'Api\BookingController@store');
    $router->get('/api/v1/sitemap',                   'Api\SeoController@sitemap');
};

// routes/auth.php — JWT auth routes
return function (Router $router): void {
    $router->post('/api/v1/auth/login',   'Auth\AuthController@login')
           ->middleware([RateLimitMiddleware::for('login')]);
    $router->post('/api/v1/auth/refresh', 'Auth\AuthController@refresh');
    $router->post('/api/v1/auth/logout',  'Auth\AuthController@logout')
           ->middleware([JwtMiddleware::class]);
    $router->get('/api/v1/auth/me',       'Auth\AuthController@me')
           ->middleware([JwtMiddleware::class]);
};

// routes/admin-api.php — Protected admin routes
return function (Router $router): void {
    $jwt = [JwtMiddleware::class];
    $perm = fn(string $p) => [...$jwt, PermissionMiddleware::for($p)];

    // Dashboard
    $router->get('/api/v1/admin/dashboard',            'Admin\DashboardController@index')
           ->middleware($jwt);

    // Galleries
    $router->get('/api/v1/admin/galleries',            'Admin\GalleryController@index')
           ->middleware($perm('manage-galleries'));
    $router->post('/api/v1/admin/galleries',           'Admin\GalleryController@store')
           ->middleware($perm('manage-galleries'));
    $router->get('/api/v1/admin/galleries/{id}',       'Admin\GalleryController@show')
           ->middleware($perm('manage-galleries'));
    $router->put('/api/v1/admin/galleries/{id}',       'Admin\GalleryController@update')
           ->middleware($perm('manage-galleries'));
    $router->delete('/api/v1/admin/galleries/{id}',    'Admin\GalleryController@destroy')
           ->middleware($perm('manage-galleries'));
    // Gallery media
    $router->post('/api/v1/admin/galleries/{id}/media',         'Admin\GalleryController@attachMedia')
           ->middleware($perm('manage-galleries'));
    $router->put('/api/v1/admin/galleries/{id}/media/{mediaId}','Admin\GalleryController@updateMedia')
           ->middleware($perm('manage-galleries'));
    $router->delete('/api/v1/admin/galleries/{id}/media/{mediaId}','Admin\GalleryController@removeMedia')
           ->middleware($perm('manage-galleries'));
    $router->post('/api/v1/admin/galleries/{id}/media/reorder', 'Admin\GalleryController@reorderMedia')
           ->middleware($perm('manage-galleries'));

    // Media
    $router->get('/api/v1/admin/media',                'Admin\MediaController@index')
           ->middleware($perm('manage-media'));
    $router->post('/api/v1/admin/media/upload',        'Admin\MediaController@upload')
           ->middleware($perm('manage-media'));
    $router->put('/api/v1/admin/media/{id}',           'Admin\MediaController@update')
           ->middleware($perm('manage-media'));
    $router->delete('/api/v1/admin/media/{id}',        'Admin\MediaController@destroy')
           ->middleware($perm('manage-media'));
    $router->post('/api/v1/admin/media/{id}/archive',  'Admin\MediaController@archive')
           ->middleware($perm('manage-media'));

    // Blog Posts
    $router->get('/api/v1/admin/blog/posts',           'Admin\BlogPostController@index')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/posts',          'Admin\BlogPostController@store')
           ->middleware($perm('manage-blog'));
    $router->get('/api/v1/admin/blog/posts/{id}',      'Admin\BlogPostController@show')
           ->middleware($perm('manage-blog'));
    $router->put('/api/v1/admin/blog/posts/{id}',      'Admin\BlogPostController@update')
           ->middleware($perm('manage-blog'));
    $router->delete('/api/v1/admin/blog/posts/{id}',   'Admin\BlogPostController@destroy')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/posts/{id}/autosave', 'Admin\BlogPostController@autosave')
           ->middleware($perm('manage-blog'));

    // Blog Categories & Tags
    $router->get('/api/v1/admin/blog/categories',      'Admin\BlogCategoryController@index')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/categories',     'Admin\BlogCategoryController@store')
           ->middleware($perm('manage-blog'));
    $router->put('/api/v1/admin/blog/categories/{id}', 'Admin\BlogCategoryController@update')
           ->middleware($perm('manage-blog'));
    $router->delete('/api/v1/admin/blog/categories/{id}','Admin\BlogCategoryController@destroy')
           ->middleware($perm('manage-blog'));

    $router->get('/api/v1/admin/blog/tags',            'Admin\BlogTagController@index')
           ->middleware($perm('manage-blog'));
    $router->post('/api/v1/admin/blog/tags',           'Admin\BlogTagController@store')
           ->middleware($perm('manage-blog'));
    $router->put('/api/v1/admin/blog/tags/{id}',       'Admin\BlogTagController@update')
           ->middleware($perm('manage-blog'));
    $router->delete('/api/v1/admin/blog/tags/{id}',    'Admin\BlogTagController@destroy')
           ->middleware($perm('manage-blog'));

    // Inquiries
    $router->get('/api/v1/admin/inquiries',            'Admin\InquiryController@index')
           ->middleware($perm('manage-inquiries'));
    $router->get('/api/v1/admin/inquiries/{id}',       'Admin\InquiryController@show')
           ->middleware($perm('manage-inquiries'));
    $router->patch('/api/v1/admin/inquiries/{id}/status','Admin\InquiryController@updateStatus')
           ->middleware($perm('manage-inquiries'));
    $router->post('/api/v1/admin/inquiries/{id}/notes','Admin\InquiryController@addNote')
           ->middleware($perm('manage-inquiries'));

    // Bookings
    $router->get('/api/v1/admin/bookings',             'Admin\BookingController@index')
           ->middleware($perm('manage-inquiries'));
    $router->get('/api/v1/admin/bookings/{id}',        'Admin\BookingController@show')
           ->middleware($perm('manage-inquiries'));
    $router->patch('/api/v1/admin/bookings/{id}/status','Admin\BookingController@updateStatus')
           ->middleware($perm('manage-inquiries'));

    // Settings
    $router->get('/api/v1/admin/settings',             'Admin\SettingsController@index')
           ->middleware($perm('manage-settings'));
    $router->put('/api/v1/admin/settings',             'Admin\SettingsController@update')
           ->middleware($perm('manage-settings'));

    // Services
    $router->get('/api/v1/admin/services',             'Admin\ServiceController@index')
           ->middleware($perm('manage-services'));
    $router->post('/api/v1/admin/services',            'Admin\ServiceController@store')
           ->middleware($perm('manage-services'));
    $router->get('/api/v1/admin/services/{id}',        'Admin\ServiceController@show')
           ->middleware($perm('manage-services'));
    $router->put('/api/v1/admin/services/{id}',        'Admin\ServiceController@update')
           ->middleware($perm('manage-services'));
    $router->delete('/api/v1/admin/services/{id}',     'Admin\ServiceController@destroy')
           ->middleware($perm('manage-services'));

    // Testimonials
    $router->get('/api/v1/admin/testimonials',         'Admin\TestimonialController@index')
           ->middleware($perm('manage-testimonials'));
    $router->post('/api/v1/admin/testimonials',        'Admin\TestimonialController@store')
           ->middleware($perm('manage-testimonials'));
    $router->put('/api/v1/admin/testimonials/{id}',    'Admin\TestimonialController@update')
           ->middleware($perm('manage-testimonials'));
    $router->delete('/api/v1/admin/testimonials/{id}', 'Admin\TestimonialController@destroy')
           ->middleware($perm('manage-testimonials'));

    // Hero Slides
    $router->get('/api/v1/admin/hero-slides',          'Admin\HeroSlideController@index')
           ->middleware($perm('manage-pages'));
    $router->post('/api/v1/admin/hero-slides',         'Admin\HeroSlideController@store')
           ->middleware($perm('manage-pages'));
    $router->put('/api/v1/admin/hero-slides/{id}',     'Admin\HeroSlideController@update')
           ->middleware($perm('manage-pages'));
    $router->delete('/api/v1/admin/hero-slides/{id}',  'Admin\HeroSlideController@destroy')
           ->middleware($perm('manage-pages'));

    // Pages
    $router->get('/api/v1/admin/pages',                'Admin\PageController@index')
           ->middleware($perm('manage-pages'));
    $router->post('/api/v1/admin/pages',               'Admin\PageController@store')
           ->middleware($perm('manage-pages'));
    $router->get('/api/v1/admin/pages/{id}',           'Admin\PageController@show')
           ->middleware($perm('manage-pages'));
    $router->put('/api/v1/admin/pages/{id}',           'Admin\PageController@update')
           ->middleware($perm('manage-pages'));
    $router->delete('/api/v1/admin/pages/{id}',        'Admin\PageController@destroy')
           ->middleware($perm('manage-pages'));

    // Users
    $router->get('/api/v1/admin/users',                'Admin\UserController@index')
           ->middleware($perm('manage-users'));
    $router->post('/api/v1/admin/users',               'Admin\UserController@store')
           ->middleware($perm('manage-users'));
    $router->put('/api/v1/admin/users/{id}',           'Admin\UserController@update')
           ->middleware($perm('manage-users'));
    $router->delete('/api/v1/admin/users/{id}',        'Admin\UserController@destroy')
           ->middleware($perm('manage-users'));

    // Slug check utility
    $router->get('/api/v1/admin/slug-check',           'Admin\SlugController@check')
           ->middleware($jwt);
};
```

---

## 7. Controller Layer

All controllers extend `App\Core\Controller`. They are intentionally thin:

1. Extract and validate input
2. Delegate to services
3. Return structured JSON response

### Standard Response Envelope

```php
// Success
return $response->json([
    'ok'      => true,
    'message' => 'Gallery created successfully.',
    'data'    => $gallery,
    'meta'    => ['created_at' => date('c')],
], 201);

// Validation failure
return $response->json([
    'ok'     => false,
    'message' => 'Validation failed.',
    'errors' => $validator->errors(),
    'data'   => [],
], 422);

// Not found
throw new HttpException(404, 'Gallery not found');

// Forbidden
throw new HttpException(403, 'You do not have permission to manage galleries');
```

### Pagination Pattern

```php
// Controller: extract pagination params
$page    = max(1, (int) $request->query('page', 1));
$perPage = min(100, max(1, (int) $request->query('per_page', 20)));

// Service/Repository: paginated query
$result = $repository->paginate(filters: $filters, page: $page, perPage: $perPage);

// Response with pagination meta
return $response->json([
    'ok'   => true,
    'data' => $result['items'],
    'meta' => [
        'current_page' => $page,
        'per_page'     => $perPage,
        'total'        => $result['total'],
        'last_page'    => (int) ceil($result['total'] / $perPage),
    ],
]);
```

---

## 8. Service Layer

Services contain all business logic. They are injected with their dependencies via constructor.

### Key Services

#### `GalleryService`
- `create(array $data, int $userId): int` — creates gallery, syncs categories, records usage
- `update(int $id, array $data): void` — updates gallery, re-syncs categories
- `softDelete(int $id): void` — sets `deleted_at`
- `attachMedia(int $galleryId, int $mediaId, ?string $caption): void`
- `removeMedia(int $galleryId, int $mediaId): void`
- `reorderMedia(int $galleryId, int $mediaId, string $direction): void`

#### `BlogPostService`
- `create(array $data, int $authorId): int` — creates post, syncs taxonomy, creates revision
- `update(int $id, array $data): void`
- `softDelete(int $id): void`
- `autosave(int $id, string $body): void` — updates draft body without revision

#### `MediaUploadService`
- `upload(array $file, int $userId): array` — full upload pipeline (see Section 12)

#### `InquiryService`
- `create(array $data): int` — validates, inserts inquiry, sends confirmation email
- `updateStatus(int $id, string $status): void`
- `addNote(int $id, string $note, int $userId): int`

#### `BookingRequestService`
- `create(array $data): int`
- `updateStatus(int $id, string $status): void`

#### `JwtService`
- `issueTokens(array $user): array` — access + refresh tokens
- `refreshAccessToken(string $refreshToken): array`
- `revokeRefreshToken(string $refreshToken): void`

#### `SeoService`
- `metaForEntity(string $type, int $id): array` — loads or constructs fallback SEO data
- `generateSitemap(): string` — builds XML from all published content

---

## 9. Repository Layer

Repositories handle complex multi-join queries that don't belong in thin models.

### `BlogRepository`

```php
public function publicIndex(array $filters): array
{
    // Joins: blog_posts + media (cover) + media_variants (thumb) + blog_categories
    // GROUP_CONCAT for category names
    // Filters: status='published', published_at <= NOW()
    // Pagination support
}

public function searchPublished(string $query, int $page, int $perPage): array
{
    // FULLTEXT search on title, excerpt, body (MATCH AGAINST with IN BOOLEAN MODE)
    // Or LIKE '%query%' fallback if FULLTEXT not available
}
```

### `GalleryRepository`

```php
public function publishedWithCover(array $filters): array
{
    // Joins: galleries + media (cover) + media_variants (thumb) + gallery_categories
    // Filters by category slug, status='published'
}
```

### `MediaRepository`

```php
public function searchWithVariants(array $filters): array
{
    // Joins: media + media_variants LEFT JOIN
    // Filters: file_type, keyword (filename, alt_text, title), status='active'
    // Returns usage status from media_usage_map
}
```

---

## 10. Model Layer

All models receive `Database $db` via constructor. Methods return arrays (not objects). Soft deletes filtered via `WHERE deleted_at IS NULL`.

### Complete Model List

| Model | Table | Key Methods |
|---|---|---|
| `User` | `users` | `findById`, `findByEmail`, `allActive`, `create`, `update`, `softDelete` |
| `Role` | `roles` | `findBySlug`, `allWithPermissions` |
| `Permission` | `permissions` | `findBySlug`, `all` |
| `Setting` | `settings` | `allGrouped`, `updateByKey`, `getByGroupAndKey` |
| `Page` | `pages` | `findBySlug`, `allPublished`, `create`, `update`, `softDelete` |
| `PageSection` | `page_sections` | `forPage`, `create`, `update`, `delete`, `reorder` |
| `ReusableBlock` | `reusable_blocks` | `findByKey`, `allPublished` |
| `HeroSlide` | `hero_slides` | `allPublished`, `create`, `update`, `softDelete` |
| `Service` | `services` | `allPublished`, `findBySlug`, `create`, `update`, `softDelete` |
| `Testimonial` | `testimonials` | `allPublished`, `create`, `update`, `softDelete` |
| `Media` | `media` | `findById`, `findByUuid`, `create`, `update`, `archive`, `hardDelete` |
| `MediaVariant` | `media_variants` | `findByMediaId`, `upsert` |
| `MediaUsageMap` | `media_usage_map` | `record`, `remove`, `isInUse` |
| `Gallery` | `galleries` | `allPublished`, `findBySlug`, `create`, `update`, `softDelete` |
| `GalleryCategory` | `gallery_categories` | `all`, `findBySlug`, `create`, `update`, `delete` |
| `GalleryCategoryMap` | `gallery_category_map` | `sync`, `forGallery` |
| `GalleryMedia` | `gallery_media` | `attach`, `update`, `remove`, `reorder`, `forGallery` |
| `BlogPost` | `blog_posts` | `findBySlug`, `allPublished`, `create`, `update`, `softDelete` |
| `BlogCategory` | `blog_categories` | `all`, `findBySlug`, `create`, `update`, `delete` |
| `BlogTag` | `blog_tags` | `all`, `findBySlug`, `create`, `update`, `delete` |
| `BlogPostCategory` | `blog_post_categories` | `sync`, `forPost` |
| `BlogPostTag` | `blog_post_tags` | `sync`, `forPost` |
| `BlogPostRevision` | `blog_post_revisions` | `create`, `forPost`, `findById` |
| `SeoMeta` | `seo_meta` | `findForEntity`, `upsert` |
| `ActivityLog` | `activity_logs` | `log`, `recent` |
| `Inquiry` | `inquiries` | `create`, `allWithStatus`, `findById`, `updateStatus` |
| `InquiryNote` | `inquiry_notes` | `create`, `forInquiry` |
| `BookingRequest` | `booking_requests` | `create`, `allWithStatus`, `findById`, `updateStatus` |
| `UserRefreshToken` | `user_refresh_tokens` | `store`, `find`, `revoke`, `pruneExpired` |

---

## 11. Database Schema

The full schema is defined in `docs/blueprint.md` §11. Key additions for the API architecture:

### `user_refresh_tokens` (New)

```sql
CREATE TABLE user_refresh_tokens (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    token_hash  CHAR(64) NOT NULL UNIQUE,  -- SHA-256 of raw refresh token
    expires_at  TIMESTAMP NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_at  TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

### `manage-services` Permission

Add to migration `20260315020000` or new migration:
```sql
INSERT INTO permissions (name, slug, description) VALUES
('Manage Services', 'manage-services', 'Create, edit, and delete service listings');
```

---

## 12. Media Upload Pipeline

```
POST /api/v1/admin/media/upload
  Content-Type: multipart/form-data
  Authorization: Bearer <token>

  Field: file (binary)
  Field: context (optional: 'gallery', 'blog', 'service', etc.)
```

### `MediaUploadService::upload(array $file, int $userId): array`

```
Step 1:  Check $_FILES structure validity
Step 2:  Verify PHP upload error code === UPLOAD_ERR_OK
Step 3:  is_uploaded_file($file['tmp_name']) — path traversal prevention
Step 4:  finfo_file() MIME detection (NOT client Content-Type)
Step 5:  Resolve file type: image / video / document / other
Step 6:  Extension whitelist (block php, phtml, phar, exe, sh, bat, cmd, ps1)
Step 7:  MIME whitelist check per type from config/uploads.php
Step 8:  Size check: filesize($tmp) <= UPLOAD_MAX_FILE_SIZE_MB * 1024 * 1024
Step 9:  For images: getimagesize() validity + dimension check <= 6000x6000
Step 10: SHA-256 checksum: hash_file('sha256', $tmp)
Step 11: Build target directory: public/uploads/{type}/YYYY/MM/
Step 12: mkdir($dir, 0775, true) — create recursively
Step 13: ensureDirectoryProtection($dir) — write .htaccess blocking scripts
Step 14: Generate stored_name: date('YmdHis') . '-' . bin2hex(random_bytes(10)) . '.' . $ext
Step 15: move_uploaded_file($tmp, $targetPath)
Step 16: INSERT INTO media (all fields including uuid = UUID(), checksum)
Step 17: If image: createImageVariant('thumb', $mediaId, $targetPath)
         → GD: imagecreatefrom*(), resize to 640px width (proportional)
         → imagejpeg($resized, $thumbPath, 82)
         → INSERT INTO media_variants ON DUPLICATE KEY UPDATE
Step 18: Return { id, uuid, url, thumb_url, mime_type, file_type, size_bytes, width, height }
```

---

## 13. Email System

### `MailService::send(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool`

```php
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = config('mail.mailers.smtp.host');
$mail->SMTPAuth   = true;
$mail->Username   = config('mail.mailers.smtp.username');
$mail->Password   = config('mail.mailers.smtp.password');
$mail->SMTPSecure = config('mail.mailers.smtp.encryption') === 'ssl'
    ? PHPMailer::ENCRYPTION_SMTPS
    : PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = config('mail.mailers.smtp.port');
$mail->setFrom(config('mail.from.address'), config('mail.from.name'));
$mail->addAddress($to);
if ($replyTo) { $mail->addReplyTo($replyTo); }
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->Body    = $htmlBody;
$mail->send();
```

### Email Templates (`resources/views/emails/`)

All templates use inline styles for email client compatibility:

- `inquiry-confirmation.php` — visitor confirmation on contact form
- `booking-confirmation.php` — visitor confirmation on booking request
- `inquiry-notification.php` — admin alert on new inquiry (Planned)
- `booking-notification.php` — admin alert on new booking (Planned)
- `password-reset.php` — password reset token (Planned)

---

## 14. Settings and Caching

### Settings Access Pattern

```php
// Single value with fallback
app_setting('site', 'name', 'Mesh Photography');

// Entire group
app_settings_group('contact');

// All groups
app_settings_all();
```

### Cache Implementation

```
Request calls app_settings_all()
  │
  ├─ Static $cache set? → return $cache
  │
  ├─ Cache file exists and age < TTL? → require file → set $cache → return
  │
  └─ Query DB → build array → write cache file (LOCK_EX) → set $cache → return

After POST /api/v1/admin/settings:
  └─ app_settings_refresh_cache() → unlink cache file → rebuild
```

---

## 15. Error Handling and Logging

### Response Codes

| Scenario | HTTP Code |
|---|---|
| Success (read) | 200 |
| Created | 201 |
| No content (delete) | 204 |
| Bad request / validation | 422 |
| Unauthorized (no token) | 401 |
| Forbidden (no permission) | 403 |
| Not found | 404 |
| Rate limited | 429 |
| Server error | 500 |

### JSON Error Responses

```php
// HttpException caught by Application::run()
// All API responses follow the envelope
{
    "ok": false,
    "message": "Gallery not found",
    "errors": {},
    "data": null,
    "meta": { "status": 404 }
}
```

### Log Format

```json
{
  "timestamp": "2026-06-14T12:00:00+00:00",
  "request_id": "a1b2c3d4",
  "level": "error",
  "status": 500,
  "method": "POST",
  "path": "/api/v1/admin/galleries",
  "ip": "10.0.0.1",
  "user_id": 3,
  "exception": "PDOException",
  "message": "...",
  "file": "...",
  "line": 42
}
```

---

## 16. Validation

Every controller validates input before passing to services:

```php
$validator = new Validator($request->all(), [
    'title'       => 'required|min:2|max:255',
    'slug'        => 'nullable|max:255',
    'description' => 'nullable|max:5000',
    'status'      => 'required|in:draft,published',
    'cover_media_id' => 'nullable',
]);

if ($validator->fails()) {
    return $response->json([
        'ok'     => false,
        'message' => 'Validation failed.',
        'errors' => $validator->errors(),
    ], 422);
}
```

Domain-specific validators in `app/Validators/` (e.g., `GalleryValidator`, `BlogPostValidator`) encapsulate reusable rule sets.

---

## 17. Security Implementation

### Password Handling

```php
// Store
$hash = password_hash($password, PASSWORD_DEFAULT); // bcrypt, cost=10

// Verify
$ok = password_verify($inputPassword, $storedHash);
```

### CSRF (for any non-API form endpoints)

API endpoints use JWT (stateless) — no CSRF needed for Bearer token auth. CSRF remains for any server-rendered fallback forms.

### SQL Injection Prevention

```php
// Always parameterized — never string interpolation
$db->query('SELECT * FROM galleries WHERE slug = :slug AND deleted_at IS NULL', [
    'slug' => $slug,
])->fetch(PDO::FETCH_ASSOC);
```

### Output Escaping

API responses contain raw data (JSON-encoded). XSS prevention is a frontend responsibility (React's JSX auto-escapes by default). CMS HTML bodies are admin-authored — intentionally unescaped when stored; rendered in React via `dangerouslySetInnerHTML` with DOMPurify sanitization.

---

## 18. Testing Strategy

### Unit Tests (`tests/Unit/`)

Priority test classes:
- `JwtTest` — encode/decode/expiry/tamper
- `ValidatorTest` — all rule combinations
- `MediaUploadServiceTest` — validation logic (no filesystem; use VFS)
- `LoginThrottleServiceTest` — attempt counting, lockout, reset
- `SlugifyTest` — edge cases

### Integration Tests (`tests/Feature/`)

Priority flows:
- `AuthTest` — login success/failure/lockout, token refresh, logout
- `ContactTest` — form submission end-to-end, email mock
- `GalleryApiTest` — CRUD, pagination, permission enforcement
- `MediaUploadTest` — happy path, MIME rejection, size rejection
- `SettingsTest` — save + cache invalidation

### Test Database

```bash
# .env.testing
DB_DATABASE=mesh_photo_test

# Run tests
DB_DATABASE=mesh_photo_test vendor/bin/phpunit
```

Migrations run via setUp(); seeders provide deterministic base data.
