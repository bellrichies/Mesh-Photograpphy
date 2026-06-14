# Mesh Photography — Agent Instructions

You are working on **Mesh Photography**, a decoupled web application consisting of a PHP 8.2+ REST API backend and a React 18 SPA frontend. Read this file fully before writing a single line of code.

---

## Project Structure

```
mesh-photo/
├── backend/              ← PHP 8.2+ custom MVC REST API
│   ├── app/
│   │   ├── Controllers/  ← Thin: validate → service → JSON response
│   │   ├── Core/         ← Router, Request, Response, DB, JWT, Middleware
│   │   ├── Models/       ← Active-record-style, PDO, soft deletes
│   │   ├── Repositories/ ← Complex queries only (3 exist)
│   │   └── Services/     ← Business logic lives here (20 services)
│   ├── config/           ← app.php, database.php, jwt.php, permissions.php
│   ├── migrations/       ← 36 SQL files, numbered sequentially
│   ├── routes/           ← api.php (public), admin-api.php (authenticated)
│   ├── public/           ← index.php (entry point), uploads/
│   └── .env              ← Never committed; copy from .env.example
└── frontend/             ← React 18 + TypeScript 5 + Vite 5 SPA
    ├── src/
    │   ├── api/          ← Axios client, query hooks (one file per resource)
    │   ├── components/
    │   │   ├── layout/   ← Navbar, Footer, PublicLayout, AdminLayout, AuthLayout
    │   │   ├── public/   ← Public-facing page components
    │   │   ├── admin/    ← Admin dashboard components
    │   │   └── ui/       ← Primitives: Button, Input, FormField, etc.
    │   ├── hooks/        ← Custom hooks (useScrollReveal, useHeroCarousel, etc.)
    │   ├── pages/
    │   │   ├── public/   ← HomePage, PortfolioPage, BlogPostPage, etc.
    │   │   └── admin/    ← DashboardPage, GalleryFormPage, etc.
    │   ├── store/        ← AuthContext.tsx (JWT memory + refresh logic)
    │   ├── types/        ← api.ts (envelopes), models.ts (all 17 interfaces)
    │   └── utils/        ← cn, format, media, api-errors
    └── public/           ← Static assets
```

---

## Architecture Rules — Never Violate These

1. **Backend is API-only.** No PHP templates, no view rendering, no HTML output. Every response is JSON.
2. **Frontend is SPA-only.** No server-side rendering. React handles all routing via React Router v6.
3. **Auth is JWT-only.** No PHP sessions, no cookies for auth identity. Access token lives in React memory (`AuthContext`). Refresh token lives in `httpOnly SameSite=Strict` cookie.
4. **Business logic belongs in Services.** Controllers validate the request and call a service. Services do the work. Models are data access only.
5. **All responses use the standard envelope.** See [API Response Format](#api-response-format) below.
6. **CORS middleware runs before routing.** It must handle OPTIONS preflight and attach headers to all responses, including error responses.
7. **Soft deletes everywhere.** Never `DELETE FROM` content tables. Set `deleted_at = NOW()`. Models always filter `WHERE deleted_at IS NULL`.
8. **No `any` in TypeScript.** Strict mode is on. Every variable, prop, and return value must be typed.

---

## Backend — PHP Standards

### Mandatory File Header

```php
<?php

declare(strict_types=1);

namespace App\Controllers\Api;
```

Every `.php` file starts with `declare(strict_types=1)`.

### Controller Pattern

Controllers are thin. The entire job is: parse input → validate → call service → return JSON.

```php
public function store(Request $request): void
{
    $data = $request->json();

    $errors = $this->validate($data, [
        'title'   => 'required|string|max:255',
        'slug'    => 'required|string|max:255',
    ]);

    if ($errors) {
        $this->json(['errors' => $errors], 422);
        return;
    }

    $gallery = $this->galleryService->create($data, $request->user()->id);
    $this->json(['data' => $gallery->toArray()], 201);
}
```

### API Response Format

**Always** return this envelope:

```json
// Success — single resource
{ "data": { ... } }

// Success — collection
{ "data": [...], "meta": { "current_page": 1, "per_page": 20, "total": 84, "last_page": 5 } }

// Validation error
{ "message": "Validation failed", "errors": { "field": ["message"] } }

// Other error
{ "message": "Human-readable error message" }
```

HTTP status codes: `200` OK, `201` Created, `204` No Content (delete), `400` Bad Request, `401` Unauthorized, `403` Forbidden, `404` Not Found, `422` Unprocessable Entity, `500` Server Error.

### Model Pattern

```php
// Soft delete — always use this, never hard delete
public function softDelete(int $id): bool
{
    $stmt = $this->db->prepare('UPDATE galleries SET deleted_at = NOW() WHERE id = ?');
    return $stmt->execute([$id]);
}

// Queries always exclude soft-deleted rows
public function findPublished(): array
{
    $stmt = $this->db->prepare(
        'SELECT * FROM galleries WHERE deleted_at IS NULL AND status = ? ORDER BY sort_order ASC'
    );
    $stmt->execute(['published']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### JWT Implementation

Use `App\Core\JWT` (custom HS256 implementation). Never install a JWT library.

```php
// Issue tokens after login
$tokens = $this->jwtService->issueTokens($user);
$this->jwtService->setRefreshCookie($tokens['refresh_token']);
$this->json(['data' => ['access_token' => $tokens['access_token'], 'user' => ...]]);

// Protect a route — add middleware to route registration
$router->post('/admin/galleries', [GalleryController::class, 'store'])
       ->middleware([JwtMiddleware::class, PermissionMiddleware::for('manage-galleries')]);
```

### Database — PDO Only

Never use string concatenation for SQL values. Always use prepared statements with `?` placeholders.

```php
// CORRECT
$stmt = $this->db->prepare('SELECT * FROM galleries WHERE id = ? AND deleted_at IS NULL');
$stmt->execute([$id]);

// NEVER DO THIS
$stmt = $this->db->query("SELECT * FROM galleries WHERE id = $id");
```

### File Uploads

Uploaded files go through a 10-step security pipeline in `MediaService`:
1. MIME type check (allowlist, not denylist)
2. Extension validation
3. File size limit
4. Image content validation (GD/Imagick must parse it)
5. Strip EXIF metadata
6. Generate UUID filename — never use the original filename
7. Move to `public/uploads/{year}/{month}/`
8. Generate thumbnail
9. Persist to `media` table
10. Return `MediaRecord`

Never write file upload logic outside `MediaService`.

---

## Frontend — React/TypeScript Standards

### Query Hook Pattern

Every API resource gets a file in `src/api/` with a key factory and hooks:

```typescript
// src/api/galleries.ts
export const galleryKeys = {
  all:    () => ['galleries'] as const,
  public: () => [...galleryKeys.all(), 'public'] as const,
  list:   (f: GalleryFilters) => [...galleryKeys.public(), f] as const,
  detail: (slug: string) => [...galleryKeys.public(), 'detail', slug] as const,
};

export function useGalleries(filters: GalleryFilters = {}) {
  return useQuery({
    queryKey: galleryKeys.list(filters),
    queryFn:  () => fetchGalleries(filters),
    staleTime: 5 * 60 * 1000,
  });
}
```

### Auth — Access Token in Memory

The access token is stored in a module-level variable in `src/api/client.ts`, not in `localStorage` or `sessionStorage`.

```typescript
let accessToken: string | null = null;
export const setAccessToken = (t: string | null) => { accessToken = t; };
export const getAccessToken = () => accessToken;
```

The Axios request interceptor reads `accessToken` and attaches it to every request. The 401 response interceptor calls `/auth/refresh` once (single-flight queue pattern) and retries. Never implement this logic outside `src/api/client.ts`.

### Component Responsibility Rules

- **Page components** (`src/pages/`) — data fetching, loading/error states, pass data to sections
- **Section components** (`src/components/public/`, `src/components/admin/`) — layout and display logic, receive data as props
- **UI primitives** (`src/components/ui/`) — no API calls, no business logic, fully reusable

### Styling — Tailwind Only

No inline `style={{}}` objects except for dynamic values that Tailwind cannot express (e.g., `style={{ backgroundImage: \`url(${url})\` }}`). No CSS modules. No emotion. No styled-components.

Use the `cn()` utility for conditional classes:

```typescript
import { cn } from '@/utils/cn'; // clsx + tailwind-merge

<div className={cn('base-class', condition && 'conditional-class', variant === 'primary' && 'variant-class')} />
```

### Design Tokens — Never Hardcode Colors

Use the custom Tailwind tokens defined in `tailwind.config.ts`:

| Token | Value | Usage |
|---|---|---|
| `text-charcoal` | #1a1a1a | Primary body text |
| `text-taupe` | #a8998a | Secondary/muted text |
| `text-bronze` | #9a7b5c | Brand accent, links, CTA |
| `bg-ivory` | #faf9f7 | Primary page background |
| `bg-espresso` | #1b1714 | Dark CTA sections |
| `text-ivory` | #faf9f7 | Text on dark backgrounds |
| `text-gold` | #c4a77d | Decorative accents |

Fonts: `font-display` (Cormorant Garamond) for headings, `font-body` (Inter) for all other text.

### Image Rules

```tsx
// Decorative images (hero backgrounds) — empty alt, no announcement to screen readers
<img src={slide.url} alt="" loading="eager" fetchPriority="high" width={1920} height={1080} />

// Content images — descriptive alt from CMS data
<img src={gallery.cover.url} alt={gallery.cover.alt_text ?? gallery.title} loading="lazy" width={480} height={600} />

// Always include explicit width and height to prevent CLS
```

### Forms — React Hook Form + Zod

```typescript
const schema = z.object({
  email: z.string().email('Invalid email address'),
  message: z.string().min(10, 'Message must be at least 10 characters'),
});

const { register, handleSubmit, formState: { errors } } = useForm<z.infer<typeof schema>>({
  resolver: zodResolver(schema),
});
```

Never use uncontrolled inputs outside of `react-hook-form`. Never build custom form validation logic.

### Admin-Authored HTML

Blog post body content from the API is HTML generated by Tiptap. Sanitize it before rendering:

```typescript
import DOMPurify from 'dompurify';
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(post.body_html) }} />
```

Never render `dangerouslySetInnerHTML` on any other content.

---

## Routing Reference

### Public Routes (no auth)

| Path | Component |
|---|---|
| `/` | `HomePage` |
| `/portfolio` | `PortfolioPage` |
| `/portfolio/:slug` | `GalleryDetailPage` |
| `/services` | `ServicesPage` |
| `/services/:slug` | `ServiceDetailPage` |
| `/blog` | `BlogIndexPage` |
| `/blog/:slug` | `BlogPostPage` |
| `/contact` | `ContactPage` |
| `/booking` | `BookingPage` |
| `/about` | `AboutPage` |
| `/:slug` | `CmsPage` (catch-all for CMS pages) |

### Admin Routes (JWT required)

All admin routes are prefixed `/admin`. The `PrivateRoute` wrapper redirects unauthenticated users to `/admin/login`.

### Backend API Prefix

All API routes are prefixed `/api/v1/`. No exceptions.

---

## Common Pitfalls

**Backend**
- Do not skip `CorsMiddleware` for any route — including error routes
- Do not return `true`/`false` from controllers; always return JSON
- Do not access `$_POST` directly; use `$request->json()` or `$request->input()`
- Do not commit `.env`; it contains `JWT_SECRET` and database credentials
- Do not use `md5` or `sha1` for password hashing; use `password_hash($pw, PASSWORD_BCRYPT)`
- Do not delete rows from `galleries`, `blog_posts`, `media`, `services`, `testimonials`, `hero_slides`, `pages`, `inquiries`, `booking_requests`

**Frontend**
- Do not store the access token in `localStorage` — XSS risk
- Do not call `axios` directly from components; use query hooks from `src/api/`
- Do not import from `src/components/admin/` in public page components
- Do not use `useEffect` to fetch data; use TanStack Query hooks
- Do not render blog post HTML without `DOMPurify.sanitize()`
- Do not add `console.log` to committed code
- Do not use `any` — if you need an escape hatch use `unknown` and narrow it

---

## Key Documentation

Read these before implementing a feature:

| What | Where |
|---|---|
| Full DB schema (36 tables) | `docs/blueprint.md` §3–§14 |
| All API endpoints with request/response shapes | `docs/06-api-design.md` |
| PHP backend architecture + all code stubs | `docs/04-backend-architecture.md` |
| React architecture + all component stubs | `docs/05-frontend-architecture.md` |
| Homepage design spec (sections, components, a11y) | `docs/homepage.md` |
| Implementation task list | `docs/07-implementation-plan.md` |
| Full backend implementation prompt | `docs/prompts/backend.md` |
| Full frontend implementation prompt | `docs/prompts/frontend.md` |
