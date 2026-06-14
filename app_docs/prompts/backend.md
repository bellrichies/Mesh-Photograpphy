# Mesh Photography — Backend Implementation Prompt

> **For:** PHP Developer / AI Coding Agent  
> **Project:** Mesh Photography REST API  
> **Stack:** PHP 8.2+ Custom MVC · MySQL 8.0+ · JWT Authentication  
> **Architecture Reference:** `docs/04-backend-architecture.md`  
> **API Reference:** `docs/06-api-design.md`  
> **Master Blueprint:** `docs/blueprint.md`

---

## Your Mission

You are implementing the **PHP REST API backend** for Mesh Photography, a premium photography portfolio and studio management platform. Your output is a running, tested PHP application that exposes a JSON REST API consumed by a separate React frontend.

This is a **custom PHP 8.2+ MVC application** — you are NOT using Laravel, Symfony, CodeIgniter, or any other PHP framework. A custom MVC framework is already defined and partially implemented. Your job is to extend it to serve a complete REST API.

Read every section of this prompt before writing a single line of code. The architecture is precise — deviating from it without justification will cause integration failures with the frontend.

---

## Source of Truth Documents

You must study these documents before implementing:

1. `docs/blueprint.md` — Complete MVC architecture, all database tables, services, models, middleware, helpers. This is the authoritative reference for all existing components.
2. `docs/04-backend-architecture.md` — JWT implementation, API route structure, middleware stack, controller patterns, service layer, new additions beyond the blueprint.
3. `docs/06-api-design.md` — Every API endpoint: URL, method, request body, response shape, HTTP status codes, error formats.
4. `docs/02-build_blueprint.md` — Directory structure, environment variables, coding standards, dependency list.

---

## Project Context

The existing codebase has a working PHP MVC with:
- Custom Router, Request, Response, Config, Session, CSRF, Database, Validator, View, ErrorHandler
- 31 models, 3 repositories, 20 services
- 36 database migrations and 8 seeders
- PHP template-based views (these will NOT be used — the API serves JSON only)
- Session-based admin authentication (this is being replaced/extended with JWT for API)

Your task is to **add the REST API layer** on top of this existing architecture:
1. Add JWT authentication (`App\Core\JWT`, `App\Services\JwtService`)
2. Add CORS middleware (`App\Core\Middleware\CorsMiddleware`)
3. Add JWT middleware (`App\Core\Middleware\JwtMiddleware`)
4. Refactor/extend all existing controllers to serve JSON instead of (or in addition to) HTML
5. Create new API-specific controllers under `app/Controllers/Api/` and `app/Controllers/Admin/`
6. Register all routes in `routes/api.php`, `routes/admin-api.php`, and `routes/auth.php`
7. Ensure `ErrorHandler` returns JSON for all `/api/*` requests

---

## Non-Negotiable Requirements

Before any feature implementation, these must be in place:

### 1. All errors on `/api/*` routes return JSON

```php
// If request path starts with /api/, always return JSON envelope
// Never return HTML error pages for API routes
{
  "ok": false,
  "message": "Not found",
  "errors": {},
  "data": null,
  "meta": { "status": 404 }
}
```

### 2. CORS on every response

Every response to any request includes appropriate CORS headers. The `CorsMiddleware` must run before routing, not inside route handlers.

```php
// config/cors.php
return [
    'allowed_origins'   => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')),
    'allowed_methods'   => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers'   => ['Content-Type', 'Authorization', 'X-Request-Id'],
    'allow_credentials' => true,
    'max_age'           => 86400,
];
```

Handle `OPTIONS` preflight immediately (return 204 before routing):

```php
if ($request->method() === 'OPTIONS') {
    return (new Response())->json([], 204);
}
```

### 3. Consistent response envelope

Every JSON response — success and error — uses:

```json
{
  "ok": true|false,
  "message": "Human-readable string",
  "data": {} | [] | null,
  "errors": {},
  "meta": {}
}
```

Create a helper or base controller method to build this envelope.

### 4. Correct HTTP status codes

- `200` — Successful read/update
- `201` — Resource created
- `204` — Delete with no body (or `200` with empty data if envelope required)
- `401` — No/invalid/expired token
- `403` — Valid token but insufficient permission
- `404` — Resource not found
- `422` — Validation failure (include `errors` map)
- `429` — Rate limited (include `retry_after`)
- `500` — Server error (message: generic in prod, specific in debug mode)

### 5. No sensitive data in responses or logs

Never include passwords, raw JWT secrets, or PII beyond what the requesting user is authorized to receive.

---

## Implementation Order

Follow this exact order to avoid dependency issues:

### Step 1: JWT Core

Implement `App\Core\JWT`:

```php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

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
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new HttpException(401, 'Malformed token');
        }
        [$header, $body, $sig] = $parts;
        $expectedSig = $this->base64url(
            hash_hmac('sha256', "$header.$body", $this->secret, true)
        );
        if (!hash_equals($expectedSig, $sig)) {
            throw new HttpException(401, 'Invalid token signature');
        }
        $payload = json_decode($this->base64urlDecode($body), true);
        if (!is_array($payload)) {
            throw new HttpException(401, 'Invalid token payload');
        }
        if (($payload['exp'] ?? 0) < time()) {
            throw new HttpException(401, 'Token expired');
        }
        return $payload;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
```

Add `app_jwt(): JWT` helper in `bootstrap/helpers.php`:
```php
function app_jwt(): JWT {
    static $instance;
    if (!$instance) {
        $instance = new \App\Core\JWT(config('jwt.secret'));
    }
    return $instance;
}
```

Add `config/jwt.php`:
```php
return [
    'secret'          => env('JWT_SECRET'),
    'access_ttl'      => (int) env('JWT_ACCESS_TTL', 900),
    'refresh_ttl'     => (int) env('JWT_REFRESH_TTL', 604800),
    'refresh_cookie'  => env('JWT_REFRESH_COOKIE', 'mesh_refresh_token'),
];
```

---

### Step 2: `user_refresh_tokens` Migration

```php
// database/migrations/20260614000000_create_user_refresh_tokens.php
public function up(): void
{
    $this->db->query("
        CREATE TABLE user_refresh_tokens (
            id         INT PRIMARY KEY AUTO_INCREMENT,
            user_id    INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            revoked_at TIMESTAMP NULL,
            UNIQUE KEY uq_token_hash (token_hash),
            KEY idx_user_id (user_id),
            KEY idx_expires_at (expires_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}
```

---

### Step 3: `JwtService`

Implement `App\Services\JwtService`:

```php
declare(strict_types=1);

namespace App\Services;

use App\Core\JWT;
use App\Core\Exceptions\HttpException;
use App\Models\User;
use App\Models\UserRefreshToken;

class JwtService
{
    public function __construct(
        private readonly JWT              $jwt,
        private readonly User             $userModel,
        private readonly UserRefreshToken $tokenModel,
        private readonly AuthorizationService $authz
    ) {}

    public function issueTokens(array $user): array
    {
        $permissions = $this->authz->getUserPermissions($user['id']);
        $roles       = $this->authz->getUserRoles($user['id']);

        $accessToken  = $this->jwt->encode([
            'sub'         => $user['id'],
            'email'       => $user['email'],
            'roles'       => $roles,
            'permissions' => $permissions,
        ], config('jwt.access_ttl'));

        $rawRefresh  = bin2hex(random_bytes(64));
        $tokenHash   = hash('sha256', $rawRefresh);
        $expiresAt   = date('Y-m-d H:i:s', time() + config('jwt.refresh_ttl'));

        $this->tokenModel->store($user['id'], $tokenHash, $expiresAt);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefresh,
            'expires_in'    => config('jwt.access_ttl'),
        ];
    }

    public function refreshAccessToken(string $rawRefreshToken): array
    {
        $tokenHash = hash('sha256', $rawRefreshToken);
        $record    = $this->tokenModel->findValid($tokenHash);

        if (!$record) {
            throw new HttpException(401, 'Invalid or expired refresh token');
        }

        $user = $this->userModel->findById($record['user_id']);
        if (!$user || $user['status'] !== 'active') {
            throw new HttpException(401, 'User account is inactive');
        }

        // Rotate: revoke old, issue new
        $this->tokenModel->revoke($tokenHash);
        return $this->issueTokens($user);
    }

    public function revokeRefreshToken(string $rawRefreshToken): void
    {
        $tokenHash = hash('sha256', $rawRefreshToken);
        $this->tokenModel->revoke($tokenHash);
    }

    public function setRefreshCookie(string $rawRefreshToken): void
    {
        setcookie(
            config('jwt.refresh_cookie'),
            $rawRefreshToken,
            [
                'expires'  => time() + config('jwt.refresh_ttl'),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => env('APP_ENV') === 'production',
            ]
        );
    }

    public function clearRefreshCookie(): void
    {
        setcookie(config('jwt.refresh_cookie'), '', ['expires' => time() - 3600, 'path' => '/']);
    }

    public function getRefreshTokenFromCookie(): ?string
    {
        return $_COOKIE[config('jwt.refresh_cookie')] ?? null;
    }
}
```

---

### Step 4: `UserRefreshToken` Model

```php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class UserRefreshToken
{
    public function __construct(private readonly Database $db) {}

    public function store(int $userId, string $tokenHash, string $expiresAt): void
    {
        $this->db->query(
            'INSERT INTO user_refresh_tokens (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)',
            ['uid' => $userId, 'hash' => $tokenHash, 'exp' => $expiresAt]
        );
    }

    public function findValid(string $tokenHash): ?array
    {
        return $this->db->query(
            'SELECT * FROM user_refresh_tokens
             WHERE token_hash = :hash AND revoked_at IS NULL AND expires_at > NOW()
             LIMIT 1',
            ['hash' => $tokenHash]
        )->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function revoke(string $tokenHash): void
    {
        $this->db->query(
            'UPDATE user_refresh_tokens SET revoked_at = NOW() WHERE token_hash = :hash',
            ['hash' => $tokenHash]
        );
    }

    public function pruneExpired(): void
    {
        $this->db->query('DELETE FROM user_refresh_tokens WHERE expires_at < NOW()');
    }
}
```

---

### Step 5: Middleware

#### `CorsMiddleware`

```php
declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        $origin  = $request->server('HTTP_ORIGIN', '');
        $allowed = config('cors.allowed_origins', []);

        if (in_array($origin, $allowed, true) || in_array('*', $allowed, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Vary: Origin');
            if (config('cors.allow_credentials', false)) {
                header('Access-Control-Allow-Credentials: true');
            }
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-Id');
            header('Access-Control-Max-Age: ' . config('cors.max_age', 86400));
        }

        if ($request->method() === 'OPTIONS') {
            return (new Response())->json([], 204);
        }

        return $next($request);
    }
}
```

#### `JwtMiddleware`

```php
declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Exceptions\HttpException;

class JwtMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        $token = $request->bearerToken();
        if (!$token) {
            throw new HttpException(401, 'Authentication required');
        }
        $payload = app_jwt()->decode($token);
        $request->setAuthPayload($payload);
        return $next($request);
    }
}
```

Add `bearerToken()` and `setAuthPayload()`/`authPayload()` to `App\Core\Request`.

#### `PermissionMiddleware`

```php
declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Exceptions\HttpException;

class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission) {}

    public static function for(string $permission): self
    {
        return new self($permission);
    }

    public function handle(Request $request, callable $next): mixed
    {
        $payload     = $request->authPayload() ?? [];
        $permissions = $payload['permissions'] ?? [];
        if (!in_array($this->permission, $permissions, true)) {
            throw new HttpException(403, 'You do not have permission to perform this action');
        }
        return $next($request);
    }
}
```

---

### Step 6: `Auth\AuthController`

```php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Exceptions\HttpException;
use App\Models\User;
use App\Services\JwtService;
use App\Services\LoginThrottleService;
use App\Services\SecurityLogger;

class AuthController extends Controller
{
    public function __construct(
        private readonly User                 $userModel,
        private readonly JwtService           $jwtService,
        private readonly LoginThrottleService $throttle,
        private readonly SecurityLogger       $secLogger
    ) {}

    public function login(Request $request, Response $response): Response
    {
        $validator = new Validator($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $response->json(['ok' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $ip  = $request->ip();
        $key = 'login:' . $ip;

        if ($this->throttle->isThrottled($key)) {
            $remaining = $this->throttle->remainingSeconds($key);
            return $response->json([
                'ok'      => false,
                'message' => "Too many login attempts. Try again in " . ceil($remaining / 60) . " minute(s).",
                'data'    => ['retry_after' => $remaining],
            ], 429);
        }

        $data = $validator->validated();
        $user = $this->userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password_hash']) || $user['status'] !== 'active') {
            $this->throttle->recordAttempt($key);
            $this->secLogger->logLoginFailed($data['email'], $ip);
            return $response->json(['ok' => false, 'message' => 'Invalid email or password.'], 401);
        }

        $this->throttle->clearAttempts($key);
        $this->userModel->touchLastLoginAt($user['id']);
        $this->secLogger->logLogin($user['id'], $ip, $request->userAgent());

        $tokens = $this->jwtService->issueTokens($user);
        $this->jwtService->setRefreshCookie($tokens['refresh_token']);

        return $response->json([
            'ok'      => true,
            'message' => 'Login successful.',
            'data'    => [
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
                'user'         => $this->formatUser($user, $request->authPayload()),
            ],
        ]);
    }

    public function refresh(Request $request, Response $response): Response
    {
        $rawToken = $this->jwtService->getRefreshTokenFromCookie();
        if (!$rawToken) {
            throw new HttpException(401, 'No refresh token provided');
        }
        $tokens = $this->jwtService->refreshAccessToken($rawToken);
        $this->jwtService->setRefreshCookie($tokens['refresh_token']);

        return $response->json([
            'ok'   => true,
            'data' => [
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
            ],
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $rawToken = $this->jwtService->getRefreshTokenFromCookie();
        if ($rawToken) {
            $this->jwtService->revokeRefreshToken($rawToken);
        }
        $this->jwtService->clearRefreshCookie();
        $payload = $request->authPayload();
        if ($payload) {
            $this->secLogger->logLogout($payload['sub']);
        }
        return $response->json(['ok' => true, 'message' => 'Logged out successfully.']);
    }

    public function me(Request $request, Response $response): Response
    {
        $payload = $request->authPayload();
        $user    = (new User(app_database()))->findById($payload['sub']);
        if (!$user) {
            throw new HttpException(404, 'User not found');
        }
        return $response->json([
            'ok'   => true,
            'data' => $this->formatUser($user, $payload),
        ]);
    }

    private function formatUser(array $user, ?array $payload): array
    {
        return [
            'id'            => $user['id'],
            'email'         => $user['email'],
            'first_name'    => $user['first_name'],
            'last_name'     => $user['last_name'],
            'roles'         => $payload['roles'] ?? [],
            'permissions'   => $payload['permissions'] ?? [],
            'last_login_at' => $user['last_login_at'],
        ];
    }
}
```

---

### Step 7: Public API Controllers

For each public API controller (under `app/Controllers/Api/`), follow this pattern:

```php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Exceptions\HttpException;
use App\Repositories\GalleryRepository;

class GalleryController extends Controller
{
    public function __construct(private readonly GalleryRepository $repo) {}

    public function index(Request $request, Response $response): Response
    {
        $filters = [
            'category' => $request->query('category'),
            'featured' => $request->query('featured') === 'true' ? true : null,
        ];
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 12)));

        $result = $this->repo->publishedWithCover($filters, $page, $perPage);

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
    }

    public function show(Request $request, Response $response, array $params): Response
    {
        $gallery = $this->repo->findPublishedBySlugWithMedia($params['slug']);
        if (!$gallery) {
            throw new HttpException(404, 'Gallery not found');
        }
        return $response->json(['ok' => true, 'data' => $gallery]);
    }
}
```

**Create controllers for:**
- `Api\GalleryController` (index, categories, show)
- `Api\ServiceController` (index, show)
- `Api\TestimonialController` (index)
- `Api\BlogController` (index, show, search, categories, byCategory, tags, byTag)
- `Api\PageController` (show)
- `Api\HeroSlideController` (index)
- `Api\SettingController` (public)
- `Api\ContactController` (store)
- `Api\BookingController` (store)
- `Api\SeoController` (sitemap)
- `Api\HealthController` (index)

---

### Step 8: Admin API Controllers

For each admin controller (under `app/Controllers/Admin/`), follow this pattern:

```php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Exceptions\HttpException;
use App\Services\GalleryService;
use App\Models\Gallery;

class GalleryController extends Controller
{
    public function __construct(
        private readonly GalleryService $service,
        private readonly Gallery        $model
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(100, (int) $request->query('per_page', 20));
        $filters = [
            'status'   => $request->query('status'),
            'q'        => $request->query('q'),
            'category' => $request->query('category'),
        ];

        $result = $this->model->adminIndex($filters, $page, $perPage);

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
    }

    public function store(Request $request, Response $response): Response
    {
        $validator = new Validator($request->all(), [
            'title'          => 'required|min:2|max:255',
            'slug'           => 'nullable|max:255',
            'description'    => 'nullable|max:5000',
            'cover_media_id' => 'nullable',
            'status'         => 'required|in:draft,published',
            'is_featured'    => 'nullable',
            'sort_order'     => 'nullable',
        ]);

        if ($validator->fails()) {
            return $response->json([
                'ok'     => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $request->authPayload();
        $id      = $this->service->create($validator->validated(), $request->input('category_ids', []), $request->input('seo', []), $payload['sub']);

        $gallery = $this->model->findById($id);

        return $response->json([
            'ok'      => true,
            'message' => 'Gallery created successfully.',
            'data'    => $gallery,
        ], 201);
    }

    public function update(Request $request, Response $response, array $params): Response
    {
        $gallery = $this->model->findById((int) $params['id']);
        if (!$gallery) {
            throw new HttpException(404, 'Gallery not found');
        }

        $validator = new Validator($request->all(), [
            'title'  => 'required|min:2|max:255',
            'status' => 'required|in:draft,published',
        ]);

        if ($validator->fails()) {
            return $response->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        $this->service->update((int) $params['id'], $validator->validated(), $request->input('category_ids', []), $request->input('seo', []));

        return $response->json(['ok' => true, 'message' => 'Gallery updated successfully.']);
    }

    public function destroy(Request $request, Response $response, array $params): Response
    {
        $gallery = $this->model->findById((int) $params['id']);
        if (!$gallery) {
            throw new HttpException(404, 'Gallery not found');
        }
        $this->service->softDelete((int) $params['id']);
        return $response->json(['ok' => true, 'message' => 'Gallery deleted.']);
    }
}
```

**Create admin controllers for all resource types** listed in `routes/admin-api.php` in `docs/04-backend-architecture.md`.

---

### Step 9: Route Registration

Register all routes as shown in `docs/04-backend-architecture.md` Section 6. Key points:

1. Register `CorsMiddleware` globally in `Application::run()` BEFORE routing
2. Load route files in order: `auth` → `admin-api` → `api`
3. Admin routes use `[JwtMiddleware::class]` + `PermissionMiddleware::for('...')`
4. Public routes have no middleware (CORS is global)
5. The `Application` must NOT load `routes/web.php` (PHP template routes) in API mode, OR convert all web route handlers to JSON responses

---

### Step 10: Error Handler Update

Update `ErrorHandler` to return JSON for all API requests:

```php
private function buildResponse(\Throwable $e): Response
{
    $status  = $e instanceof HttpException ? $e->getStatusCode() : 500;
    $message = $this->resolveMessage($e, $status);
    $isApi   = str_starts_with($this->request->path(), '/api/');

    if ($isApi || $this->request->expectsJson()) {
        return (new Response())->json([
            'ok'      => false,
            'message' => $message,
            'errors'  => [],
            'data'    => null,
            'meta'    => ['status' => $status],
        ], $status);
    }

    // Fall back to HTML error page for non-API routes
    // ...
}
```

---

### Step 11: Application Bootstrap Update

Add CORS as a globally-applied middleware in `Application::run()`:

```php
public function run(): void
{
    // Apply CORS to every request
    $corsMiddleware = new CorsMiddleware();

    try {
        $baseHandler = fn(Request $req) => $this->router->dispatch($req);
        $handler     = fn(Request $req) => $corsMiddleware->handle($req, $baseHandler);
        $handler($this->request)->send();
    } catch (\Throwable $e) {
        $this->errorHandler->handle($e)->send();
    }
}
```

---

## Controller Completion Checklist

For every API endpoint in `docs/06-api-design.md`:

- [ ] Route registered in the correct route file
- [ ] Correct HTTP method
- [ ] Auth/permission middleware applied if protected
- [ ] Input validation with correct rules
- [ ] Delegates business logic to service layer (no SQL in controllers)
- [ ] Returns correct HTTP status code
- [ ] Uses standard JSON envelope
- [ ] Error cases handled (`404`, `422`, `403`)
- [ ] `deleted_at IS NULL` in all queries (soft-delete filtering)
- [ ] Pagination applied to all list endpoints

---

## Testing Your Implementation

### Manual Testing with cURL

```bash
# 1. Login
curl -c cookies.txt -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@meshphoto.com","password":"password123"}' | jq

# 2. Get public galleries
curl http://localhost:8000/api/v1/galleries | jq

# 3. Get admin galleries (with token from step 1)
TOKEN="your_access_token_here"
curl http://localhost:8000/api/v1/admin/galleries \
  -H "Authorization: Bearer $TOKEN" | jq

# 4. Refresh token
curl -b cookies.txt -X POST http://localhost:8000/api/v1/auth/refresh | jq

# 5. Submit contact form
curl -X POST http://localhost:8000/api/v1/contact \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","message":"Hello there from the test"}' | jq
```

### Expected Responses

All responses must:
1. Have `Content-Type: application/json` header
2. Have CORS headers when `Origin` header is present
3. Use the `{ ok, message, data, errors, meta }` envelope
4. Match the schemas in `docs/06-api-design.md`

---

## Security Checklist

Before considering any endpoint complete:

- [ ] JWT signature verified (not just decoded)
- [ ] JWT expiry checked
- [ ] Permission checked for all `/admin/` routes
- [ ] All input validated before use
- [ ] All SQL via PDO prepared statements
- [ ] No `$_GET`/`$_POST` accessed directly (use `$request->input()` / `$request->query()`)
- [ ] Upload MIME detected via `finfo_file()` (not client Content-Type)
- [ ] Upload extension whitelist enforced
- [ ] No passwords or tokens in log output
- [ ] `APP_DEBUG=false` in production hides exception details

---

## Coding Standards (Mandatory)

```php
// Every PHP file must start with:
declare(strict_types=1);

// All properties and methods must have visibility
// All method parameters must have type declarations
// All return types must be declared

// Good:
public function findBySlug(string $slug): ?array { ... }

// Bad:
public function findBySlug($slug) { ... }

// No direct SQL in controllers:
// Bad:
$db->query('SELECT * FROM galleries WHERE id = ' . $id); // injection risk
$db->query('SELECT * FROM galleries WHERE id = :id', ['id' => $id]); // good

// Constructor injection for all dependencies:
public function __construct(
    private readonly GalleryService    $service,
    private readonly GalleryRepository $repo
) {}
```

---

## Common Pitfalls to Avoid

1. **Forgetting `deleted_at IS NULL`** — Every query on soft-deletable tables must filter this
2. **Returning PHP session errors on API routes** — Disable session start for pure API routes or ensure session errors don't reach the response
3. **CORS headers on error responses** — Error handler must also apply CORS headers
4. **Token in response body AND cookie** — Refresh token goes in `httpOnly` cookie ONLY, not in response body
5. **Missing pagination on list endpoints** — All admin list endpoints must accept `?page` and `?per_page`
6. **Slug uniqueness** — Always check slug uniqueness before insert/update; exclude current record ID on update
7. **Media hard delete without usage check** — Always call `MediaUsageService::isInUse()` before hard delete
8. **Empty `errors` object on non-422 errors** — Include `"errors": {}` in all error envelopes for consistency
