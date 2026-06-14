# Mesh Photography — System Architecture

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Architecture:** Decoupled — PHP REST API + React SPA

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [System Components](#2-system-components)
3. [Data Flow Diagrams](#3-data-flow-diagrams)
4. [Infrastructure Architecture](#4-infrastructure-architecture)
5. [Security Architecture](#5-security-architecture)
6. [Integration Architecture](#6-integration-architecture)
7. [Caching Architecture](#7-caching-architecture)
8. [File Storage Architecture](#8-file-storage-architecture)
9. [Scalability Architecture](#9-scalability-architecture)
10. [Monitoring and Observability](#10-monitoring-and-observability)

---

## 1. Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              INTERNET                                       │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │ HTTPS
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         Apache Web Server                                   │
│                       (mod_rewrite, mod_ssl)                                │
│                                                                             │
│  ┌────────────────────────┐         ┌────────────────────────────────────┐  │
│  │   /  → React SPA        │         │  /api/* → PHP Backend (index.php)  │  │
│  │   (static build files)  │         │  /uploads/* → Static media files   │  │
│  └────────────────────────┘         └────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
         │                                          │
         ▼                                          ▼
┌──────────────────┐                   ┌────────────────────────────────────┐
│   React SPA      │                   │       PHP Custom MVC               │
│                  │                   │                                    │
│  • Public Pages  │  REST API calls   │  ┌─────────────────────────────┐  │
│  • Admin Panel   │ ◄────────────────►│  │       Router                │  │
│  • Auth UI       │  JWT Bearer token │  │  /api/v1/* (public)         │  │
│                  │                   │  │  /api/v1/admin/* (protected) │  │
│  TanStack Query  │                   │  │  /api/v1/auth/* (auth)       │  │
│  React Router    │                   │  └──────────┬──────────────────┘  │
│  Tailwind CSS    │                   │             │                      │
└──────────────────┘                   │  ┌──────────▼──────────────────┐  │
                                       │  │      Middleware Chain        │  │
                                       │  │  CorsMiddleware              │  │
                                       │  │  JwtMiddleware (protected)   │  │
                                       │  │  PermissionMiddleware        │  │
                                       │  └──────────┬──────────────────┘  │
                                       │             │                      │
                                       │  ┌──────────▼──────────────────┐  │
                                       │  │      Controllers             │  │
                                       │  │  (thin — validate, delegate) │  │
                                       │  └──────────┬──────────────────┘  │
                                       │             │                      │
                                       │  ┌──────────▼──────────────────┐  │
                                       │  │       Services               │  │
                                       │  │  (business logic)            │  │
                                       │  └──────────┬──────────────────┘  │
                                       │             │                      │
                                       │  ┌──────────▼──────────────────┐  │
                                       │  │   Repositories / Models      │  │
                                       │  │  (database access)           │  │
                                       │  └──────────┬──────────────────┘  │
                                       └─────────────┼──────────────────────┘
                                                     │
                    ┌─────────────────────────────────┤
                    │                                  │
         ┌──────────▼──────────┐          ┌───────────▼──────────────┐
         │   MySQL / MariaDB   │          │    File System           │
         │                     │          │                           │
         │  mesh_photo DB      │          │  storage/cache/           │
         │  36 tables          │          │  storage/logs/            │
         │  Indexed queries    │          │  storage/sessions/        │
         │  UTF8MB4            │          │  public/uploads/          │
         └─────────────────────┘          └───────────────────────────┘
```

---

## 2. System Components

### 2.1 React SPA (Frontend)

**Responsibilities:**
- Render all public-facing pages (homepage, portfolio, blog, services, contact, booking)
- Render the admin dashboard and all content management screens
- Manage client-side routing via React Router
- Fetch and cache API data via TanStack Query
- Manage JWT access token lifecycle (in-memory storage)
- Handle form validation via React Hook Form + Zod
- Provide responsive, accessible UI using Tailwind CSS design system

**Key characteristics:**
- Single HTML entry point (`index.html`) loaded by Apache
- JavaScript bundle served as static files (`dist/`)
- No server-side rendering in MVP (React Helmet for SEO meta)
- Admin routes protected by `<PrivateRoute>` component that checks auth state
- All API communication through `src/api/` modules using Axios

---

### 2.2 PHP REST API (Backend)

**Responsibilities:**
- Handle all HTTP requests to `/api/v1/*`
- Authenticate API clients via JWT Bearer tokens
- Enforce RBAC permissions on protected endpoints
- Execute business logic through the service layer
- Interact with the database via parameterized PDO queries
- Process and store file uploads with security validation
- Send transactional emails via PHPMailer/SMTP
- Generate XML sitemap and robots.txt
- Write structured JSON audit and error logs

**Key characteristics:**
- Single entry point (`public/index.php`) with Apache `mod_rewrite`
- Custom PHP MVC (no Laravel/Symfony)
- Stateless for API consumers (JWT); stateful for admin panel session backup
- JSON-only responses for all `/api/*` routes
- CORS headers on all API responses (configured via `config/cors.php`)

---

### 2.3 MySQL Database

**Responsibilities:**
- Persistent storage for all application data
- 36 tables covering users, content, media, galleries, blog, inquiries, bookings, settings, logs
- Soft deletes via `deleted_at` timestamps
- InnoDB engine with FK constraints
- Indexed on slug, status, deleted_at, sort_order, created_at, FK columns

**Connection:** PDO with `ATTR_EMULATE_PREPARES = false` and `ATTR_ERRMODE = ERRMODE_EXCEPTION`

---

### 2.4 File System Storage

| Path | Contents | Access |
|---|---|---|
| `public/uploads/images/YYYY/MM/` | Original uploaded images | Public HTTP |
| `public/uploads/variants/YYYY/MM/` | 640px JPEG thumbnails | Public HTTP |
| `public/uploads/documents/YYYY/MM/` | PDF documents | Public HTTP |
| `public/uploads/videos/YYYY/MM/` | Video files | Public HTTP |
| `storage/logs/` | JSON error/audit logs | Server only |
| `storage/cache/` | Settings PHP file cache | Server only |
| `storage/sessions/` | PHP session files | Server only |

---

### 2.5 Apache Web Server

**Responsibilities:**
- Serve the React SPA static build from `/`
- Route all `/api/*` requests to PHP `public/index.php`
- Serve `public/uploads/*` as static files
- Enforce HTTPS in production
- Set security headers (CSP, X-Frame-Options, etc.)
- Block directory listing
- Enable `mod_rewrite` for SPA routing and PHP dispatch

**`.htaccess` (root / React SPA directory):**
```apache
Options -Indexes
RewriteEngine On

# Serve API requests directly to PHP
RewriteRule ^api/(.*)$ /backend/public/index.php [QSA,L]

# Serve uploads from backend
RewriteRule ^uploads/(.*)$ /backend/public/uploads/$1 [L]

# Serve React SPA for all other routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [L]
```

---

## 3. Data Flow Diagrams

### 3.1 Public Page Load (React SPA)

```
Browser
  │
  ├─1─► Apache: GET /portfolio
  │      └─► Serves: frontend/dist/index.html
  │
  ├─2─► React Router renders <PortfolioPage>
  │
  ├─3─► TanStack Query: GET /api/v1/galleries
  │      └─► Apache routes to PHP backend/public/index.php
  │           └─► Router → CorsMiddleware
  │                └─► Api\GalleryController@index
  │                     └─► GalleryService → GalleryRepository → MySQL
  │                          └─► JSON response: { ok: true, data: [...galleries] }
  │
  └─4─► React renders gallery grid with fetched data
         └─► React Helmet sets <title>, <meta description>
```

---

### 3.2 Admin Authentication Flow

```
Browser (LoginPage)
  │
  ├─1─► POST /api/v1/auth/login { email, password }
  │      └─► Apache → PHP: Auth\AuthController@login
  │           ├─► LoginThrottleService: check attempts
  │           ├─► User model: findByEmail → password_verify()
  │           ├─► SecurityLogger: logLogin()
  │           ├─► JwtService: issueAccessToken() (15min, HS256)
  │           ├─► JwtService: issueRefreshToken() (7d, stored in DB + httpOnly cookie)
  │           └─► Response: { ok: true, data: { access_token, user } }
  │
  ├─2─► React: store access_token in AuthContext (memory)
  │      └─► Redirect to /admin/dashboard
  │
  ├─3─► Dashboard: GET /api/v1/admin/dashboard
  │      └─► JwtMiddleware: verify Bearer token
  │           └─► Admin\DashboardController@index → metrics queries → JSON
  │
  └─4─► Token expiry (15min) → Axios interceptor:
         POST /api/v1/auth/refresh (sends httpOnly cookie)
         └─► New access_token issued, stored in memory
              └─► Original request retried
```

---

### 3.3 Media Upload Flow

```
Admin (MediaLibraryPage)
  │
  ├─1─► User drops file onto MediaUploader component
  │
  ├─2─► FormData POST /api/v1/admin/media/upload
  │      Authorization: Bearer <access_token>
  │      │
  │      └─► JwtMiddleware → PermissionMiddleware('manage-media')
  │           └─► Admin\MediaController@upload
  │                └─► MediaUploadService::upload($file, $userId)
  │                     ├─► Validate PHP upload error
  │                     ├─► is_uploaded_file() check
  │                     ├─► finfo_file() MIME detection
  │                     ├─► Extension whitelist check
  │                     ├─► Size limit check (≤10MB)
  │                     ├─► getimagesize() dimension check (≤6000×6000)
  │                     ├─► SHA-256 checksum
  │                     ├─► move_uploaded_file() to public/uploads/images/YYYY/MM/
  │                     ├─► ensureDirectoryProtection() → .htaccess
  │                     ├─► INSERT INTO media
  │                     ├─► GD: create 640px thumbnail
  │                     ├─► INSERT INTO media_variants
  │                     └─► Response: { ok: true, data: { id, uuid, url, thumb_url } }
  │
  └─3─► React: append new media card to MediaGrid
```

---

### 3.4 Contact Form Submission

```
Browser (ContactPage)
  │
  ├─1─► User fills and submits ContactForm
  │
  ├─2─► React Hook Form validates via Zod schema (client-side)
  │
  ├─3─► POST /api/v1/contact { name, email, phone, subject, message }
  │      │
  │      └─► Api\ContactController@store
  │           ├─► Server-side Validator (name, email, message rules)
  │           ├─► InquiryService::create($data)
  │           │    └─► INSERT INTO inquiries (status='new')
  │           ├─► MailService::send() → confirmation email to visitor
  │           ├─► MailService::send() → notification email to admin (planned)
  │           └─► Response: { ok: true, message: 'Your message has been sent.' }
  │
  └─4─► React: show success Toast, reset form
```

---

## 4. Infrastructure Architecture

### 4.1 Production Environment

```
┌─────────────────────────────────────────────────────────────────┐
│                    Production Server (VPS/Dedicated)            │
│                                                                 │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │  Apache 2.4 (Port 443, SSL/TLS)                           │ │
│  │                                                            │ │
│  │  VirtualHost meshphoto.com                                │ │
│  │    DocumentRoot /var/www/mesh/frontend/dist               │ │
│  │    Alias /api /var/www/mesh/backend/public                │ │
│  │    Alias /uploads /var/www/mesh/backend/public/uploads    │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌──────────────────┐  ┌───────────────────────────────────┐   │
│  │  PHP 8.2 (FPM)  │  │  MySQL 8.0                        │   │
│  │  OPcache enabled │  │  mesh_photo database              │   │
│  │  GD extension    │  │  InnoDB, utf8mb4                  │   │
│  └──────────────────┘  └───────────────────────────────────┘   │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  File System                                             │  │
│  │  /var/www/mesh/backend/public/uploads/  (writable)      │  │
│  │  /var/www/mesh/backend/storage/         (writable)      │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                         │
              ┌──────────▼────────────┐
              │   Let's Encrypt SSL   │
              │   Certbot auto-renew  │
              └───────────────────────┘
```

### 4.2 Development Environment

```
Developer Machine
  │
  ├── XAMPP / Laragon / PHP Built-in Server (port 8000)
  │   └── PHP 8.2+, MySQL, Apache
  │
  └── Vite Dev Server (port 5173)
      └── React SPA with HMR
          └── Proxy: /api/* → localhost:8000/api/*
```

---

## 5. Security Architecture

### 5.1 Authentication Layers

| Context | Mechanism | Storage |
|---|---|---|
| API consumers (React SPA) | JWT HS256 Bearer token | Memory (access) + httpOnly cookie (refresh) |
| Admin panel web | PHP session (legacy fallback) | Server-side file |

### 5.2 Authorization Model

```
Request arrives at protected API endpoint
  │
  ├─► JwtMiddleware: verify Bearer token signature + expiry
  │    └─► Decode payload: { sub: userId, roles: [...], permissions: [...], exp }
  │
  └─► PermissionMiddleware('manage-galleries')
       └─► Check decoded JWT permissions array
            ├─► Allowed → proceed to controller
            └─► Denied → 403 { ok: false, message: 'Forbidden' }
```

### 5.3 CORS Configuration

```php
// config/cors.php
return [
    'allowed_origins'   => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
    'allowed_methods'   => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers'   => ['Content-Type', 'Authorization', 'X-Request-Id'],
    'exposed_headers'   => ['X-Request-Id'],
    'allow_credentials' => (bool) env('CORS_ALLOW_CREDENTIALS', true),
    'max_age'           => 86400,
];
```

`CorsMiddleware` runs on every request before routing and handles `OPTIONS` preflight requests.

### 5.4 Security Headers

Added via Apache `<VirtualHost>` config or PHP response:

```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
Header always set Content-Security-Policy "default-src 'self'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self'; connect-src 'self' https://api.meshphoto.com"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### 5.5 Upload Security Pipeline

```
File received by PHP
  │
  ├─ 1. Validate PHP $_FILES error code = UPLOAD_ERR_OK
  ├─ 2. is_uploaded_file() — prevents path traversal
  ├─ 3. finfo_file() MIME detection — ignores client-provided type
  ├─ 4. Extension whitelist — blocks .php, .phtml, .phar, .exe, .sh, .bat, etc.
  ├─ 5. File size check — ≤ UPLOAD_MAX_FILE_SIZE_MB (default 10MB)
  ├─ 6. Image dimension check — ≤ 6000×6000px (via getimagesize())
  ├─ 7. move_uploaded_file() — atomic, OS-level safe move
  ├─ 8. .htaccess written to directory — blocks script execution
  └─ 9. Stored name: `YYYYMMDDHHiiss-<20randomhex>.<ext>` — no original name
```

---

## 6. Integration Architecture

### 6.1 Email — PHPMailer + SMTP

```
MailService::send(to, subject, htmlBody, replyTo)
  │
  └─► PHPMailer instance
       ├─► SMTP: host, port, encryption from config/mail.php
       ├─► Auth: username/password from .env
       ├─► From: MAIL_FROM_ADDRESS
       └─► send() → SMTP server → recipient inbox
```

**Supported SMTP providers:** Mailgun, Postmark, SendGrid, Amazon SES, Gmail SMTP

**Email types:**
- Inquiry confirmation (to visitor)
- Booking confirmation (to visitor)
- New inquiry notification (to admin) — Planned
- New booking notification (to admin) — Planned
- Password reset (to user) — Planned

### 6.2 Google Fonts

```html
<!-- In React public/index.html -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
```

### 6.3 Analytics (Planned)

Integration point: inject Google Analytics / Plausible via React `useEffect` in root `App.tsx` or via `index.html` script tag.

### 6.4 Future Integrations

| Integration | Trigger | Priority |
|---|---|---|
| Stripe | Payment/deposit collection | Tier 3 |
| S3/Cloudflare R2 | Media file storage adapter | Tier 3 |
| Redis | Session and cache driver | Tier 3 |
| GitHub Actions | CI/CD pipeline | Tier 2 |

---

## 7. Caching Architecture

### 7.1 Request-level Cache (In-memory)

Settings and reusable blocks are cached in PHP static variables within the request lifecycle. After the first access, subsequent reads within the same request are free.

### 7.2 File-based Settings Cache

```
Request: app_settings_all()
  │
  ├─► Check static $cache (in-memory) → return if hit
  │
  ├─► Check storage/cache/settings.php age < TTL (300s)
  │    └─► require() file → return array → populate static $cache
  │
  └─► Query SELECT * FROM settings
       └─► Write storage/cache/settings.php with LOCK_EX
            └─► Populate static $cache → return
```

**Invalidation:** `app_settings_refresh_cache()` deletes `storage/cache/settings.php` then rebuilds it from DB. Called after `POST /api/v1/admin/settings`.

### 7.3 PHP OPcache (Server-level)

PHP bytecode compilation is cached by OPcache. Config (production):
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0   ; Never revalidate — clear on deploy
opcache.revalidate_freq=0
```

### 7.4 Browser Caching (Frontend)

Vite builds assets with content-hash filenames (`app-[hash].js`). Apache serves:
```apache
# Cache compiled assets forever
<FilesMatch "\.(js|css|woff2|png|jpg|webp)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
</FilesMatch>

# Never cache HTML (SPA entry point)
<FilesMatch "\.html$">
    Header set Cache-Control "no-cache, no-store, must-revalidate"
</FilesMatch>
```

### 7.5 TanStack Query Cache (React)

React Query caches API responses in memory with configurable `staleTime` and `gcTime`. Invalidation is explicit (on mutations). Example:

```typescript
// After creating a gallery, invalidate the gallery list cache
queryClient.invalidateQueries({ queryKey: ['galleries'] });
```

Default stale times:
- Public content (galleries, blog, services): 5 minutes
- Admin listings: 1 minute
- Dashboard metrics: 30 seconds

---

## 8. File Storage Architecture

### 8.1 Local Storage Layout

```
backend/public/uploads/
├── images/
│   └── YYYY/MM/
│       ├── 20260614120000-a1b2c3d4e5f6g7h8i9j0.jpg  (original)
│       └── .htaccess                                   (blocks execution)
├── variants/
│   └── YYYY/MM/
│       ├── 20260614120000-a1b2c3d4e5f6g7h8i9j0-thumb.jpg
│       └── .htaccess
├── documents/
│   └── YYYY/MM/
│       └── .htaccess
└── videos/
    └── YYYY/MM/
        └── .htaccess
```

### 8.2 URL Pattern

```
Original: https://meshphoto.com/uploads/images/2026/06/20260614120000-{hex}.jpg
Thumb:    https://meshphoto.com/uploads/variants/2026/06/20260614120000-{hex}-thumb.jpg
```

React uses `VITE_UPLOADS_BASE_URL` env variable to construct these URLs from the `directory` and `stored_name` fields returned by the API.

### 8.3 Media URL Helper (Frontend)

```typescript
// src/utils/media.ts
export function mediaUrl(directory: string, storedName: string): string {
  const base = import.meta.env.VITE_UPLOADS_BASE_URL;
  return `${base}/${directory}/${storedName}`;
}

export function preferredMediaUrl(media: MediaRecord): string {
  if (media.thumb) return mediaUrl(media.thumb.directory, media.thumb.stored_name);
  return mediaUrl(media.directory, media.stored_name);
}
```

---

## 9. Scalability Architecture

### 9.1 Current Scale Targets

| Metric | Target |
|---|---|
| Concurrent visitors | 500 |
| Media storage | Up to 50GB |
| DB records | Thousands (content), low millions (logs) |
| API requests/minute | ~1,000 |

### 9.2 Scale-up Path

**Phase 1 (current):** Single server, local files, file-based cache

**Phase 2 (growth):** 
- Add Redis for session storage (`SESSION_DRIVER=redis`)
- Add CDN (Cloudflare) in front of Apache for static assets and edge caching
- Add full-page cache for public API endpoints (5-minute TTL)
- MySQL query optimization: `EXPLAIN ANALYZE` on slow queries, add composite indexes

**Phase 3 (scale):**
- S3/Cloudflare R2 for media storage — add `StorageAdapter` interface, swap local for S3
- Read replicas for MySQL — queries split between primary (writes) and replica (reads)
- Consider Next.js for public site (SSG/ISR) to reduce API load and improve SEO
- Horizontal scaling: PHP API becomes stateless (JWT already stateless); shared S3 storage

### 9.3 Database Indexes

Critical indexes (in addition to PK and UNIQUE):

| Table | Index | Reason |
|---|---|---|
| galleries | `(status, deleted_at, sort_order)` | Public gallery listing |
| blog_posts | `(status, published_at, deleted_at)` | Chronological public listing |
| blog_posts | `FULLTEXT(title, excerpt, body)` | Blog search |
| media | `(status, file_type, created_at)` | Media library filtering |
| inquiries | `(status, created_at)` | Admin inquiry list |
| booking_requests | `(status, event_date)` | Booking pipeline |
| activity_logs | `(user_id, action, created_at)` | Audit log queries |
| seo_meta | `(entity_type, entity_id)` | Per-entity SEO lookup |

---

## 10. Monitoring and Observability

### 10.1 Application Logs

Structured JSON logs at `storage/logs/app-YYYY-MM-DD.log`:

```json
{
  "timestamp": "2026-06-14T12:00:00+00:00",
  "request_id": "a1b2c3d4e5f6g7h8",
  "level": "error",
  "status": 500,
  "method": "POST",
  "path": "/api/v1/admin/galleries",
  "ip": "203.0.113.42",
  "user_agent": "Mozilla/5.0...",
  "user_id": 1,
  "exception": "RuntimeException",
  "message": "Gallery slug already exists",
  "file": "/app/Services/GalleryService.php",
  "line": 78,
  "trace": "..."
}
```

### 10.2 Audit Logs

Security and content events in `activity_logs` table:

| Event | `action` value |
|---|---|
| Successful login | `login` |
| Failed login | `login_failed` |
| Logout | `logout` |
| Gallery created | `gallery.created` |
| Image uploaded | `media.uploaded` |
| Inquiry status changed | `inquiry.status_changed` |

### 10.3 Error Monitoring (Production)

**Recommended:** Integrate Sentry PHP SDK for automatic error capture with stack traces and context. Add Sentry React SDK for frontend JS errors.

```bash
# Backend
composer require sentry/sentry-php

# Frontend
npm install @sentry/react
```

### 10.4 Health Check Endpoint

```
GET /api/v1/health
Response: { "ok": true, "db": "connected", "version": "1.0.0" }
```

Used by uptime monitoring (UptimeRobot, Pingdom, etc.) to alert on downtime.

### 10.5 Performance Monitoring

- **Server-side:** PHP OPcache stats, MySQL slow query log (`long_query_time=1`)
- **Frontend:** Core Web Vitals via Google Search Console + Lighthouse CI
- **API:** Request timing via `X-Response-Time` response header (added by middleware)
