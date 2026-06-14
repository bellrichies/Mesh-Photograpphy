# Mesh Photography — Master Technical Blueprint

> **Status:** Implementation-aligned technical reference  
> **Last Updated:** 2026-06-14  
> **Scope:** Full-stack PHP MVC — backend architecture, frontend implementation, database design, deployment, and operational guidance

---

## Table of Contents

1. [Project Vision and Positioning](#1-project-vision-and-positioning)
2. [Technology Stack](#2-technology-stack)
3. [Directory Structure](#3-directory-structure)
4. [Application Bootstrap and Lifecycle](#4-application-bootstrap-and-lifecycle)
5. [Core Framework](#5-core-framework)
6. [Routing](#6-routing)
7. [Middleware](#7-middleware)
8. [Authentication and Session Management](#8-authentication-and-session-management)
9. [Authorization and Roles](#9-authorization-and-roles)
10. [Database Layer](#10-database-layer)
11. [Database Schema](#11-database-schema)
12. [Models](#12-models)
13. [Repositories](#13-repositories)
14. [Services](#14-services)
15. [Controllers](#15-controllers)
16. [Validation](#16-validation)
17. [Error Handling and Logging](#17-error-handling-and-logging)
18. [Media Upload and Storage](#18-media-upload-and-storage)
19. [Settings and Caching](#19-settings-and-caching)
20. [View System and Templating](#20-view-system-and-templating)
21. [Frontend Architecture](#21-frontend-architecture)
22. [Public Website Pages](#22-public-website-pages)
23. [Admin Panel](#23-admin-panel)
24. [Email System](#24-email-system)
25. [SEO Strategy](#25-seo-strategy)
26. [Security Architecture](#26-security-architecture)
27. [Performance Strategy](#27-performance-strategy)
28. [Accessibility Strategy](#28-accessibility-strategy)
29. [Configuration Reference](#29-configuration-reference)
30. [Helper Functions Reference](#30-helper-functions-reference)
31. [Database Migrations and Seeders](#31-database-migrations-and-seeders)
32. [AJAX Workflows](#32-ajax-workflows)
33. [Coding Standards](#33-coding-standards)
34. [Testing Strategy](#34-testing-strategy)
35. [Deployment Blueprint](#35-deployment-blueprint)
36. [Post-Launch Maintenance](#36-post-launch-maintenance)
37. [Development Roadmap](#37-development-roadmap)
38. [Definition of Done](#38-definition-of-done)

---

## 1. Project Vision and Positioning

**Mesh Photography** is a premium, full-stack portfolio and content management platform built for professional photographers. It serves two audiences simultaneously:

- **Public visitors** — a polished, brand-forward marketing website for showcasing photography work, services, and blog content
- **Studio administrators** — a private CMS dashboard for managing all content without technical knowledge

### Brand Positioning

The platform targets high-end clientele (weddings, portraits, commercial). Every visual and UX decision reinforces:

- Elegance over complexity
- Whitespace, typography, and imagery over dense content
- Trustworthiness and professionalism

### Core Capabilities

| Capability | Status |
|---|---|
| Custom PHP MVC framework (no Laravel/Symfony) | Implemented |
| Role-based admin CMS dashboard | Implemented |
| Portfolio gallery management | Implemented |
| Blog with categories, tags, and revisions | Implemented |
| Service listings | Implemented |
| Testimonials management | Implemented |
| Contact form with CRM-style inquiry tracking | Implemented |
| Booking request workflow | Implemented |
| Media library with thumbnails | Implemented |
| Homepage hero carousel | Implemented |
| Reusable content blocks | Implemented |
| Flexible CMS pages with sections | Implemented |
| SEO metadata management | Implemented |
| Sitemap and robots.txt generation | Implemented |
| Role-based access control | Implemented |
| Session security with fingerprinting | Implemented |
| Structured error logging | Implemented |
| Settings with file-based cache | Implemented |
| Database migrations and seeders | Implemented |
| REST API endpoints | Planned |
| Full user management UI | Partially implemented (view-only) |
| Two-factor authentication | Planned |
| Email notification system | Partially implemented |

---

## 2. Technology Stack

### Backend

| Component | Choice | Version |
|---|---|---|
| Language | PHP | 8.2+ |
| Architecture | Custom OOP MVC | — |
| Environment | vlucas/phpdotenv | ^5.6 |
| Mail | PHPMailer | ^6.10 |
| Database | MySQL / MariaDB | 8.0+ / 10.5+ |
| PDO Adapter | PHP PDO (MySQL) | — |
| Autoloading | Composer PSR-4 | — |

### Frontend

| Component | Choice | Version |
|---|---|---|
| CSS Framework | Tailwind CSS | 3.4.19 |
| JavaScript | Vanilla JS (ES6+) | — |
| Display Typeface | Cormorant Garamond | Google Fonts |
| Body Typeface | Inter | Google Fonts |
| Build Tool | npm + Tailwind CLI | — |

### Infrastructure

| Component | Choice |
|---|---|
| Web Server | Apache (XAMPP for dev) |
| Session Storage | File-based (`storage/sessions/`) |
| Asset Storage | Local filesystem (`public/uploads/`) |
| Cache | File-based PHP (`storage/cache/`) |
| Logs | File-based JSON (`storage/logs/`) |

---

## 3. Directory Structure

```
mesh/
├── app/                              # All application code
│   ├── Controllers/
│   │   ├── Admin/                    # 17 admin controllers
│   │   ├── Auth/                     # AuthController.php
│   │   └── Web/                      # 9 public controllers
│   ├── Core/                         # Custom MVC framework
│   │   ├── Application.php           # App entry point and bootstrap
│   │   ├── Auth.php                  # Session-based authentication
│   │   ├── Config.php                # Singleton config loader
│   │   ├── Controller.php            # Base controller class
│   │   ├── CSRF.php                  # Token generation and verification
│   │   ├── Database.php              # PDO wrapper
│   │   ├── ErrorHandler.php          # Exception handling, logging
│   │   ├── Request.php               # HTTP request abstraction
│   │   ├── Response.php              # HTTP response builder
│   │   ├── Router.php + RouteDefinition # Route compilation and dispatch
│   │   ├── Session.php               # Session management + fingerprinting
│   │   ├── Validator.php             # Input validation engine
│   │   ├── View.php                  # PHP template renderer
│   │   ├── Exceptions/
│   │   │   └── HttpException.php     # HTTP-aware exception class
│   │   └── Middleware/
│   │       ├── MiddlewareInterface.php
│   │       ├── AuthMiddleware.php
│   │       ├── GuestMiddleware.php
│   │       └── PermissionMiddleware.php
│   ├── Models/                       # 31 data-access classes
│   ├── Repositories/                 # 3 complex-query encapsulations
│   ├── Services/                     # 20 business-logic classes
│   ├── Policies/
│   │   └── AdminPolicy.php
│   ├── Helpers/
│   │   └── UploadPathHelper.php
│   └── Validators/                   # Domain-specific validator classes
├── bootstrap/
│   ├── app.php                       # bootstrapApplication() function
│   └── helpers.php                   # 50+ global helper functions
├── config/
│   ├── app.php                       # Application settings
│   ├── database.php                  # Database connections
│   ├── mail.php                      # Mail transport config
│   ├── permissions.php               # Role and permission definitions
│   ├── session.php                   # Session configuration
│   └── uploads.php                   # File upload constraints
├── database/
│   ├── console.php                   # CLI migration/seed runner
│   ├── migrations/                   # 36 ordered migration files
│   └── seeders/                      # 8 seeder classes
├── docs/
│   └── blueprint.md                  # This document
├── public/
│   ├── index.php                     # Single entry point
│   └── assets/
│       ├── css/
│       │   └── tailwind.css          # Compiled Tailwind output
│       └── js/                       # Compiled JavaScript modules
├── resources/
│   ├── css/
│   │   └── tailwind.css              # Tailwind source file
│   └── views/
│       ├── layouts/                  # Base HTML layouts
│       │   ├── main.php              # Public website layout
│       │   ├── admin.php             # Admin dashboard layout
│       │   └── auth.php              # Login page layout
│       ├── admin/                    # Admin interface templates
│       ├── auth/                     # Auth page templates
│       ├── web/                      # Public page templates
│       ├── components/               # Reusable PHP view partials
│       ├── emails/                   # Transactional email templates
│       └── errors/                   # Error page templates (404, 500)
├── routes/
│   ├── auth.php                      # Login, logout routes
│   ├── admin.php                     # Protected admin routes (126)
│   ├── web.php                       # Public website routes (21)
│   └── api.php                       # API routes (planned, empty)
├── storage/
│   ├── cache/                        # settings.php file cache
│   ├── logs/                         # app-YYYY-MM-DD.log (JSON)
│   ├── sessions/                     # PHP session files
│   └── uploads/                      # (symlink or alias; actual: public/uploads)
├── vendor/                           # Composer-managed dependencies
├── node_modules/                     # npm dependencies
├── .env                              # Active environment file (not committed)
├── .env.example                      # Environment variable template
├── composer.json                     # PHP dependency manifest
├── package.json                      # npm scripts and dependencies
└── tailwind.config.js                # Tailwind customization
```

---

## 4. Application Bootstrap and Lifecycle

### Entry Point

`public/index.php` is the single entry point for all HTTP requests. Apache's `mod_rewrite` routes every request here.

```php
// public/index.php
$basePath = dirname(__DIR__);
$app = bootstrapApplication($basePath);
$app->run();
```

### Bootstrap Sequence (`bootstrap/app.php`)

`bootstrapApplication(string $basePath): Application` performs the following in order:

1. **Autoloader** — `require vendor/autoload.php` (Composer PSR-4)
2. **Helpers** — `require bootstrap/helpers.php` (50+ global functions)
3. **Environment guard** — throws `RuntimeException` if `.env` file is missing
4. **Dotenv load** — `Dotenv::createImmutable($basePath)->safeLoad()` populates `$_ENV` / `$_SERVER`
5. **Required key validation** — `validateRequiredEnvKeys([...])` throws if any mandatory key is absent

   Required keys: `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `SESSION_DRIVER`, `SESSION_LIFETIME`, `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM_ADDRESS`, `ADMIN_PATH`, `CSRF_TOKEN_NAME`

6. **Config load** — `Config::load($basePath . '/config')` reads all `config/*.php` files
7. **Error reporting** — `error_reporting(E_ALL)`, `display_errors` based on `APP_DEBUG`
8. **Timezone** — `date_default_timezone_set(config('app.timezone'))`
9. **Application** — returns `new Application($basePath)`

### Request Lifecycle (`Application.php`)

```
new Application($basePath)
  → new Router()
  → new Request()
  → new View($basePath)
  → new ErrorHandler($basePath, $request)
  → errorHandler->registerShutdownHandler()
  → loadRoutes()          # Includes auth, admin, api, web route files
      Each file returns a callable(Router $router): void
$app->run()
  → try { router->dispatch(request)->send() }
    catch Throwable { errorHandler->handle(exception)->send() }
```

Route loading order: `auth` → `admin` → `api` → `web`. This matters: more specific routes (auth prefix) are registered before the catch-all `/{slug}` web route.

---

## 5. Core Framework

### Config (`App\Core\Config`)

- **Singleton** loaded once at bootstrap
- Reads every `*.php` file in `config/` and stores it keyed by filename without extension
- Dot-notation access: `Config::get('database.connections.mysql.host')`
- `config($key, $default)` helper wraps `Config::get()`

### Request (`App\Core\Request`)

- Wraps `$_SERVER`, `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`
- `method()`: returns normalized HTTP method string (`GET`, `POST`, etc.)
- `path()`: resolves request path relative to application base path
- `input(key, default)`: reads POST data
- `query(key, default)`: reads GET data
- `file(key)`: returns `$_FILES[key]`
- `server(key, default)`: reads `$_SERVER`
- `ip()`: client IP address
- `userAgent()`: `HTTP_USER_AGENT`
- `expectsJson()`: true if `Accept: application/json` or `X-Requested-With: XMLHttpRequest`
- `isAdminRequest()`: true if path starts with the configured admin prefix
- `setRouteParams(array)` / `routeParams()`: injected by router after match

### Response (`App\Core\Response`)

- Builder pattern — methods return `$this` for chaining
- `html(string $content, int $status = 200)`: sets HTML body
- `json(array $data, int $status = 200)`: encodes JSON, sets `Content-Type: application/json`
- `redirect(string $url, int $status = 302)`: issues HTTP redirect
- `setHeader(string $name, string $value)`: appends response header
- `send()`: emits all headers and body — called once at end of lifecycle

### View (`App\Core\View`)

- PHP-native template engine using `extract()` + `include()`
- `render(string $view, array $data = [], string $layout = '')`: renders a view, optionally wrapped in a layout
- `partial(string $view, array $data = [])`: renders a view without a layout, returns string
- Dot-notation paths: `'admin.pages.form'` → `resources/views/admin/pages/form.php`
- Layout receives `$content` as the inner rendered HTML
- `e(string $value)`: escapes for HTML output via `htmlspecialchars()`

### Validator (`App\Core\Validator`)

- `make(array $data, array $rules)`: instantiate and run
- Built-in rules: `required`, `email`, `min:N`, `max:N`, `date`, `in:a,b,c`, `nullable`
- Custom callable rules: `fn($value) => true|'error message'`
- `errors()`: returns `['field' => 'first error message']`
- `validated()`: returns only the subset of input that passed rules
- `passes()` / `fails()`: boolean check

### CSRF (`App\Core\CSRF`)

- Token stored in session under the configured key (`CSRF_TOKEN_NAME`, default `_token`)
- `generate()`: creates a 32-byte random hex token and stores in session
- `verify(string $token)`: timing-safe `hash_equals()` comparison
- `inputField()`: returns `<input type="hidden" name="_token" value="...">` HTML
- Token is rotated on each login/logout (session regeneration)

### Database (`App\Core\Database`)

- Thin PDO wrapper
- Constructor receives config array (`host`, `port`, `database`, `username`, `password`, `charset`, `collation`)
- DSN format: `mysql:host=...;port=...;dbname=...;charset=...`
- `ATTR_EMULATE_PREPARES = false` — uses true server-side prepared statements
- `ATTR_ERRMODE = ERRMODE_EXCEPTION` — throws `PDOException` on error
- `query(string $sql, array $params = []): PDOStatement`: prepares and executes
- `lastInsertId()`: wraps `PDO::lastInsertId()`
- Singleton provided via `app_database()` helper

---

## 6. Routing

### Route Files

Routes are defined in four files under `routes/`. Each file returns a `callable(Router $router): void`. The `Application` loads them in this fixed order: `auth`, `admin`, `api`, `web`.

#### `routes/auth.php` — Authentication Routes

| Method | Path | Handler | Middleware |
|---|---|---|---|
| GET | `/login` | Redirects to `/{adminPrefix}/login` | `GuestMiddleware` |
| POST | `/login` | Redirects to `/{adminPrefix}/login` | `GuestMiddleware` |
| GET | `/{adminPrefix}/login` | `AuthController@showLogin` | `GuestMiddleware` |
| POST | `/{adminPrefix}/login` | `AuthController@login` | `GuestMiddleware` |
| POST | `/logout` | Redirects to `/{adminPrefix}/logout` | `AuthMiddleware` |
| POST | `/{adminPrefix}/logout` | `AuthController@logout` | `AuthMiddleware` |

#### `routes/web.php` — Public Website Routes (21 routes)

| Method | Path | Handler |
|---|---|---|
| GET | `/sitemap.xml` | `SeoController@sitemap` |
| GET | `/robots.txt` | `SeoController@robots` |
| GET | `/` | `HomeController@index` |
| GET | `/portfolio` | `PortfolioController@index` |
| GET | `/portfolio/category/{slug}` | `PortfolioController@category` |
| GET | `/portfolio/{slug}` | `PortfolioController@show` |
| GET | `/services` | `ServiceController@index` |
| GET | `/services/{slug}` | `ServiceController@show` |
| GET | `/testimonials` | `TestimonialController@index` |
| GET | `/contact` | `ContactController@show` |
| POST | `/contact` | `ContactController@store` |
| GET | `/booking` | `BookingController@show` |
| POST | `/booking` | `BookingController@store` |
| GET | `/blog` | `BlogController@index` |
| GET | `/blog/search` | `BlogController@search` |
| GET | `/blog/category/{slug}` | `BlogController@category` |
| GET | `/blog/tag/{slug}` | `BlogController@tag` |
| GET | `/blog/{slug}` | `BlogController@show` |
| GET | `/terms-of-service` | 301 redirect to `/terms` |
| GET | `/{slug}` | `PageController@show` (catch-all) |

**Note:** The `/{slug}` catch-all is registered last and only matches if no earlier route matched. Routes for `/portfolio/{slug}`, `/services/{slug}`, `/blog/{slug}` are registered before it to take precedence.

#### `routes/admin.php` — Admin Panel Routes (126 routes)

All admin routes are dynamically prefixed with the value of `config('app.admin_path')` (default `/admin`, configurable via `ADMIN_PATH` env var). All routes require `AuthMiddleware`. Most require an additional `PermissionMiddleware`.

**Dashboard:**
- `GET /{prefix}` → redirect to `/dashboard` or `/login`
- `GET /{prefix}/dashboard` → inline closure (dashboard metrics)
- `GET /{prefix}/content` → Content Workspace overview

**Pages (`manage-pages`):**
- `GET /{prefix}/pages` — index
- `GET /{prefix}/pages/create` — create form
- `POST /{prefix}/pages/store` — save new
- `GET /{prefix}/pages/edit/{id}` — edit form
- `POST /{prefix}/pages/update/{id}` — save update
- `POST /{prefix}/pages/delete/{id}` — soft delete
- `GET /{prefix}/pages/slug` — slug availability check (AJAX)
- `GET /{prefix}/pages/{pageId}/sections` — section manager
- `POST /{prefix}/pages/{pageId}/sections/store` — add section
- `POST /{prefix}/pages/{pageId}/sections/update/{sectionId}` — edit section
- `POST /{prefix}/pages/{pageId}/sections/delete/{sectionId}` — remove section
- `POST /{prefix}/pages/{pageId}/sections/reorder/{sectionId}` — reorder
- `POST /{prefix}/pages/{pageId}/sections/status/{sectionId}` — toggle visibility

**Reusable Blocks (`manage-pages`):**
- `GET, POST /{prefix}/blocks` — index, store
- `GET /{prefix}/blocks/create`, `GET/POST /{prefix}/blocks/edit/{id}`, `POST /{prefix}/blocks/update/{id}`, `POST /{prefix}/blocks/delete/{id}`

**Settings (`manage-settings`):**
- `GET /{prefix}/settings` — settings form
- `POST /{prefix}/settings/update` — save settings

**Users (`manage-users`):**
- `GET /{prefix}/users` — user listing (view-only, inline closure)

**Media (`manage-media`):**
- `POST /{prefix}/media/upload` — file upload
- `POST /{prefix}/media/archive` — soft archive
- `GET /{prefix}/media` — media library index
- `GET /{prefix}/media/search` — AJAX search
- `POST /{prefix}/media/update` — update alt text / title
- `POST /{prefix}/media/delete` — hard delete
- `GET /{prefix}/media/picker` — media picker modal

**Galleries (`manage-galleries`):**
- Full CRUD: index, create, store, edit/{id}, update/{id}, delete/{id}, slug
- Media management: attach, update, reorder, remove

**Gallery Categories (`manage-galleries`):**
- `GET /{prefix}/gallery-categories` — index
- `POST /{prefix}/gallery-categories/store` — create
- `POST /{prefix}/gallery-categories/update/{id}` — edit
- `POST /{prefix}/gallery-categories/delete/{id}` — delete
- `GET /{prefix}/gallery-categories/slug` — slug check

**Services (`manage-services`):**
- Full CRUD: index, create, store, edit/{id}, update/{id}, delete/{id}, slug

**Testimonials (`manage-testimonials`):**
- Full CRUD: index, create, store, edit/{id}, update/{id}, delete/{id}

**Hero Slides (`manage-pages`):**
- Full CRUD: index, create, store, edit/{id}, update/{id}, delete/{id}

**Blog Posts (`manage-blog`):**
- Full CRUD + slug check
- Media operations: attach, update, reorder, remove
- `POST /{prefix}/blog/posts/autosave/{id}` — draft autosave

**Blog Categories and Tags (`manage-blog`):**
- `GET /{prefix}/blog/categories` — index + inline CRUD via AJAX
- `POST /{prefix}/blog/categories/store`, `update/{id}`, `delete/{id}`, slug check
- `GET /{prefix}/blog/tags` — index
- `POST /{prefix}/blog/tags/store`, `update/{id}`, `delete/{id}`, slug check

**Inquiries (`manage-inquiries`):**
- `GET /{prefix}/inquiries` — list
- `GET /{prefix}/inquiries/filter` — AJAX filter
- `GET /{prefix}/inquiries/view/{id}` — detail
- `POST /{prefix}/inquiries/status/{id}` — update status
- `POST /{prefix}/inquiries/notes/{id}` — add note

**Bookings (`manage-inquiries`):**
- `GET /{prefix}/bookings` — list
- `GET /{prefix}/bookings/view/{id}` — detail
- `POST /{prefix}/bookings/status/{id}` — update status

### Router Implementation (`App\Core\Router`)

- Routes are registered as flat arrays with compiled regex patterns
- Path parameters use `{name}` syntax; compiled to named capture groups `(?P<name>[^/]+)`
- `dispatch(Request $request): Response`: iterates routes, matches method + regex
- Route handlers support three forms:
  1. Callable/closure: `function(Request $request, Response $response, array $params): mixed`
  2. String `'ClassName@methodName'`
  3. Array `[ClassName::class, 'methodName']`
- Middleware is applied as a chain (reversed, onion model): outermost middleware wraps innermost handler
- `RouteDefinition` returned by `add()` allows fluent `->middleware([...])` chaining
- Throws `HttpException(404)` if no route matches

---

## 7. Middleware

All middleware implements `App\Core\Middleware\MiddlewareInterface`:

```php
interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed;
}
```

### AuthMiddleware

- Calls `app_auth()->check()`
- On failure: pulls `pullSecurityFailureReason()` and sets flash message, redirects to login
- Failure reasons: `session_idle_timeout`, `session_fingerprint_mismatch`, `session_security_missing`

### GuestMiddleware

- Inverts auth check: if user is authenticated, redirects to admin dashboard
- Allows unauthenticated users to proceed
- Used on login form and related routes

### PermissionMiddleware

- Checks `can($permission)` for the required permission slug
- Static factory: `PermissionMiddleware::for('manage-pages')` returns a callable string
- On failure: returns 403 response or redirect with "permission denied" flash

---

## 8. Authentication and Session Management

### Session Management (`App\Core\Session`)

Session is initialized at bootstrap via the `app_session()` helper (singleton).

**Configuration applied before `session_start()`:**

| Setting | Config Key | Default |
|---|---|---|
| Driver | `session.driver` | `file` |
| Save path | `session.files_path` | `storage/sessions` |
| Cookie lifetime | `session.lifetime` | 120 minutes |
| Cookie path | `session.path` | `/` |
| Secure cookie | `session.secure` | `false` (dev), `true` (prod) |
| HttpOnly cookie | `session.http_only` | `true` |
| SameSite | `session.same_site` | `Lax` |

**Security context:** On every request, `Session::validateSecurityContext()` checks:
1. `_session_security` key exists in `$_SESSION`
2. **Fingerprint match** (SHA-256 hash of User-Agent + Accept-Language, optionally IP)
3. **Idle timeout** — time since `last_activity_at` does not exceed `SESSION_IDLE_TIMEOUT` minutes (default 60)

On pass: updates `last_activity_at` to current time.

**Flash data lifecycle:**
- `flash(key, value)`: writes to `_flash.new[key]`
- `ageFlashData()` (called on construction): moves `_flash.new` to `_flash.old`, clears `_flash.new`
- `getFlash(key)`: reads from `_flash.old[key]` — data available for exactly one request

### Authentication (`App\Core\Auth`)

Session key for user ID: `admin_user_id`

**Login flow:**
1. `session->regenerate()` — prevents session fixation
2. `session->set('admin_user_id', $userId)`
3. `session->refreshSecurityContext()` — writes fingerprint + timestamps

**Check flow (every protected request):**
1. `id()` — reads `$_SESSION['admin_user_id']`
2. `session->validateSecurityContext()` — fingerprint + idle checks
3. On failure: clears auth state, stores failure reason, returns `false`

**Logout flow:**
1. `session->forget('admin_user_id')`
2. `session->clearSecurityContext()`
3. `session->regenerate()`

**User resolver:**
```php
$auth->setUserResolver(function(int $id): ?array {
    return (new User(app_database()))->findById($id);
});
```
Set in `bootstrap/helpers.php`. Called by `auth->user()` and `auth->currentUser()`.

### Login Throttling (`App\Services\LoginThrottleService`)

- Key-based rate limiting (by IP or username)
- State stored in PHP session
- Configurable: max attempts, lockout duration
- Returns remaining lockout time for UI feedback

---

## 9. Authorization and Roles

### Configuration (`config/permissions.php`)

```php
return [
    'roles' => ['super-admin', 'editor', 'content-manager'],
    'abilities' => [
        'manage-users',
        'manage-settings',
        'manage-media',
        'manage-pages',
        'manage-galleries',
        'manage-blog',
        'manage-testimonials',
        'manage-inquiries',
    ],
];
```

Note: Admin routes for services use `manage-services` permission; this should be explicitly added to `config/permissions.php` as part of the next update.

### Database Tables

- `roles` (id, name, slug, description)
- `permissions` (id, name, slug, description)
- `role_user` (role_id, user_id) — pivot
- `permission_role` (permission_id, role_id) — pivot

### AuthorizationService (`App\Services\AuthorizationService`)

- `userHasRole(int $userId, string $roleSlug): bool` — queries `role_user` join `roles`
- `userHasPermission(int $userId, string $permissionSlug): bool` — queries `user → role → permission` chain

### Helper Functions

```php
can(string $permission): bool   // checks current user's permission
has_role(string $role): bool    // checks current user's role
```

Both return `false` if user is not authenticated.

### Permission → Route Mapping

| Permission | Protected Resources |
|---|---|
| `manage-users` | `/admin/users` |
| `manage-settings` | `/admin/settings` |
| `manage-media` | `/admin/media/*` |
| `manage-pages` | `/admin/pages/*`, `/admin/blocks/*`, `/admin/hero-slides/*` |
| `manage-galleries` | `/admin/galleries/*`, `/admin/gallery-categories/*` |
| `manage-blog` | `/admin/blog/*` |
| `manage-testimonials` | `/admin/testimonials/*` |
| `manage-inquiries` | `/admin/inquiries/*`, `/admin/bookings/*` |
| `manage-services` | `/admin/services/*` (permission defined in routes, add to config) |

---

## 10. Database Layer

### Connection (`App\Core\Database`)

Configured from environment variables:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mesh_photo
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### Query Pattern

All database interaction uses parameterized queries:

```php
$db->query(
    'SELECT * FROM pages WHERE slug = :slug AND deleted_at IS NULL',
    ['slug' => $slug]
)->fetch(PDO::FETCH_ASSOC);
```

### Soft Deletes

All content tables include a `deleted_at TIMESTAMP NULL` column. All queries filter `WHERE deleted_at IS NULL`. Hard deletes are used only for media records after archive confirmation.

---

## 11. Database Schema

### users

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `email` | VARCHAR(255) UNIQUE | Login identifier |
| `password_hash` | VARCHAR(255) | bcrypt via `password_hash()` |
| `first_name` | VARCHAR(100) | |
| `last_name` | VARCHAR(100) | |
| `status` | ENUM('active','inactive') | Default: `active` |
| `last_login_at` | TIMESTAMP NULL | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | Soft delete |

### roles / permissions

| Column | Type |
|---|---|
| `id` | INT PK |
| `name` | VARCHAR(100) |
| `slug` | VARCHAR(100) UNIQUE |
| `description` | TEXT NULL |

### role_user / permission_role

| Column | Type |
|---|---|
| `role_id` | INT FK |
| `user_id` | INT FK |

### settings

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `group` | VARCHAR(100) | Namespace for settings (e.g. `site`, `seo`, `contact`) |
| `key` | VARCHAR(100) | Setting identifier |
| `value` | TEXT NULL | Raw value |
| `label` | VARCHAR(255) NULL | Display label |
| `type` | VARCHAR(50) | `text`, `textarea`, `boolean`, `image`, etc. |
| `sort_order` | INT | Within-group ordering |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Composite unique constraint: `(group, key)`.

### pages

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `title` | VARCHAR(255) | |
| `slug` | VARCHAR(255) UNIQUE | URL path segment |
| `template` | VARCHAR(100) NULL | Template identifier |
| `status` | ENUM('draft','published') | |
| `parent_id` | INT NULL FK(pages) | Hierarchical nesting |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | Soft delete |

### page_sections

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `page_id` | INT FK(pages) | |
| `section_type` | VARCHAR(100) | Block/component type |
| `title` | VARCHAR(255) NULL | |
| `content` | LONGTEXT NULL | HTML or structured data |
| `settings` | JSON NULL | Section-specific config |
| `status` | ENUM('active','inactive') | |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### reusable_blocks

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `key` | VARCHAR(100) UNIQUE | Lookup key for `render_reusable_block()` |
| `label` | VARCHAR(255) | Admin display name |
| `block_type` | VARCHAR(100) | Determines render partial |
| `content` | LONGTEXT NULL | |
| `settings` | JSON NULL | |
| `status` | ENUM('draft','published') | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | |

### seo_meta

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `entity_type` | VARCHAR(100) | `page`, `blog_post`, `gallery`, etc. |
| `entity_id` | INT | FK to respective entity |
| `meta_title` | VARCHAR(255) NULL | |
| `meta_description` | TEXT NULL | |
| `og_title` | VARCHAR(255) NULL | Open Graph title |
| `og_description` | TEXT NULL | |
| `og_image_id` | INT NULL FK(media) | |
| `canonical_url` | VARCHAR(500) NULL | |
| `robots` | VARCHAR(100) NULL | `index,follow` etc. |
| `schema_markup` | LONGTEXT NULL | JSON-LD |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Unique constraint: `(entity_type, entity_id)`.

### media

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `uuid` | CHAR(36) UNIQUE | UUID v4, used in public references |
| `original_name` | VARCHAR(255) | Original filename |
| `stored_name` | VARCHAR(255) | `YYYYMMDDHHiiss-<20hex>.<ext>` |
| `directory` | VARCHAR(500) | Relative to doc root: `public/uploads/images/YYYY/MM` |
| `disk` | VARCHAR(50) | Default: `public` |
| `extension` | VARCHAR(20) | |
| `mime_type` | VARCHAR(100) | Verified via `finfo` |
| `file_type` | ENUM('image','video','document','other') | |
| `size_bytes` | BIGINT | |
| `width` | INT NULL | Images only |
| `height` | INT NULL | Images only |
| `duration_seconds` | INT NULL | Videos (future) |
| `alt_text` | VARCHAR(500) NULL | |
| `title` | VARCHAR(255) NULL | |
| `caption` | TEXT NULL | |
| `description` | TEXT NULL | |
| `checksum` | CHAR(64) NULL | SHA-256 of file content |
| `is_public` | TINYINT(1) | Default: 1 |
| `status` | ENUM('active','archived') | |
| `uploaded_by` | INT NULL FK(users) | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | Soft delete |

### media_variants

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `media_id` | INT FK(media) | |
| `variant_key` | VARCHAR(50) | Currently: `thumb` |
| `stored_name` | VARCHAR(255) | `<original-stem>-thumb.jpg` |
| `directory` | VARCHAR(500) | `public/uploads/variants/YYYY/MM` |
| `disk` | VARCHAR(50) | |
| `mime_type` | VARCHAR(100) | `image/jpeg` |
| `extension` | VARCHAR(20) | `jpg` |
| `size_bytes` | BIGINT | |
| `width` | INT NULL | 640px |
| `height` | INT NULL | Proportional |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

Unique constraint: `(media_id, variant_key)`.

### media_usage_map

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `media_id` | INT FK(media) | |
| `entity_type` | VARCHAR(100) | `gallery`, `blog_post`, `testimonial`, etc. |
| `entity_id` | INT | |
| `context` | VARCHAR(100) NULL | `cover`, `featured`, `gallery`, etc. |
| `created_at` | TIMESTAMP | |

### galleries

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `title` | VARCHAR(255) | |
| `slug` | VARCHAR(255) UNIQUE | |
| `description` | TEXT NULL | |
| `cover_media_id` | INT NULL FK(media) | |
| `status` | ENUM('draft','published') | |
| `is_featured` | TINYINT(1) | Default: 0 |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | |

### gallery_categories / gallery_category_map / gallery_media

| Column | Type |
|---|---|
| `gallery_categories.id` | INT PK |
| `gallery_categories.name` | VARCHAR(255) |
| `gallery_categories.slug` | VARCHAR(255) UNIQUE |
| `gallery_category_map` | (gallery_id, category_id) pivot |
| `gallery_media` | (gallery_id, media_id, sort_order, caption) |

### albums / album_media

| Column | Type |
|---|---|
| `albums.id` | INT PK |
| `albums.title` | VARCHAR(255) |
| `albums.gallery_id` | INT NULL FK(galleries) |
| `album_media` | (album_id, media_id, sort_order) pivot |

### hero_slides

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `title` | VARCHAR(255) | Overlay heading |
| `subtitle` | TEXT NULL | Overlay subtext |
| `media_id` | INT NULL FK(media) | Background image |
| `cta_label` | VARCHAR(100) NULL | Button text |
| `cta_url` | VARCHAR(500) NULL | Button link |
| `status` | ENUM('draft','published') | |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | |

### services

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `title` | VARCHAR(255) | |
| `slug` | VARCHAR(255) UNIQUE | |
| `short_description` | TEXT NULL | |
| `description` | LONGTEXT NULL | |
| `media_id` | INT NULL FK(media) | Feature image |
| `price_display` | VARCHAR(100) NULL | Freeform price text |
| `status` | ENUM('draft','published') | |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | |

### testimonials

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `client_name` | VARCHAR(255) | |
| `client_role` | VARCHAR(255) NULL | |
| `body` | TEXT | Testimonial text |
| `rating` | TINYINT NULL | 1–5 |
| `portrait_media_id` | INT NULL FK(media) | Client photo |
| `status` | ENUM('draft','published') | |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | |

### blog_posts

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `title` | VARCHAR(255) | |
| `slug` | VARCHAR(255) UNIQUE | |
| `excerpt` | TEXT NULL | Short teaser |
| `body` | LONGTEXT NULL | Full HTML content |
| `cover_media_id` | INT NULL FK(media) | |
| `author_id` | INT NULL FK(users) | |
| `status` | ENUM('draft','published','archived','scheduled') | |
| `published_at` | TIMESTAMP NULL | Supports scheduled publishing |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP NULL | |

### blog taxonomy tables

- `blog_categories` — (id, name, slug, description, parent_id)
- `blog_tags` — (id, name, slug)
- `blog_post_categories` — pivot (post_id, category_id)
- `blog_post_tags` — pivot (post_id, tag_id)
- `blog_post_media` — (post_id, media_id, context, sort_order)

### blog_post_revisions

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `post_id` | INT FK(blog_posts) | |
| `title` | VARCHAR(255) | Snapshot |
| `body` | LONGTEXT | Snapshot |
| `saved_by` | INT NULL FK(users) | |
| `created_at` | TIMESTAMP | |

### inquiries

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `name` | VARCHAR(255) | |
| `email` | VARCHAR(255) | |
| `phone` | VARCHAR(50) NULL | |
| `subject` | VARCHAR(255) NULL | |
| `message` | TEXT | |
| `status` | ENUM('new','in_progress','replied','closed') | |
| `ip_address` | VARCHAR(45) NULL | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### inquiry_notes

| Column | Type |
|---|---|
| `id` | INT PK |
| `inquiry_id` | INT FK(inquiries) |
| `note` | TEXT |
| `created_by` | INT NULL FK(users) |
| `created_at` | TIMESTAMP |

### booking_requests

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `name` | VARCHAR(255) | |
| `email` | VARCHAR(255) | |
| `phone` | VARCHAR(50) NULL | |
| `event_type` | VARCHAR(100) NULL | Wedding, portrait, etc. |
| `event_date` | DATE NULL | |
| `event_location` | VARCHAR(500) NULL | |
| `message` | TEXT NULL | |
| `status` | ENUM('new','contacted','quoted','booked','cancelled') | |
| `ip_address` | VARCHAR(45) NULL | |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### activity_logs

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `user_id` | INT NULL FK(users) | Null for system events |
| `action` | VARCHAR(100) | e.g. `login`, `logout`, `login_failed` |
| `entity_type` | VARCHAR(100) NULL | |
| `entity_id` | INT NULL | |
| `description` | TEXT NULL | |
| `ip_address` | VARCHAR(45) NULL | |
| `user_agent` | TEXT NULL | |
| `created_at` | TIMESTAMP | |

### migrations

| Column | Type |
|---|---|
| `id` | INT PK |
| `migration` | VARCHAR(255) UNIQUE |
| `ran_at` | TIMESTAMP |

---

## 12. Models

All models follow a consistent pattern:
- Constructor receives `Database $db` instance
- Methods use `$db->query(SQL, params)->fetch()` or `fetchAll()`
- Return arrays (not objects)
- Soft deletes filtered via `deleted_at IS NULL`

### User Models

**`App\Models\User`**
- `findById(int $id): ?array`
- `findByEmail(string $email): ?array`
- `allActive(): array`
- `touchLastLoginAt(int $id): void`
- `create(array $data): int`
- `update(int $id, array $data): void`
- `softDelete(int $id): void`

### Content Models

**`App\Models\Page`** — CMS page CRUD, slug uniqueness, parent options, status management

**`App\Models\PageSection`** — Sections within pages, sort order management

**`App\Models\ReusableBlock`** — Shared content blocks by `key`, used via `render_reusable_block()`

**`App\Models\HeroSlide`** — Carousel slides with media relationship and sort order

**`App\Models\Service`** — Service listings with cover media

**`App\Models\Testimonial`** — Testimonials with portrait media

### Media Models

**`App\Models\Media`** — Central asset store. Includes archive (soft-delete) and active/archived status.

**`App\Models\MediaVariant`** — Generated thumbnails. One `thumb` variant per image, stored at 640px wide JPEG.

**`App\Models\MediaUsageMap`** — Tracks all references to a media record across entities. Used to block deletion of in-use media.

### Gallery Models

**`App\Models\Gallery`** — Portfolio galleries with cover image and featured flag

**`App\Models\GalleryCategory`** — Category definitions

**`App\Models\GalleryCategoryMap`** — Gallery-to-category pivot

**`App\Models\GalleryMedia`** — Gallery-to-media pivot with sort order and per-image captions

**`App\Models\Album`** — Named collections within or outside galleries

**`App\Models\AlbumMedia`** — Album-to-media pivot with sort order

### Blog Models

**`App\Models\BlogPost`** — Full CRUD with soft deletes, status management, author reference

**`App\Models\BlogCategory`** — Hierarchical (parent_id) categories

**`App\Models\BlogTag`** — Flat tag list

**`App\Models\BlogPostCategory`** / **`BlogPostTag`** / **`BlogPostMedia`** — Pivot tables

**`App\Models\BlogPostRevision`** — Full snapshots of post title + body, created on each published save

### CMS/System Models

**`App\Models\Setting`** — Key-value store with group namespacing. `allGrouped(): array<group, array<key, value>>`

**`App\Models\SeoMeta`** — Entity-scoped SEO fields

**`App\Models\ActivityLog`** — Audit trail writes

**`App\Models\Permission`** / **`App\Models\Role`** — Permission and role CRUD

### Form Models

**`App\Models\Inquiry`** — Contact form submissions with status lifecycle

**`App\Models\InquiryNote`** — Internal CRM notes on inquiries

**`App\Models\BookingRequest`** — Booking submissions with event details

---

## 13. Repositories

Repositories encapsulate complex, multi-join queries that don't belong in thin models.

### `App\Repositories\BlogRepository`

- `postSlugExists(string $slug, ?int $excludeId): bool`
- `adminIndex(array $filters): array` — paginated listing with category/tag/status filters
- `publicIndex(array $filters): array` — published posts with thumbnail joins
- `categoryCounts(): array` — posts per category
- `tagCounts(): array` — posts per tag
- `archiveCounts(): array` — posts per year/month
- `featuredPost(): ?array` — most recent featured post
- `relatedPosts(int $postId, array $categoryIds): array`
- `recentPosts(int $limit): array`

Uses `GROUP_CONCAT` for denormalized category/tag lists. Joins `media_variants` for thumbnail URLs.

### `App\Repositories\GalleryRepository`

- `published(array $filters): array` — published galleries with media counts
- `search(string $query): array`
- `all(array $filters): array` — admin listing

### `App\Repositories\MediaRepository`

- `findByUuid(string $uuid): ?array`
- `search(array $filters): array` — type, keyword, date filters
- `byType(string $fileType): array`
- Integrates `media_usage_map` to surface "in-use" status

---

## 14. Services

Services contain business logic that spans multiple models or has workflow complexity. All services receive dependencies via constructor injection.

### Authentication Services

**`App\Services\AuthorizationService`**
- `userHasRole(int $userId, string $roleSlug): bool`
- `userHasPermission(int $userId, string $permissionSlug): bool`

**`App\Services\LoginThrottleService`**
- `isThrottled(string $key): bool` — checks attempt count against limit
- `recordAttempt(string $key): void`
- `clearAttempts(string $key): void`
- `remainingSeconds(string $key): int`

**`App\Services\SecurityLogger`**
- `logLogin(int $userId, string $ip, string $userAgent): void`
- `logLogout(int $userId): void`
- `logLoginFailed(string $email, string $ip): void`
- Writes to `activity_logs` via `ActivityLog` model

### Content Services

**`App\Services\PageService`**
- Slug generation with uniqueness enforcement
- Publishing and draft workflow
- Section ordering

**`App\Services\PageSectionService`**
- Section type rendering

**`App\Services\ReusableBlockService`**
- `renderPartialForType(string $type): string` — maps block type to view partial path
- Used by `render_reusable_block()` helper

**`App\Services\BlogPostService`**
- Post CRUD orchestration with SEO meta, category/tag sync

**`App\Services\BlogPublishingService`**
- Status transitions: draft → published, scheduled, archived
- Enforces published_at timestamp for scheduled posts

**`App\Services\BlogRevisionService`**
- Creates revision snapshot on each published save
- `listRevisions(int $postId): array`
- `restoreRevision(int $revisionId, int $postId): void`

**`App\Services\BlogTaxonomyService`**
- Syncs `blog_post_categories` and `blog_post_tags` on save

**`App\Services\ServiceService`**
- Service listing CRUD with sort order management

**`App\Services\GalleryService`**
- Gallery CRUD, media attachment, sort order management

### Media Services

**`App\Services\MediaUploadService`**
- Full upload pipeline (validation → storage → DB record → thumbnail generation)
- See [Section 18](#18-media-upload-and-storage) for detailed flow

**`App\Services\MediaUsageService`**
- `recordUsage(int $mediaId, string $entityType, int $entityId, string $context): void`
- `removeUsage(int $mediaId, string $entityType, int $entityId): void`
- `isInUse(int $mediaId): bool`

**`App\Services\MediaArchiveService`**
- Checks `isInUse()` before archiving
- Sets `status = 'archived'` and `deleted_at`

### Form Services

**`App\Services\InquiryService`**
- Validates and persists contact form submissions
- Status lifecycle: `new` → `in_progress` → `replied` → `closed`

**`App\Services\BookingRequestService`**
- Validates and persists booking requests
- Status lifecycle: `new` → `contacted` → `quoted` → `booked` → `cancelled`

### Output Services

**`App\Services\MailService`**
- PHPMailer wrapper
- `send(string $to, string $subject, string $htmlBody, ?string $replyTo): bool`
- Configured from `config/mail.php` (SMTP host, port, encryption, credentials)
- Fails gracefully; logs errors

**`App\Services\LogService`**
- Structured JSON line logging to `storage/logs/app-YYYY-MM-DD.log`

**`App\Services\SeoService`**
- `metaForPage(int $pageId): array` — retrieves or constructs fallback SEO data
- `metaForPost(int $postId): array`
- `renderMetaTags(array $meta): string` — outputs `<meta>` HTML

---

## 15. Controllers

### Controller Responsibilities

Controllers are intentionally thin. They:
1. Extract and validate input via `Validator`
2. Delegate to services for business logic
3. Pass data to view templates or return JSON responses
4. Handle redirects and flash messages

Controllers must NOT contain SQL queries, business logic decisions, or HTML rendering beyond view delegation.

### Auth Controller

**`App\Controllers\Auth\AuthController`**

- `showLogin(Request, Response)`: renders `auth/login` view
- `login(Request, Response)`: validates CSRF, validates credentials, calls `LoginThrottleService`, logs auth event, sets session, redirects to dashboard
- `logout(Request, Response)`: calls `auth->logout()`, logs event, redirects to login

### Admin Controllers

#### PageController (`App\Controllers\Admin\PageController`)

- `index`: paginated page listing with status filter
- `create`: render create form with template options
- `store`: validate → `PageService::create()` → redirect with flash
- `edit`: load page + seo_meta for edit form
- `update`: validate → `PageService::update()` → redirect
- `delete`: `PageService::softDelete()` → redirect
- `slug`: AJAX slug availability check

#### PageSectionController (`App\Controllers\Admin\PageSectionController`)

- `index`: render section manager for a page
- `store`, `update`, `delete`: AJAX-driven, returns JSON
- `reorder`: accepts `{direction: 'up'|'down'}`, adjusts sort_order
- `toggleStatus`: toggles section active/inactive

#### ReusableBlockController

- `index`, `create`, `store`, `edit/{id}`, `update/{id}`, `delete/{id}`
- Returns JSON for AJAX operations

#### GalleryController

- `index`, `create`, `store`, `edit/{id}`, `update/{id}`, `delete/{id}`, `slug`
- `attachMedia`: POST `{media_id}` → `gallery_media` insert
- `updateMedia`: update caption on a gallery-media record
- `reorderMedia`: reorder by sort_order
- `removeMedia`: remove from `gallery_media`

All media operations return JSON.

#### BlogPostController

- `index`: filterable listing (status, category, tag, search)
- `create`: form with category/tag pickers
- `store`, `update`: orchestrates `BlogPostService`, `BlogTaxonomyService`, `BlogRevisionService`
- `delete`: soft delete
- `slug`: AJAX slug check
- `attachMedia`, `updateMedia`, `reorderMedia`, `removeMedia`: blog media management (JSON)
- `autosave`: auto-save draft body on interval (JSON)

#### MediaController

- `index`: media library with grid/list view, type filter, search
- `search`: AJAX JSON search with pagination
- `update`: update alt_text, title, caption (JSON)
- `delete`: hard delete after usage check
- `picker`: modal media picker for embedding in content forms

#### MediaUploadController

- `upload`: processes `$_FILES['file']`, delegates to `MediaUploadService`, returns JSON `{ok, data}`
- `archive`: soft-archives via `MediaArchiveService`

#### InquiryController

- `index`: inquiry list with status tabs
- `filter`: AJAX filter by status, date range
- `show`: inquiry detail + notes
- `updateStatus`: change inquiry status (JSON)
- `addNote`: add internal note (JSON)

#### BookingController (Admin)

- `index`: booking list with status filter
- `show`: booking detail
- `updateStatus`: status transition (JSON)

#### SettingsController

- `index`: settings grouped by section
- `update`: persist updated settings, invalidate cache via `app_settings_refresh_cache()`

#### HeroSlideController

- `index`, `create`, `store`, `edit/{id}`, `update/{id}`, `delete/{id}`
- Manages sort_order for carousel sequencing

#### ServiceController (Admin), TestimonialController (Admin)

- Standard CRUD pattern (index, create, store, edit, update, delete)

#### BlogCategoryController / BlogTagController

- `index`: listing page with inline-create form
- `store`, `update/{id}`, `delete/{id}`: AJAX-driven, returns JSON
- `slug`: slug availability check

### Web Controllers

#### HomeController

- Loads: published hero slides, published galleries (featured), recent blog posts, published testimonials, site settings
- Renders `web/home` with `layouts/main`

#### PageController (Web)

- Resolves `/{slug}` to a published page record
- Loads page sections, seo_meta
- Renders `web/page` or a page-specific template if `$page['template']` is set
- Returns 404 if page not found or not published

#### BlogController (Web)

- `index`: paginated published posts with category/tag sidebar
- `show`: single post with related posts, previous/next navigation
- `search`: keyword search over title, excerpt, body
- `category`: posts filtered by category slug
- `tag`: posts filtered by tag slug

#### PortfolioController

- `index`: published galleries (optionally filtered by category)
- `category`: galleries in a specific category
- `show`: single gallery with all media in sort order

#### ServiceController (Web)

- `index`: all published services
- `show`: single service by slug

#### TestimonialController (Web)

- `index`: all published testimonials, paginated or all-at-once

#### ContactController

- `show`: render contact form (`web/contact`) with CSRF token
- `store`: validate → `InquiryService::create()` → send confirmation email → redirect with success flash

#### BookingController (Web)

- `show`: render booking form
- `store`: validate → `BookingRequestService::create()` → confirmation → redirect

#### SeoController

- `sitemap`: generates `sitemap.xml` with all public pages, galleries, blog posts, services
- `robots`: generates `robots.txt` with `Disallow: /admin` rule

---

## 16. Validation

### Core Validator (`App\Core\Validator`)

```php
$validator = new Validator($data, [
    'email'   => 'required|email',
    'name'    => 'required|min:2|max:100',
    'status'  => 'required|in:draft,published',
    'note'    => 'nullable|max:5000',
    'date'    => 'nullable|date',
]);

if ($validator->fails()) {
    // redirect with errors
}

$validated = $validator->validated(); // only validated fields
```

### Available Rules

| Rule | Description |
|---|---|
| `required` | Field must be present and non-empty |
| `email` | Valid email format |
| `min:N` | Minimum string length N |
| `max:N` | Maximum string length N |
| `date` | Parseable date string |
| `in:a,b,c` | Value must be one of listed options |
| `nullable` | Field may be empty/null (skip other rules if empty) |
| custom callable | `fn($value) => true` or `'error message string'` |

### Domain Validators (`app/Validators/`)

Domain-specific validator classes extend or compose `Validator` with custom rule sets for entities like `Page`, `BlogPost`, etc. These encapsulate field-level rules and can be reused across store/update operations.

### Flash Validation State

On failure, controllers:
1. Flash `old_input` with the submitted data: `session()->flash('old_input', $request->all())`
2. Flash `errors` with validation messages
3. Redirect back

In templates: `old('field_name')` and `$errors['field']` are available for form repopulation.

---

## 17. Error Handling and Logging

### ErrorHandler (`App\Core\ErrorHandler`)

Registered as shutdown handler for fatal PHP errors:
- `register_shutdown_function()` captures E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR
- Wraps them in `ErrorException` and delegates to `handle()`

**Exception handling pipeline:**
1. Determine HTTP status (HttpException carries status; all others → 500)
2. Generate or extract `X-Request-Id` (from `HTTP_X_REQUEST_ID` header or `bin2hex(random_bytes(8))`)
3. Log structured JSON record to `storage/logs/app-YYYY-MM-DD.log`
4. Determine response format (JSON if `expectsJson()`, HTML otherwise)
5. Render appropriate error view or JSON response
6. Set `X-Request-Id` response header

**Log record format:**
```json
{
  "timestamp": "2026-06-14T12:00:00+00:00",
  "request_id": "a1b2c3d4e5f6g7h8",
  "status": 500,
  "method": "POST",
  "path": "/admin/pages/store",
  "ip": "127.0.0.1",
  "user_agent": "Mozilla/5.0...",
  "exception": "RuntimeException",
  "message": "...",
  "file": "/app/Services/PageService.php",
  "line": 42,
  "trace": "..."
}
```

**Error views:**
- `resources/views/errors/404.php` — not found
- `resources/views/errors/500-admin.php` — server error (admin context)
- `resources/views/errors/500-public.php` — server error (public context)

**Safe message rules:**
- 404: shows exception message or generic "page not found"
- 500 in debug mode: shows exception message
- 500 in production: always shows "Something went wrong on our side..."
- Other 4xx: shows exception message if non-empty

### HttpException (`App\Core\Exceptions\HttpException`)

```php
throw new HttpException(404, 'Gallery not found');
throw new HttpException(403, 'Permission denied');
```

Carries `getStatusCode(): int`. Caught in `Application::run()`.

---

## 18. Media Upload and Storage

### Upload Pipeline (`App\Services\MediaUploadService`)

```
HTTP POST /admin/media/upload
  → MediaUploadController::upload()
  → MediaUploadService::upload($file, $userId)
      1. Validate $_FILES array structure
      2. Verify PHP upload error code = 0
      3. Confirm is_uploaded_file() (prevents path traversal)
      4. Detect MIME via finfo_file() — NOT relying on client-provided type
      5. Resolve fileType: image / video / document / other
      6. validateByType():
           - Size check: <= UPLOAD_MAX_FILE_SIZE_MB (default 10 MB)
           - MIME whitelist from config/uploads.php
           - Image: extension whitelist, getimagesize() validity,
                    dimension check <= UPLOAD_MAX_IMAGE_WIDTH x UPLOAD_MAX_IMAGE_HEIGHT (6000x6000)
           - Blocked extensions: php, phtml, phar, exe, sh, bat, cmd, ps1
           - fileType 'other' is rejected
      7. SHA-256 checksum of file
      8. Generate date-based target directory: {type_path}/YYYY/MM/
      9. Create directories recursively (0775)
     10. ensureDirectoryProtection() — write .htaccess blocking script execution
     11. Generate stored name: YYYYMMDDHHiiss-{20 random hex chars}.{ext}
     12. move_uploaded_file() to target
     13. INSERT INTO media (all fields)
     14. For images: createImageVariant() → 640px JPEG thumbnail
     15. INSERT INTO media_variants ON DUPLICATE KEY UPDATE
  → Return {id, uuid, stored_name, directory, mime_type, file_type, size_bytes, checksum}
```

### Directory Protection (`.htaccess` auto-generated)

```apache
Options -Indexes
<FilesMatch "\.(php|phtml|phar|pl|py|cgi|asp|aspx|sh|bat|cmd|exe|dll|com)$">
    Require all denied
</FilesMatch>
RemoveHandler .php .phtml .phar ...
RemoveType .php .phtml .phar ...
```

### Storage Paths

| File Type | Config Key | Default Path |
|---|---|---|
| Images | `uploads.paths.images` | `public/uploads/images/` |
| Videos | `uploads.paths.videos` | `public/uploads/videos/` |
| Documents | `uploads.paths.documents` | `public/uploads/documents/` |
| Thumbnails | `uploads.paths.variants` | `public/uploads/variants/` |
| Base | `uploads.paths.base` | `public/uploads/` |

Actual files are stored at: `{app_root}/{path}/YYYY/MM/{stored_name}`

Web URL: `{APP_URL}/uploads/{type}/YYYY/MM/{stored_name}`

### Thumbnail Generation

- Uses PHP GD extension
- Resizes to 640px width (proportional height)
- Output: JPEG at quality 82
- Stored name: `{original-stem}-thumb.jpg`
- Falls back gracefully if GD is unavailable

### Allowed MIME Types (configured via `.env`)

| Type | Env Key | Example values |
|---|---|---|
| Images | `UPLOAD_ALLOWED_IMAGE_MIMES` | `image/jpeg,image/png,image/webp,image/gif` |
| Documents | `UPLOAD_ALLOWED_DOCUMENT_MIMES` | `application/pdf` |
| Videos | `UPLOAD_ALLOWED_VIDEO_MIMES` | `video/mp4,video/quicktime` |

### URL Resolution Helpers

```php
app_media_url($directory, $storedName)           // full URL to original
app_media_url($variantDir, $variantStoredName)   // full URL to thumbnail
app_media_preferred_url($record, $prefix)        // uses thumb if available, falls back to original
```

---

## 19. Settings and Caching

### Settings Architecture

Settings are stored in the `settings` table as key-value pairs grouped by namespace (`group`). They are used for site configuration that non-technical admins can change via the admin panel.

**Groups (examples):**
- `site` — site name, tagline, logo, favicon
- `contact` — phone, email, address
- `seo` — default meta title/description
- `social` — social media URLs
- `booking` — booking policy text

### Settings Cache

The `app_settings_all()` helper implements a two-tier cache:

1. **In-memory (within request):** Static variable `$cache` — first call within a request reuses it
2. **File cache:** `storage/cache/settings.php` — PHP `return [...]` file
   - TTL: `APP_SETTINGS_CACHE_TTL` (default 300 seconds)
   - Written with `LOCK_EX` for atomic updates
   - Invalidated by `app_settings_refresh_cache()` (called after settings save)

```php
app_settings_all()                        // grouped array
app_settings_group('site')               // single group
app_setting('site', 'name', 'Default')  // single value with fallback
app_settings_refresh_cache()             // force rebuild
```

---

## 20. View System and Templating

### View Engine (`App\Core\View`)

- Pure PHP templates — no Blade/Twig/Smarty
- `render(view, data, layout)`:
  1. Extracts `$data` into local scope
  2. Includes the view file, captures output as `$content`
  3. If `$layout` specified: extracts + includes layout with `$content` available
- `partial(view, data)`: extracts data, includes view, returns string (no layout)
- Escaping: `View::e($value)` → `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`

### Layouts

#### `resources/views/layouts/main.php` (Public)

Includes:
- `<head>`: Tailwind CSS, Google Fonts (Cormorant Garamond, Inter), meta charset/viewport
- Navigation bar with site branding and menu links
- `<?= $content ?>` for page body
- Footer with contact info, social links, copyright
- Site settings injected via `app_settings_all()`

#### `resources/views/layouts/admin.php` (Admin)

Includes:
- Admin top bar (brand logo, user menu, logout)
- Sidebar navigation (links to all admin sections, active state via `is_current_path()`)
- Breadcrumb component
- Flash message display (success/error/warning)
- `<?= $content ?>` for page body
- Alpine.js or vanilla JS for sidebar collapse and dropdowns

#### `resources/views/layouts/auth.php` (Login)

Minimal centered layout for login form only.

### View Directory Structure

```
resources/views/
├── layouts/
│   ├── main.php
│   ├── admin.php
│   └── auth.php
├── web/
│   ├── home.php
│   ├── page.php
│   ├── blog/
│   │   ├── index.php
│   │   ├── show.php
│   │   ├── category.php
│   │   └── tag.php
│   ├── portfolio/
│   │   ├── index.php
│   │   ├── category.php
│   │   └── show.php
│   ├── services/
│   │   ├── index.php
│   │   └── show.php
│   ├── testimonials.php
│   ├── contact.php
│   └── booking.php
├── admin/
│   ├── dashboard.php
│   ├── content.php
│   ├── users.php
│   ├── settings.php
│   ├── pages/
│   │   ├── index.php
│   │   ├── form.php
│   │   └── sections.php
│   ├── blocks/
│   ├── galleries/
│   ├── blog/
│   │   ├── posts/
│   │   └── categories.php
│   ├── media/
│   ├── inquiries/
│   ├── bookings/
│   ├── services/
│   ├── testimonials/
│   └── hero-slides/
├── auth/
│   └── login.php
├── components/
│   ├── breadcrumbs.php
│   ├── flash.php
│   ├── pagination.php
│   └── ...
├── emails/
│   ├── inquiry-confirmation.php
│   └── booking-confirmation.php
└── errors/
    ├── 404.php
    ├── 500-admin.php
    └── 500-public.php
```

---

## 21. Frontend Architecture

### Design System

#### Color Palette (from `tailwind.config.js`)

| Token | Hex | Usage |
|---|---|---|
| `charcoal` | `#1a1a1a` | Primary dark backgrounds, body text |
| `charcoal-light` | `#2d2d2d` | Elevated surfaces |
| `ivory` | `#faf9f7` | Main background |
| `ivory-warm` | `#f5f1eb` | Section backgrounds |
| `cream` | `#ebe5dc` | Borders, dividers |
| `bronze` | `#9a7b5c` | Primary accent, CTAs |
| `bronze-light` | `#b8956f` | Hover states |
| `bronze-dark` | `#7a5f42` | Active states |
| `gold` | `#c4a77d` | Decorative accents |
| `taupe` | `#a8998a` | Muted text, icons |
| `sand` | `#f6f1ea` | Card backgrounds |
| `parchment` | `#f4efe8` | Alt section backgrounds |
| `pine` | `#2f4c45` | Dark accent |
| `moss` | `#3f5a4f` | Hover on dark |
| `espresso` | `#1b1714` | Deep footer background |
| `ink` | `#171411` | Rich black text |
| `ember` | `#b08968` | Warm accent |
| `clay` | `#a67f63` | Alt accent |

#### Typography

| Role | Font | Weights |
|---|---|---|
| Display (`font-display`) | Cormorant Garamond | 300, 400, 500, 600, 700 |
| Body (`font-body`) | Inter | 300, 400, 500, 600 |

#### Custom Tailwind Extensions

```js
boxShadow: { soft: '0 10px 30px -16px rgba(15, 23, 42, 0.28)' }
borderRadius: { '2xl': '1rem', '3xl': '1.5rem', '4xl': '2rem' }
spacing: { '18': '4.5rem', '22': '5.5rem', '26': '6.5rem', '30': '7.5rem' }
```

### CSS Build Process

```bash
# Development (watch mode)
npm run watch:css

# Production (minified)
npm run build:css
```

Tailwind's JIT engine scans `app/**/*.php`, `resources/views/**/*.php`, `public/assets/js/**/*.js` for class usage.

Output: `public/assets/css/tailwind.css`

### JavaScript Approach

- **Vanilla ES6+** — no jQuery on the public site
- Admin panel may use lightweight utilities (no heavy framework)
- AJAX via `fetch()` API
- DOM manipulation via standard `document.querySelector`, `classList`, `dataset`
- Progressive enhancement — forms work without JS; JS enhances UX

### Responsive Strategy

Breakpoints follow Tailwind defaults:

| Prefix | Min-width | Target devices |
|---|---|---|
| (default) | 0px | Mobile portrait |
| `sm` | 640px | Mobile landscape |
| `md` | 768px | Tablet |
| `lg` | 1024px | Desktop |
| `xl` | 1280px | Wide desktop |
| `2xl` | 1536px | Ultra-wide |

Layout approach:
- Mobile-first CSS utilities
- Navigation collapses to hamburger at mobile breakpoints
- Gallery grids: 1 → 2 → 3 → 4 columns
- Typography scales up at `lg` and above

---

## 22. Public Website Pages

### Homepage (`/`)

**Data loaded:**
- Published hero slides (sorted by `sort_order`)
- Featured published galleries (limit 6)
- Recent published blog posts (limit 3)
- Published testimonials (limit 3)
- Site settings (logo, tagline, contact info)
- Reusable blocks (CTA section, about teaser)

**Sections:**
1. Hero carousel (auto-rotating, keyboard accessible)
2. Introduction / brand statement
3. Featured portfolio galleries grid
4. Services overview teaser
5. Testimonial carousel
6. Blog post teasers
7. CTA / contact prompt

### Portfolio Index (`/portfolio`)

- All published galleries, sorted by `sort_order`
- Category filter navigation (gallery categories)
- Cover image for each gallery from `cover_media_id` (or first attached media)
- Click → gallery detail

### Portfolio Category (`/portfolio/category/{slug}`)

- Galleries filtered to a specific category
- Same layout as index

### Gallery Detail (`/portfolio/{slug}`)

- Single gallery with title, description
- All attached media in sort order
- Lightbox viewer for full-screen images
- Previous / next gallery navigation

### Services Index (`/services`)

- All published services sorted by `sort_order`
- Feature image, title, short description, price display

### Service Detail (`/services/{slug}`)

- Full service description, feature image
- CTA linking to booking form

### Blog Index (`/blog`)

- Paginated published posts (newest first)
- Sidebar: categories with post counts, recent posts, tag cloud
- Archive groupings by month/year

### Blog Post (`/blog/{slug}`)

- Full post title, body, cover image, published date
- Author attribution (if set)
- Category and tag links
- Related posts (same category, limit 3)
- Previous / next post navigation

### Blog Search (`/blog/search`)

- Query parameter: `?q=keyword`
- Searches title, excerpt, body
- Results list with match context

### Blog Category / Tag (`/blog/category/{slug}`, `/blog/tag/{slug}`)

- Posts filtered by category or tag
- Same layout as blog index

### Contact Page (`/contact`)

**Form fields:** name, email, phone (optional), subject, message  
**Behavior:**
1. CSRF-protected POST
2. Validation: name required, email required+valid, message required+min:10
3. On success: `InquiryService::create()`, optional confirmation email, flash "message sent", redirect
4. On failure: flash errors + old_input, redirect back

### Booking Page (`/booking`)

**Form fields:** name, email, phone, event_type, event_date, event_location, message  
**Behavior:**
1. CSRF-protected POST
2. On success: `BookingRequestService::create()`, flash, redirect
3. On failure: flash errors + old_input, redirect back

### Custom CMS Pages (`/{slug}`)

- Dynamic pages created in the admin
- Slug resolved against `pages` table (status = published)
- Renders `web/page` template with page sections
- Page template field allows future custom templates

### SEO Pages

- **`/sitemap.xml`**: XML sitemap with all public URLs (pages, galleries, blog posts, services)
- **`/robots.txt`**: `User-agent: *`, `Disallow: /admin`, `Sitemap:` reference

---

## 23. Admin Panel

### Dashboard (`/admin/dashboard`)

Real-time metrics counters (all via inline SQL):

| Metric | Source |
|---|---|
| Pages (total / published) | `pages` |
| Galleries (total / published) | `galleries` |
| Blog posts (total / published) | `blog_posts` |
| Media files total | `media` |
| Services (total / published) | `services` |
| Testimonials (total / published) | `testimonials` |
| Hero slides (total / published) | `hero_slides` |
| Inquiries (total / new / in progress) | `inquiries` |
| Bookings (total / new / quoted) | `booking_requests` |

### Content Management

**Pages:** CRUD with template selector, parent page (for hierarchy), status toggle, SEO meta fields, and section manager.

**Page Sections:** Sub-resource of pages. Supports add, edit, delete, reorder (up/down), and toggle visibility. Section types determine available content fields.

**Reusable Blocks:** CMS blocks referenced by key in templates via `render_reusable_block('key')`. Block type determines the render partial and available fields.

**Hero Slides:** Carousel items with background image (media picker), title, subtitle, CTA label + URL, sort order, and status toggle.

### Gallery Management

- Create/edit gallery: title, slug (auto-generated, editable), description, status, featured flag, cover image
- Gallery media: attach from media library, add captions, drag-to-reorder, remove
- Category management: create/edit/delete categories, assign to galleries

### Blog Management

- Post editor: title (with AJAX slug generation), body (rich HTML), excerpt, cover image, categories, tags, status, `published_at`
- Post media: attach images to post, set context (cover, featured, inline)
- Autosave: `POST /admin/blog/posts/autosave/{id}` on interval (30s)
- Revision history: list revisions, restore to previous version
- Categories and tags: inline creation from listing page

### Media Library

- Grid view with type filter (all / images / videos / documents)
- Search by filename, alt text, title
- Click to view details, edit alt text / title / caption
- Media picker modal: used by page editor, gallery editor, blog post editor for selecting images
- Upload via drag-and-drop or file input (chunked to `POST /admin/media/upload`)
- Archive removes from library (soft) without deleting file if in use

### Inquiry Management

- List view with status tabs: new, in_progress, replied, closed
- Filter by date range, keyword
- Detail view: full message, contact info, internal notes
- Status updates and note-adding are AJAX-driven

### Booking Management

- List view with status pipeline: new → contacted → quoted → booked → cancelled
- Detail view with event details
- Status update via AJAX

### Settings Management

Settings grouped by section. The settings form renders different input types (`text`, `textarea`, `boolean`, `image`, etc.) based on `type` field. After save, `app_settings_refresh_cache()` is called to rebuild the PHP file cache.

---

## 24. Email System

### Configuration (`config/mail.php`)

```php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => [
        'smtp' => [
            'host'       => env('MAIL_HOST'),
            'port'       => (int) env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username'   => env('MAIL_USERNAME'),
            'password'   => env('MAIL_PASSWORD'),
            'timeout'    => (int) env('MAIL_TIMEOUT', 10),
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name'    => env('MAIL_FROM_NAME', env('APP_NAME', 'Mesh Photography')),
    ],
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', ''),
    ],
];
```

### MailService (`App\Services\MailService`)

PHPMailer wrapper. `send()` method:
1. Creates `PHPMailer` instance configured from `config/mail.php`
2. Sets SMTP credentials and encryption
3. Sets From, To, ReplyTo
4. Enables HTML body
5. Sends, returns bool
6. On failure: catches `PHPMailerException`, logs via `LogService`, returns false

### Email Templates

Located at `resources/views/emails/`:

- **`inquiry-confirmation.php`**: Sent to visitor on contact form submission. Fields: visitor name, submitted message summary, photographer contact info.
- **`booking-confirmation.php`**: Sent on booking request. Includes event date, type, next steps copy.

Templates are plain PHP/HTML using inline styles for email client compatibility.

### Planned Email Flows (Future)

- Notification to admin on new inquiry/booking
- Password reset flow
- Admin user invitation via email

---

## 25. SEO Strategy

### On-page SEO

Each entity (page, blog post, gallery, service) can have a `seo_meta` record with:
- `meta_title`, `meta_description`
- Open Graph: `og_title`, `og_description`, `og_image_id`
- Canonical URL
- `robots` directive (`index,follow`, `noindex`, etc.)
- `schema_markup` (JSON-LD block)

### SeoService (`App\Services\SeoService`)

- Loads entity-specific `seo_meta`
- Falls back to site settings for defaults
- `renderMetaTags(array $meta): string` — outputs `<title>`, description meta, OG tags, canonical link

### Technical SEO

- **Sitemap:** `GET /sitemap.xml` — auto-generated XML listing all public published content
- **Robots:** `GET /robots.txt` — blocks `/admin`, references sitemap URL
- **Canonical:** Rendered in `<head>` for every page with a canonical URL
- **Structured data:** JSON-LD via `schema_markup` field in `seo_meta`
- **URL structure:** All public URLs use clean slugs (no query parameters for canonical content)
- **Status codes:** 301 for permanent redirects (e.g. `/terms-of-service` → `/terms`)

### Admin SEO Fields

The page and blog post edit forms include an SEO panel with:
- Meta title (character counter, 50–60 chars ideal)
- Meta description (character counter, 120–160 chars ideal)
- OG title / description
- OG image picker
- Robots directives select
- JSON-LD textarea

---

## 26. Security Architecture

### Input Handling

- All GET/POST input is never interpolated directly into SQL — always via prepared statements
- `$request->input()`, `$request->query()`, `$request->file()` are the only input entry points
- No use of `extract($_POST)` or direct `$_GET[$key]` in business logic

### CSRF Protection

- All state-changing requests (POST, PUT, PATCH, DELETE forms) require a `_token` field
- Token is a 32-byte random hex value stored in session
- Verified by `app_csrf()->verify($token)` using `hash_equals()` (timing-safe)
- `csrf_field()` helper outputs the hidden input in forms

### Session Security

- Session IDs regenerated on login and logout (`session_regenerate_id(true)`)
- Session cookies: `HttpOnly=true`, `SameSite=Lax`, `Secure=true` (production)
- Fingerprinting: SHA-256 of User-Agent + Accept-Language (IP optional, disabled by default)
- Idle timeout: 60 minutes of inactivity (configurable)
- Session files stored outside `public/` in `storage/sessions/`

### Password Security

- `password_hash($password, PASSWORD_DEFAULT)` for storage (bcrypt)
- `password_verify($input, $hash)` for login check
- No MD5, SHA1, or reversible encryption

### Upload Security

- MIME type detected server-side via `finfo_file()` — client value ignored
- Extension whitelist — blocked: `php`, `phtml`, `phar`, `exe`, `sh`, `bat`, `cmd`, `ps1`
- `is_uploaded_file()` verification before `move_uploaded_file()`
- `.htaccess` written to every upload directory blocking script execution
- File size limit enforced (default 10 MB)
- Image dimension limit (6000×6000px)

### Output Escaping

- All template output escaped via `View::e()` or `htmlspecialchars()`
- JSON responses use `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` but never embed raw user HTML
- Raw HTML from CMS page body is admin-authored and intentionally unescaped (XSS accepted by design for CMS content)

### Rate Limiting

- Login attempts throttled by `LoginThrottleService`
- Stores attempt count in session (per-IP or per-email key)
- Lockout duration and max attempts configurable

### Audit Logging

- All admin login / logout / failed attempts written to `activity_logs`
- SecurityLogger is the entry point for all security events

### Admin Path Security

- Admin URL prefix is configurable via `ADMIN_PATH` (default `/admin`)
- Changing the prefix provides basic security-through-obscurity as an additional layer
- All admin routes still require session authentication regardless of path

### Error Information Disclosure

- `APP_DEBUG=false` in production hides exception stack traces from users
- Generic error messages shown; full details in log file only
- `X-Request-Id` header allows log correlation without exposing internals

### Headers (Recommended — Pending Implementation)

The following security headers should be added to Apache config or emitted by the application:

```
Content-Security-Policy: default-src 'self'; ...
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

---

## 27. Performance Strategy

### Database

- All queries use prepared statements (reduces parsing overhead)
- Indexes on: `slug` columns (UNIQUE), `status`, `deleted_at`, `sort_order`, `created_at`, FK columns
- `GROUP_CONCAT` used in blog/gallery repositories to denormalize taxonomy in a single query
- `LIMIT` applied to all list queries (admin pagination, public listings)
- Settings and reusable blocks cached to avoid repeated queries per request

### Asset Performance

- Tailwind CSS compiled and minified for production (`npm run build:css`)
- No jQuery shipped to public visitors (vanilla JS only)
- No unused CSS classes (Tailwind JIT scans templates)
- Fonts loaded from Google Fonts with `preconnect` hints

### Image Performance

- 640px JPEG thumbnails generated server-side at upload time
- `app_media_preferred_url()` uses thumbnail URL for card/list views, full URL for lightboxes
- Browser-level lazy loading via `loading="lazy"` on non-hero images
- Upload dimension limit (6000×6000px) prevents serving oversized originals

### Caching

- **Settings cache:** PHP file at `storage/cache/settings.php` with configurable TTL (default 300s)
- **Reusable block cache:** In-memory static array within the request (no cross-request cache currently)
- **OPcache** (server-level): PHP compiled bytecode cache — recommended for production

### Planned Performance Improvements

- Browser caching headers (`Cache-Control`, `ETag`) for static assets
- WebP conversion for uploaded images
- Additional image sizes (responsive srcset)
- Full-page or fragment caching for public pages

---

## 28. Accessibility Strategy

### Implemented

- Semantic HTML5 elements (`<nav>`, `<main>`, `<article>`, `<section>`, `<header>`, `<footer>`)
- `<img>` alt text stored in `media.alt_text`, rendered in templates
- Form labels associated with inputs via `for`/`id` pairing
- Skip-to-content links in main layout
- ARIA roles and labels on interactive components (menus, modals, carousels)

### Targets (WCAG 2.1 AA)

- Minimum 4.5:1 color contrast ratio for normal text
- 3:1 for large text and UI components
- Keyboard navigation for all interactive elements
- Focus indicators visible
- Error messages associated with form fields via `aria-describedby`
- Carousel with pause controls and keyboard support

---

## 29. Configuration Reference

### Required Environment Variables

| Variable | Description | Example |
|---|---|---|
| `APP_NAME` | Application display name | `Mesh Photography` |
| `APP_ENV` | Environment (`local`, `production`) | `production` |
| `APP_DEBUG` | Show debug errors | `false` |
| `APP_URL` | Canonical app URL | `https://meshphoto.com` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `mesh_photo` |
| `DB_USERNAME` | Database user | `mesh_user` |
| `SESSION_DRIVER` | Session backend | `file` |
| `SESSION_LIFETIME` | Cookie TTL (minutes) | `120` |
| `MAIL_MAILER` | Mail transport | `smtp` |
| `MAIL_HOST` | SMTP server | `smtp.mailgun.org` |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_FROM_ADDRESS` | Sender address | `hello@meshphoto.com` |
| `ADMIN_PATH` | Admin URL prefix | `/admin` |
| `CSRF_TOKEN_NAME` | CSRF field name | `_token` |

### Optional Environment Variables

| Variable | Default | Description |
|---|---|---|
| `APP_TIMEZONE` | `UTC` | PHP timezone |
| `APP_LOCALE` | `en` | Locale code |
| `APP_SETTINGS_CACHE_TTL` | `300` | Settings cache TTL (seconds) |
| `DB_PASSWORD` | — | Database password |
| `DB_CHARSET` | `utf8mb4` | DB charset |
| `SESSION_FILE_PATH` | `storage/sessions` | Session file directory |
| `SESSION_IDLE_TIMEOUT` | `60` | Idle timeout (minutes) |
| `SESSION_SECURE` | `false` | Secure cookie (true in prod) |
| `SESSION_HTTP_ONLY` | `true` | HttpOnly cookie |
| `SESSION_SAME_SITE` | `Lax` | SameSite cookie policy |
| `SESSION_FINGERPRINT_IP` | `false` | Include IP in fingerprint |
| `MAIL_USERNAME` | — | SMTP username |
| `MAIL_PASSWORD` | — | SMTP password |
| `MAIL_ENCRYPTION` | `tls` | `tls` or `ssl` |
| `MAIL_FROM_NAME` | `$APP_NAME` | Sender display name |
| `MAIL_REPLY_TO_ADDRESS` | — | Reply-To address |
| `UPLOAD_MAX_FILE_SIZE_MB` | `10` | Max upload size in MB |
| `UPLOAD_MAX_IMAGE_WIDTH` | `6000` | Max image width (px) |
| `UPLOAD_MAX_IMAGE_HEIGHT` | `6000` | Max image height (px) |
| `UPLOAD_ALLOWED_IMAGE_MIMES` | — | CSV of allowed image MIME types |
| `UPLOAD_ALLOWED_DOCUMENT_MIMES` | — | CSV of allowed document MIME types |
| `UPLOAD_ALLOWED_VIDEO_MIMES` | — | CSV of allowed video MIME types |
| `UPLOAD_BASE_PATH` | `public/uploads` | Base upload directory |
| `UPLOAD_IMAGES_PATH` | `public/uploads/images` | Image upload directory |
| `UPLOAD_DOCUMENTS_PATH` | `public/uploads/documents` | Document upload directory |
| `UPLOAD_VIDEOS_PATH` | `public/uploads/videos` | Video upload directory |
| `UPLOAD_VARIANTS_PATH` | `public/uploads/variants` | Thumbnail directory |

---

## 30. Helper Functions Reference

All helpers are defined in `bootstrap/helpers.php` using `function_exists()` guards.

### Environment and Config

| Function | Signature | Description |
|---|---|---|
| `env()` | `(string $key, mixed $default): mixed` | Read env var; converts `'true'`/`'false'`/`'null'`/`'empty'` strings |
| `config()` | `(string $key, mixed $default): mixed` | Dot-notation config access |

### URL Helpers

| Function | Signature | Description |
|---|---|---|
| `base_url()` | `(string $path = ''): string` | Absolute URL with APP_URL base |
| `app_path_url()` | `(string $path = ''): string` | Path relative to app base (for href attributes) |
| `app_href()` | `(string $path = ''): string` | Alias for `app_path_url()` |
| `app_base_path()` | `(): string` | URL path prefix (e.g. `/mesh`) |
| `admin_url()` | `(string $path = ''): string` | Admin panel URL |
| `asset_url()` | `(string $path): string` | URL to compiled asset |
| `upload_url()` | `(string $path): string` | URL to uploaded file |
| `redirect_url()` | `(string $path = ''): string` | URL for use in `Response::redirect()` |
| `app_media_url()` | `(string $dir, string $name): string` | Full URL to a media file |
| `app_media_web_path()` | `(string $dir, string $name): string` | Path component only (no host) |
| `app_media_preferred_url()` | `(array $record, string $prefix = ''): string` | Prefers thumbnail over original |

### Authentication

| Function | Description |
|---|---|
| `app_auth()` | Returns `Auth` singleton |
| `app_session()` | Returns `Session` singleton |
| `app_csrf()` | Returns `CSRF` singleton |
| `current_user()` | Returns current user array or `null` |
| `csrf_field()` | Returns hidden CSRF input HTML |
| `can(string $permission)` | Returns bool — current user permission |
| `has_role(string $role)` | Returns bool — current user role |
| `app_authorization()` | Returns `AuthorizationService` singleton |
| `app_security_logger()` | Returns `SecurityLogger` singleton |

### Database and Models

| Function | Description |
|---|---|
| `app_database()` | Returns `Database` PDO wrapper singleton |
| `app_settings_model()` | Returns `Setting` model singleton |
| `app_reusable_blocks_model()` | Returns `ReusableBlock` model singleton |

### Settings

| Function | Signature | Description |
|---|---|---|
| `app_settings_all()` | `(bool $refresh = false): array` | All settings grouped |
| `app_settings_group()` | `(string $group, bool $refresh = false): array` | Single group |
| `app_setting()` | `(string $group, string $key, mixed $default, bool $refresh): mixed` | Single value |
| `app_settings_refresh_cache()` | `(): void` | Invalidate and rebuild settings cache |
| `app_cache_file()` | `(string $name): string` | Path to a named cache file |

### Content

| Function | Description |
|---|---|
| `app_reusable_block(string $key)` | Returns block data array or null (in-memory cached) |
| `app_reusable_block_refresh(string $key)` | Returns fresh block from DB |
| `published_reusable_block(string $key)` | Returns block only if status = published |
| `render_reusable_block(string $key)` | Returns rendered HTML of a published block |
| `render_component(string $view, array $data)` | Renders a view partial and returns HTML |

### Request

| Function | Description |
|---|---|
| `old(string $key, mixed $default)` | Previous form input from flash |
| `current_path()` | Current request path (app-relative) |
| `is_current_path(string $path, bool $exact)` | True if current path matches |

### Utilities

| Function | Description |
|---|---|
| `slugify(string $value)` | Converts to URL-safe slug (lowercase, hyphens) |

---

## 31. Database Migrations and Seeders

### Migration System

**CLI runner:** `php database/console.php {command}`  
**Composer shortcuts:**
```bash
composer migrate              # Run pending migrations
composer migrate:rollback     # Rollback last batch
composer migrate:status       # Show migration status
```

**Migration files:** `database/migrations/{timestamp}_{description}.php`  
Each returns an anonymous class extending `Migration` base with `up(): void` and `down(): void`.

**Tracking:** Executed migrations recorded in `migrations` table by filename.

### Migration Order (36 files)

| Timestamp | Description |
|---|---|
| 20260315000000 | Create migrations table |
| 20260315010000 | Create users table |
| 20260315020000 | Create roles table |
| 20260315020100 | Create permissions table |
| 20260315020200 | Create role_user pivot |
| 20260315020300 | Create permission_role pivot |
| 20260315030000 | Create settings table |
| 20260315030100 | Create pages table |
| 20260315030200 | Create page_sections table |
| 20260315030300 | Create reusable_blocks table |
| 20260315030400 | Create seo_meta table |
| 20260315030500 | Create activity_logs table |
| 20260315040000 | Create media table |
| 20260315040100 | Create media_variants table |
| 20260315040200 | Create media_usage_map table |
| 20260315050000–050500 | Gallery tables (categories, galleries, mappings, media, albums) |
| 20260315070000 | Create services table |
| 20260315080000 | Create testimonials table |
| 20260315081000–081600 | Blog tables (categories, tags, posts, pivot tables, revisions, media) |
| 20260315090000–090100 | Inquiry tables (inquiries, inquiry_notes) |
| 20260315092000 | Create booking_requests table |
| 20260316163000 | Create hero_slides table |

### Seeders (8 classes)

Located in `database/seeders/`.

| Command | Seeder | Data created |
|---|---|---|
| `composer seed:roles-permissions` | `RolePermissionSeeder` | 3 roles, 8 permissions, assignments |
| `composer seed:core-cms` | `CoreCmsSeeder` | System pages (home, about, blog, portfolio, contact, booking, privacy, terms) |
| `composer seed:core-cms` | `SettingsSeeder` | Site name, tagline, contact info, SEO defaults |
| `composer seed:media-sample` | `MediaSampleSeeder` | Placeholder media records (no actual files) |
| `composer seed:demo-content` | `HeroSlideSeeder` | 3 hero carousel slides |
| `composer seed:demo-content` | `AboutPageSeeder` | About page with sections |
| `composer seed:demo-content` | `ContactPageSeeder` | Contact page sections |
| `composer seed:demo-content` | `DemoContentSeeder` | Admin user, galleries, blog posts, testimonials, inquiries |

### Fresh Setup Sequence

```bash
# 1. Copy environment file
cp .env.example .env
# Edit .env with database credentials, APP_URL, etc.

# 2. Install PHP dependencies
composer install

# 3. Install npm dependencies and compile CSS
npm install
npm run build:css

# 4. Create database
mysql -u root -e "CREATE DATABASE mesh_photo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Run migrations
composer migrate

# 6. Seed with roles, permissions, and core CMS content
composer seed:roles-permissions
composer seed:core-cms

# 7. (Optional) Load demo content
composer seed:demo-content

# 8. Create .htaccess in public/ for Apache rewrite
# (See Deployment section)
```

---

## 32. AJAX Workflows

All AJAX endpoints accept `Content-Type: application/json` or form-encoded requests and return JSON. The `Request::expectsJson()` method detects AJAX callers.

### Standard JSON Response Format

```json
{
  "ok": true,
  "message": "Saved successfully.",
  "data": { ... },
  "errors": {},
  "meta": {}
}
```

On error:
```json
{
  "ok": false,
  "message": "Validation failed.",
  "data": {},
  "errors": { "field": "message" },
  "meta": { "status": 422 }
}
```

### Key AJAX Flows

#### Slug Generation

**Client:** On page/blog/gallery title input blur  
**Request:** `GET /admin/pages/slug?title=My+New+Page`  
**Response:** `{ "ok": true, "data": { "slug": "my-new-page", "available": true } }`  
**Action:** Populates slug field; shows availability indicator

#### Media Upload

**Client:** File input change or drop zone  
**Request:** `POST /admin/media/upload` with `multipart/form-data` file field `file`  
**Response:** `{ "ok": true, "data": { "id": 42, "url": "...", "thumb_url": "...", "mime_type": "image/jpeg" } }`  
**Action:** Appends thumbnail card to media grid

#### Media Picker

**Flow:**
1. Editor clicks "Choose Image" button
2. Modal opens: `GET /admin/media/picker?type=image`
3. User searches/filters: `GET /admin/media/search?q=keyword&type=image`
4. User clicks item → JS sets hidden `input[name="media_id"]`
5. Modal closes, selected thumbnail displayed in form

#### Gallery Media Reorder

**Client:** Drag-and-drop on media cards  
**Request:** `POST /admin/galleries/media/reorder` `{ gallery_id, media_id, direction: 'up'|'down' }`  
**Response:** `{ "ok": true }`

#### Blog Post Autosave

**Client:** `setInterval(30000, autosave)`  
**Request:** `POST /admin/blog/posts/autosave/{id}` `{ body: "..." }`  
**Response:** `{ "ok": true, "data": { "saved_at": "12:34:56" } }`  
**Action:** Updates "Last saved" indicator

#### Page Section Operations

All section operations are AJAX:
- `POST /{prefix}/pages/{pageId}/sections/store` → JSON with new section HTML
- `POST /{prefix}/pages/{pageId}/sections/update/{id}` → JSON with updated data
- `POST /{prefix}/pages/{pageId}/sections/reorder/{id}` → JSON success
- `POST /{prefix}/pages/{pageId}/sections/status/{id}` → JSON with new status

#### Inquiry / Booking Status Update

**Request:** `POST /admin/inquiries/status/{id}` `{ status: "in_progress" }`  
**Response:** `{ "ok": true, "data": { "status": "in_progress" } }`

---

## 33. Coding Standards

### PHP

- **PHP 8.2+** with `declare(strict_types=1)` in every file
- **PSR-4** autoloading; namespace: `App\` mapped to `app/`
- **Visibility:** Always explicit (`public`, `protected`, `private`)
- **Type declarations:** All function parameters and return types declared
- **Readonly properties:** Used where value doesn't change after construction
- **Named arguments:** Used for readability in complex calls
- **No global state** except helper functions and singletons via `bootstrap/helpers.php`
- **No direct `$_GET`/`$_POST`/`$_SERVER`** access outside Request class
- **No raw SQL** in controllers — delegate to models/repositories

### Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Class | PascalCase | `BlogPostService` |
| Method | camelCase | `createRevision()` |
| Property | camelCase | `$basePath` |
| Variable | camelCase | `$galleryMedia` |
| Constant | UPPER_SNAKE | `SESSION_KEY` |
| Database column | snake_case | `published_at` |
| Route path | kebab-case | `/gallery-categories` |
| Config key | snake_case | `settings_cache_ttl` |
| View file | snake_case or dot-path | `admin/blog/form.php` |

### Controller Pattern

```php
public function store(Request $request, Response $response): Response
{
    // 1. Validate CSRF
    // 2. Validate input
    // 3. Delegate to service
    // 4. Return response
}
```

### Service Pattern

```php
class BlogPostService
{
    public function __construct(
        private readonly BlogPost $model,
        private readonly BlogTaxonomyService $taxonomy,
        private readonly BlogRevisionService $revisions
    ) {}

    public function create(array $data, int $authorId): int
    {
        // Business logic
        // Returns new post ID
    }
}
```

### Comments

- Write comments for non-obvious decisions only
- PHPDoc blocks on public methods with complex param/return types
- No commented-out code in committed files

---

## 34. Testing Strategy

### Current Status

Automated tests are not yet implemented. This section defines the target testing strategy.

### Unit Tests

**Target:** Services, Validators, Helpers  
**Framework:** PHPUnit  
**Location:** `tests/Unit/`

Priority test cases:
- `Validator` — all rule combinations, nullable behavior
- `slugify()` — edge cases (unicode, special chars, empty)
- `MediaUploadService` — validation logic (without filesystem calls, use VFS)
- `LoginThrottleService` — attempt counting, lockout, reset
- `CSRF` — token generation, timing-safe verify
- `Session::fingerprint()` — components, hash stability
- `SeoService` — fallback chain behavior

### Feature/Integration Tests

**Target:** Controller flows, database interactions  
**Framework:** PHPUnit with real test database  
**Location:** `tests/Feature/`

Priority flows:
- Login success / failure / lockout
- Contact form submission end-to-end
- Blog post create → publish → revision creation
- Media upload (happy path, MIME rejection, size rejection)
- Settings save + cache invalidation
- Permission check on admin routes

### Browser/E2E Tests (Planned)

**Framework:** Playwright or Cypress  
**Scope:** Critical user journeys

Priority paths:
- Homepage renders without error
- Portfolio gallery browse → detail
- Contact form submission
- Admin login → create page → publish
- Admin media upload → attach to gallery

### Test Database Setup

```bash
DB_DATABASE=mesh_photo_test composer test
```

Separate `.env.testing` with test database credentials. Migrations run before each test suite; seeders provide deterministic base data.

---

## 35. Deployment Blueprint

### Server Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.2+ |
| Extensions | `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `openssl`, `json`, `intl` |
| Web Server | Apache 2.4+ with `mod_rewrite` |
| MySQL | 8.0+ (or MariaDB 10.5+) |
| Composer | 2.x |
| Node.js | 18+ (build only) |
| Disk | 2 GB for uploads |

### Apache Configuration

`.htaccess` in `public/`:
```apache
Options -Indexes
RewriteEngine On
RewriteBase /

# Remove index.php from URL
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

Apache VirtualHost (production):
```apache
<VirtualHost *:443>
    ServerName meshphoto.com
    DocumentRoot /var/www/mesh/public

    <Directory /var/www/mesh/public>
        AllowOverride All
        Require all granted
    </Directory>

    # SSL configuration
    SSLEngine on
    SSLCertificateFile ...
    SSLCertificateKeyFile ...
</VirtualHost>
```

### Directory Permissions

```bash
chmod 755 public/
chmod 775 storage/
chmod 775 storage/logs/
chmod 775 storage/cache/
chmod 775 storage/sessions/
chmod 775 public/uploads/
chown -R www-data:www-data storage/ public/uploads/
```

### Deployment Checklist

**Environment:**
- [ ] `.env` with `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `SESSION_SECURE=true` (HTTPS required)
- [ ] `MAIL_MAILER=smtp` with production credentials
- [ ] Strong database password
- [ ] `ADMIN_PATH` changed from default `/admin`

**Build:**
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build:css
```

**Database:**
```bash
composer migrate
composer seed:roles-permissions
composer seed:core-cms
```

**Create admin user:** (via `DemoContentSeeder` or direct SQL with bcrypt hash)

**Web server:**
- [ ] HTTPS enforced (redirect HTTP → HTTPS)
- [ ] Security headers set
- [ ] PHP `display_errors` = Off
- [ ] PHP `log_errors` = On, `error_log` configured
- [ ] OPcache enabled

**Storage:**
- [ ] `storage/` writable by web server
- [ ] `public/uploads/` writable by web server
- [ ] Symlink or configure actual upload path

**Post-deploy verification:**
- [ ] Homepage loads
- [ ] Admin login works
- [ ] File upload works
- [ ] Contact form sends email
- [ ] Settings cache writes successfully

### PHP OPcache Configuration (`php.ini`)

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0         ; 0 = never revalidate (clear on deploy)
opcache.validate_timestamps=0
opcache.save_comments=1
```

Reset on deploy: `php -r "opcache_reset();"`

---

## 36. Post-Launch Maintenance

### Routine Tasks

| Task | Frequency | Method |
|---|---|---|
| Clear old log files | Weekly | `find storage/logs -name "*.log" -mtime +30 -delete` |
| Check disk usage | Weekly | Monitor `public/uploads/` growth |
| Review new inquiries | Daily | Admin `/admin/inquiries` |
| Database backup | Daily | `mysqldump mesh_photo \| gzip > backup-$(date +%F).sql.gz` |
| Review activity logs | Weekly | Check for suspicious login attempts |

### Content Updates

All content changes go through the admin panel:
- No direct database edits for CMS content
- Settings changes trigger cache invalidation automatically
- Media uploads tracked with checksums

### PHP and Dependency Updates

```bash
# Check for outdated packages
composer outdated

# Update within version constraints
composer update

# Rebuild autoloader after updates
composer dump-autoload --optimize
```

### Troubleshooting

| Symptom | Likely Cause | Resolution |
|---|---|---|
| 500 on all pages | Missing `.env` or DB connection failure | Check `storage/logs/`, verify `.env` |
| Settings not updating | Cache stale | Delete `storage/cache/settings.php` |
| Uploads failing | Directory permissions | `chmod 775 public/uploads/` |
| Sessions dropping | Idle timeout | Increase `SESSION_IDLE_TIMEOUT` |
| Login redirect loop | `SESSION_SECURE=true` but HTTP | Enable HTTPS or set `SESSION_SECURE=false` |
| Missing CSS | Tailwind not compiled | Run `npm run build:css` |

---

## 37. Development Roadmap

### Tier 1 — Immediate Priorities

- [ ] Add `manage-services` to `config/permissions.php` (currently only in routes)
- [ ] Implement full user management UI (create, edit, assign roles, soft delete)
- [ ] Complete email notification system (admin notification on new inquiry/booking)
- [ ] Add password reset flow with email token
- [ ] Write PHPUnit test suite (Unit: services, validators; Feature: auth, forms)
- [ ] Add HTTP security headers (CSP, X-Frame-Options, etc.)
- [ ] Implement proper pagination (currently may be per-controller ad hoc)

### Tier 2 — Near-term Enhancements

- [ ] REST API endpoints for headless/mobile use (`routes/api.php`)
- [ ] WebP conversion for uploaded images
- [ ] Responsive `srcset` image variants (multiple breakpoint sizes)
- [ ] Blog post scheduled publishing (cron job to publish `status='scheduled'` posts with `published_at <= NOW()`)
- [ ] Album management UI (currently model/migration exists, no controller)
- [ ] Two-factor authentication for admin users
- [ ] Admin notification emails (new contact, new booking, low disk space)
- [ ] Inquiry export to CSV

### Tier 3 — Future Improvements

- [ ] Full-page caching for public site
- [ ] Redis session driver (replace file-based)
- [ ] S3 or CDN media storage adapter
- [ ] E2E browser test suite (Playwright)
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Admin audit log viewer UI (currently logs to DB but no admin UI)
- [ ] Gallery lightbox with keyboard navigation
- [ ] Blog RSS feed
- [ ] Client portal (password-protected gallery delivery)

---

## 38. Definition of Done

A feature or fix is considered done when all of the following are true:

### Code Quality

- [ ] `declare(strict_types=1)` in all new PHP files
- [ ] All methods have type declarations (params + return)
- [ ] No raw `$_GET`/`$_POST`/`$_SERVER` outside `Request`
- [ ] No direct SQL in controllers
- [ ] No unused variables, imports, or dead code
- [ ] CSRF verified on all state-changing endpoints

### Security

- [ ] All user input either validated or not used
- [ ] All DB queries use prepared statements
- [ ] Output escaped with `View::e()` or intentionally raw (CMS content)
- [ ] File uploads validated by MIME + extension + size
- [ ] No sensitive data in logs (passwords, tokens)

### Functionality

- [ ] Feature works end-to-end (happy path)
- [ ] Form validation errors display and repopulate inputs
- [ ] AJAX endpoints return correct JSON structure
- [ ] Soft deletes filter correctly (not showing deleted content)
- [ ] Permissions enforced (cannot access without required permission)

### Frontend

- [ ] Works on mobile (320px+), tablet, and desktop
- [ ] No JavaScript errors in browser console
- [ ] Forms submit without JS (progressive enhancement)
- [ ] Images have alt text
- [ ] New Tailwind classes are included in CSS build

### Documentation

- [ ] Complex logic includes inline comments (why, not what)
- [ ] New routes added to this blueprint
- [ ] New settings documented in Configuration Reference
- [ ] `MEMORY.md` / changelog updated if applicable

---

*This blueprint is the authoritative technical reference for Mesh Photography. All implementation decisions should align with the architecture described here. Update this document whenever the architecture changes.*
