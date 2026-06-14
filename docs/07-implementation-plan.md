# Mesh Photography — Implementation Plan

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Methodology:** Iterative, feature-complete phases with clear completion criteria

---

## Table of Contents

1. [Overview](#1-overview)
2. [Phase 1 — Foundation](#2-phase-1--foundation)
3. [Phase 2 — Core Public Site](#3-phase-2--core-public-site)
4. [Phase 3 — Admin Dashboard](#4-phase-3--admin-dashboard)
5. [Phase 4 — Advanced Features](#5-phase-4--advanced-features)
6. [Phase 5 — Polish and Launch](#6-phase-5--polish-and-launch)
7. [Cross-Cutting Concerns](#7-cross-cutting-concerns)
8. [Task Dependency Graph](#8-task-dependency-graph)
9. [Risk Register](#9-risk-register)

---

## 1. Overview

The implementation is organized into five sequential phases. Each phase delivers a testable, deployable increment. Backend and frontend tasks within each phase can proceed in parallel once shared interfaces (API contracts from `06-api-design.md`) are agreed upon.

**Team model:** Two concurrent workstreams
- **Backend workstream** — PHP developer(s), implements the REST API
- **Frontend workstream** — React developer(s), builds the SPA consuming the API

**Coordination points:**
- Both teams agree on API response shapes before implementation
- Frontend can begin with mock data (MSW or hardcoded JSON) until backend endpoints are ready
- Shared TypeScript types in `frontend/src/types/` serve as the integration contract

---

## 2. Phase 1 — Foundation

**Goal:** Both backend and frontend scaffolds are running with authentication working end-to-end.

**Duration estimate:** 1–2 weeks

### Backend Tasks

| ID | Task | Priority | Estimated Effort |
|---|---|---|---|
| BE-1.1 | Set up project directory structure (`backend/`) | P0 | 2h |
| BE-1.2 | Configure `.env`, `config/*.php`, Composer | P0 | 1h |
| BE-1.3 | Run all 36 database migrations | P0 | 1h |
| BE-1.4 | Run seeders: roles-permissions, core CMS, demo content | P0 | 1h |
| BE-1.5 | Implement `App\Core\JWT` (HS256 encode/decode/verify) | P0 | 4h |
| BE-1.6 | Implement `App\Services\JwtService` (issue/refresh/revoke) | P0 | 4h |
| BE-1.7 | Add `user_refresh_tokens` migration | P0 | 1h |
| BE-1.8 | Implement `CorsMiddleware` with `config/cors.php` | P0 | 2h |
| BE-1.9 | Implement `JwtMiddleware` (Bearer token verification) | P0 | 2h |
| BE-1.10 | Implement `Auth\AuthController` (login, logout, refresh, me) | P0 | 4h |
| BE-1.11 | Register auth routes in `routes/auth.php` | P0 | 1h |
| BE-1.12 | Add `GET /api/v1/health` endpoint | P0 | 1h |
| BE-1.13 | Verify Apache `.htaccess` routes `/api/*` to PHP | P0 | 1h |
| BE-1.14 | Update `ErrorHandler` to always return JSON for `/api/*` requests | P0 | 2h |
| BE-1.15 | Add `manage-services` permission to `config/permissions.php` and seed | P1 | 1h |

**Phase 1 Backend Complete When:**
- `POST /api/v1/auth/login` returns JWT access token + refresh cookie
- `POST /api/v1/auth/refresh` returns new access token
- `GET /api/v1/auth/me` returns user with roles/permissions
- `POST /api/v1/auth/logout` revokes refresh token
- All 401/403 responses return JSON envelope
- CORS headers present on all responses

---

### Frontend Tasks

| ID | Task | Priority | Estimated Effort |
|---|---|---|---|
| FE-1.1 | Initialize Vite + React 18 + TypeScript project | P0 | 1h |
| FE-1.2 | Configure Tailwind CSS with full design system tokens | P0 | 2h |
| FE-1.3 | Add Google Fonts (Cormorant Garamond, Inter) to `index.html` | P0 | 30m |
| FE-1.4 | Set up React Router v6 with public/admin/auth layout structure | P0 | 3h |
| FE-1.5 | Configure TanStack Query with QueryClient | P0 | 1h |
| FE-1.6 | Implement `AuthContext` with access token memory storage | P0 | 4h |
| FE-1.7 | Implement Axios client with JWT interceptor + refresh queue | P0 | 4h |
| FE-1.8 | Build `LoginPage` with React Hook Form + Zod validation | P0 | 3h |
| FE-1.9 | Implement `PrivateRoute` component | P0 | 1h |
| FE-1.10 | Build `AuthLayout` (centered login layout) | P0 | 1h |
| FE-1.11 | Implement `AdminLayout` (sidebar + topbar shell) | P0 | 3h |
| FE-1.12 | Implement `PublicLayout` (navbar + footer shell) | P0 | 3h |
| FE-1.13 | Set up ESLint + Prettier + TypeScript strict mode | P0 | 1h |
| FE-1.14 | Configure Vite dev proxy for `/api/*` to backend | P0 | 30m |
| FE-1.15 | Define base TypeScript types (`ApiResponse`, `AdminUser`, etc.) | P0 | 2h |

**Phase 1 Frontend Complete When:**
- Dev server runs without errors
- `/admin/login` form connects to real backend auth API
- Successful login redirects to `/admin` dashboard
- Failed login shows validation errors
- Unauthorized access to `/admin/*` redirects to login
- Logout clears auth state and redirects to login

---

## 3. Phase 2 — Core Public Site

**Goal:** All public-facing pages are functional and consumable by real visitors.

**Duration estimate:** 2–3 weeks

### Backend Tasks

| ID | Task | Priority |
|---|---|---|
| BE-2.1 | Implement `GET /api/v1/settings/public` | P0 |
| BE-2.2 | Implement `GET /api/v1/hero-slides` | P0 |
| BE-2.3 | Implement `GET /api/v1/galleries` (with pagination, category filter, featured) | P0 |
| BE-2.4 | Implement `GET /api/v1/galleries/categories` | P0 |
| BE-2.5 | Implement `GET /api/v1/galleries/{slug}` (with media, seo, prev/next) | P0 |
| BE-2.6 | Implement `GET /api/v1/services` | P0 |
| BE-2.7 | Implement `GET /api/v1/services/{slug}` | P0 |
| BE-2.8 | Implement `GET /api/v1/testimonials` | P0 |
| BE-2.9 | Implement `GET /api/v1/blog/posts` (paginated, filters) | P0 |
| BE-2.10 | Implement `GET /api/v1/blog/posts/{slug}` (with body, related, seo, prev/next) | P0 |
| BE-2.11 | Implement `GET /api/v1/blog/posts/search` | P1 |
| BE-2.12 | Implement `GET /api/v1/blog/categories` and `/{slug}` | P1 |
| BE-2.13 | Implement `GET /api/v1/blog/tags` and `/{slug}` | P1 |
| BE-2.14 | Implement `GET /api/v1/pages/{slug}` (CMS page with sections) | P0 |
| BE-2.15 | Implement `POST /api/v1/contact` (InquiryService + email) | P0 |
| BE-2.16 | Implement `POST /api/v1/booking` (BookingRequestService + email) | P0 |
| BE-2.17 | Implement `GET /api/v1/sitemap` (XML generation) | P1 |
| BE-2.18 | Write `ContactValidator` and `BookingValidator` domain classes | P0 |
| BE-2.19 | Ensure SeoService returns fallback meta for all entity types | P0 |

### Frontend Tasks

| ID | Task | Priority |
|---|---|---|
| FE-2.1 | Implement `PageMeta` component (React Helmet Async) | P0 |
| FE-2.2 | Implement `usePublicSettings` query hook | P0 |
| FE-2.3 | Build full `Navbar` with mobile hamburger menu | P0 |
| FE-2.4 | Build `Footer` with settings data | P0 |
| FE-2.5 | Build `HeroCarousel` component (auto-rotate, pause, keyboard) | P0 |
| FE-2.6 | Build `HomePage` assembling all sections | P0 |
| FE-2.7 | Build `GalleryCard` and `GalleryGrid` components | P0 |
| FE-2.8 | Build `PortfolioPage` with category filter tabs | P0 |
| FE-2.9 | Build `GalleryDetailPage` with `Lightbox` integration | P0 |
| FE-2.10 | Build `ServicesPage` and `ServiceDetailPage` | P0 |
| FE-2.11 | Build `BlogPage` with sidebar (categories, tags, recent posts) | P0 |
| FE-2.12 | Build `BlogPostPage` with DOMPurify body rendering | P0 |
| FE-2.13 | Build `BlogCategoryPage` and `BlogTagPage` | P1 |
| FE-2.14 | Build `BlogSearchPage` | P1 |
| FE-2.15 | Build `TestimonialsPage` | P0 |
| FE-2.16 | Build `ContactPage` with `ContactForm` | P0 |
| FE-2.17 | Implement `useSubmitInquiry` mutation hook | P0 |
| FE-2.18 | Build `BookingPage` with `BookingForm` | P0 |
| FE-2.19 | Implement `useSubmitBooking` mutation hook | P0 |
| FE-2.20 | Build `CmsPage` (renders dynamic CMS page sections) | P0 |
| FE-2.21 | Build `NotFoundPage` (404) | P0 |
| FE-2.22 | Implement JSON-LD structured data on blog posts and galleries | P1 |
| FE-2.23 | Implement image lazy loading + `OptimizedImage` component | P0 |
| FE-2.24 | Build loading skeleton components for all public pages | P1 |
| FE-2.25 | Verify mobile responsiveness across all breakpoints (320–1440px) | P0 |

**Phase 2 Complete When:**
- All public pages load with real API data
- Contact and booking forms submit successfully
- Lightbox gallery viewer works on mobile and desktop
- Blog search returns results
- CMS pages (About, Terms, Privacy) render sections correctly
- Sitemap.xml accessible at `/sitemap.xml`
- Page titles and meta descriptions render correctly

---

## 4. Phase 3 — Admin Dashboard

**Goal:** Admin users can manage all content types through the React SPA dashboard.

**Duration estimate:** 3–4 weeks

### Backend Tasks — Admin API

| ID | Task | Priority |
|---|---|---|
| BE-3.1 | Implement `GET /api/v1/admin/dashboard` (metrics) | P0 |
| BE-3.2 | Implement `PermissionMiddleware` with JWT payload check | P0 |
| BE-3.3 | Full CRUD: Gallery admin API (with media attach/reorder/remove) | P0 |
| BE-3.4 | Full CRUD: Gallery Categories admin API | P0 |
| BE-3.5 | Full CRUD: Media admin API (upload, update, archive, delete) | P0 |
| BE-3.6 | Full CRUD: Blog Posts admin API (with autosave) | P0 |
| BE-3.7 | Full CRUD: Blog Categories and Tags admin API | P0 |
| BE-3.8 | Full CRUD: Services admin API | P0 |
| BE-3.9 | Full CRUD: Testimonials admin API | P0 |
| BE-3.10 | Full CRUD: Hero Slides admin API | P0 |
| BE-3.11 | Full CRUD: Pages admin API | P0 |
| BE-3.12 | Inquiry management API (list, detail, status, notes) | P0 |
| BE-3.13 | Booking management API (list, detail, status) | P0 |
| BE-3.14 | Settings get/put API | P0 |
| BE-3.15 | User management API (list, create, update, soft delete) | P1 |
| BE-3.16 | `GET /api/v1/admin/slug-check` utility | P1 |
| BE-3.17 | `BlogRevisionService` — create revision on publish, list, restore | P1 |
| BE-3.18 | Blog post media attach/update/remove/reorder | P1 |
| BE-3.19 | Admin notifications email on new inquiry/booking | P2 |
| BE-3.20 | Password reset flow (request + verify + reset endpoints) | P2 |

### Frontend Tasks — Admin Dashboard

| ID | Task | Priority |
|---|---|---|
| FE-3.1 | Build `DashboardPage` with metric cards | P0 |
| FE-3.2 | Build `AdminSidebar` with nav links and permission-based visibility | P0 |
| FE-3.3 | Build `AdminTopbar` with user menu and logout | P0 |
| FE-3.4 | Implement `usePermission` hook for conditional rendering | P0 |
| FE-3.5 | Build `MediaLibraryPage` (grid, filters, search, upload zone) | P0 |
| FE-3.6 | Build `MediaUploader` with progress indicator | P0 |
| FE-3.7 | Build `MediaPicker` modal (used across all content forms) | P0 |
| FE-3.8 | Build `GalleryListPage` (DataTable with status, actions) | P0 |
| FE-3.9 | Build `GalleryFormPage` (create/edit with all fields) | P0 |
| FE-3.10 | Build `GalleryMediaManager` (attach, reorder, caption, remove) | P0 |
| FE-3.11 | Build `SlugInput` with debounced availability check | P0 |
| FE-3.12 | Build `SeoPanel` collapsible section | P0 |
| FE-3.13 | Build `BlogPostListPage` with status tabs | P0 |
| FE-3.14 | Build `BlogPostFormPage` with `RichTextEditor` (Tiptap) | P0 |
| FE-3.15 | Implement 30-second autosave for blog post body | P1 |
| FE-3.16 | Build `BlogCategoriesPage` with inline create/edit/delete | P1 |
| FE-3.17 | Build `InquiryListPage` with status tabs | P0 |
| FE-3.18 | Build `InquiryDetailPage` with notes and status update | P0 |
| FE-3.19 | Build `BookingListPage` and `BookingDetailPage` | P0 |
| FE-3.20 | Build `ServiceListPage` and `ServiceFormPage` | P0 |
| FE-3.21 | Build `TestimonialListPage` and form modal | P0 |
| FE-3.22 | Build `HeroSlideListPage` and form with sort order | P0 |
| FE-3.23 | Build `PagesListPage` | P0 |
| FE-3.24 | Build `SettingsPage` grouped by section | P0 |
| FE-3.25 | Build `UsersPage` with role assignment | P1 |
| FE-3.26 | Build `DataTable` reusable component with sort and pagination | P0 |
| FE-3.27 | Build `ConfirmDialog` for all destructive actions | P0 |
| FE-3.28 | Implement `SortableList` with drag-to-reorder (dnd-kit) | P1 |
| FE-3.29 | Build `StatusBadge` component for all status values | P0 |

**Phase 3 Complete When:**
- Admin can log in and see dashboard metrics
- Admin can create, edit, publish, and delete galleries and attach/reorder images
- Admin can upload media with progress feedback
- Admin can write, publish, and manage blog posts with Tiptap editor
- Admin can view and update inquiry and booking statuses
- Admin can update site settings and see changes reflected on public site
- All forms show validation errors inline
- All destructive actions require confirmation

---

## 5. Phase 4 — Advanced Features

**Goal:** Implement higher-tier features that complete the platform.

**Duration estimate:** 2–3 weeks

### Backend Tasks

| ID | Task | Priority |
|---|---|---|
| BE-4.1 | Blog post scheduled publishing (check `published_at <= NOW()`) | P1 |
| BE-4.2 | Blog post revision history list + restore endpoint | P1 |
| BE-4.3 | Password reset request/verify/reset API | P1 |
| BE-4.4 | Admin notification emails on new inquiry and booking | P1 |
| BE-4.5 | Activity log viewer endpoint (`GET /api/v1/admin/activity-logs`) | P2 |
| BE-4.6 | Two-factor authentication (TOTP) — challenge/verify endpoints | P3 |
| BE-4.7 | WebP conversion on image upload | P2 |
| BE-4.8 | Responsive srcset variant generation (multiple sizes) | P2 |
| BE-4.9 | Blog post RSS feed (`GET /api/v1/blog/feed.xml`) | P2 |
| BE-4.10 | Inquiry CSV export (`GET /api/v1/admin/inquiries/export`) | P2 |

### Frontend Tasks

| ID | Task | Priority |
|---|---|---|
| FE-4.1 | Blog post revision history sidebar + restore action | P1 |
| FE-4.2 | Scheduled post `published_at` datetime picker | P1 |
| FE-4.3 | Password reset request + reset form pages | P1 |
| FE-4.4 | Activity log viewer in admin | P2 |
| FE-4.5 | Image srcset rendering with responsive breakpoints | P2 |
| FE-4.6 | Admin inquiry CSV export button | P2 |
| FE-4.7 | Social sharing buttons on blog post pages | P2 |
| FE-4.8 | Gallery previous/next keyboard navigation | P1 |
| FE-4.9 | Blog archive widget (month/year groupings) | P2 |

---

## 6. Phase 5 — Polish and Launch

**Goal:** Production hardening, testing, SEO verification, deployment.

**Duration estimate:** 1–2 weeks

### All Tasks

| ID | Task | Priority |
|---|---|---|
| PL-1 | Implement HTTP security headers (CSP, X-Frame-Options, HSTS, etc.) | P0 |
| PL-2 | Enable PHP OPcache in production `php.ini` | P0 |
| PL-3 | Configure Apache VirtualHost for production (HTTPS, `DocumentRoot`) | P0 |
| PL-4 | Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE=true` | P0 |
| PL-5 | Run PHPUnit test suite; achieve ≥80% coverage on services | P0 |
| PL-6 | Run Playwright E2E tests on staging environment | P0 |
| PL-7 | Lighthouse audit on public pages (target ≥85 mobile) | P0 |
| PL-8 | Google Search Console: submit sitemap, verify meta tags | P0 |
| PL-9 | Verify WCAG 2.1 AA with axe browser extension | P0 |
| PL-10 | Load test with k6 or Apache Bench (100 concurrent users) | P1 |
| PL-11 | Configure daily MySQL backup cron job | P0 |
| PL-12 | Set up uptime monitoring (UptimeRobot or Pingdom) for `/api/v1/health` | P0 |
| PL-13 | Configure log rotation for `storage/logs/` | P0 |
| PL-14 | Seed production database with all real content | P0 |
| PL-15 | Verify all email flows (contact confirmation, booking confirmation) | P0 |
| PL-16 | Verify file uploads work on production server (permissions, GD extension) | P0 |
| PL-17 | DNS propagation and SSL certificate activation | P0 |
| PL-18 | Redirect `www` to non-www (or vice versa) with 301 | P0 |
| PL-19 | Review error logs after launch — first 48 hours | P0 |

---

## 7. Cross-Cutting Concerns

These apply across all phases and workstreams:

### API Contract Stability

Before frontend begins implementing a feature against an endpoint, the backend must provide:
- Confirmed response shape (or mock via Postman/OpenAPI)
- All error response formats

Use **MSW (Mock Service Worker)** in the frontend for offline development against unimplemented endpoints.

### Type Safety Across Boundary

The TypeScript types in `frontend/src/types/models.ts` and `api.ts` are the authoritative contract. Both teams must update these when API shapes change.

### Error Handling Consistency

- All backend errors return the standard envelope (`ok`, `message`, `errors`)
- All frontend API errors are handled via `extractApiErrors()` utility
- Never swallow errors silently — log to console in dev, report via toast in UI

### Performance Budgets

| Asset | Budget |
|---|---|
| Initial JS bundle (gzipped) | < 200KB |
| CSS (gzipped) | < 15KB |
| LCP (mobile 4G) | < 2.5 seconds |
| API response (p95, read) | < 200ms |

### Security Checklist (per feature)

- [ ] JWT verified on all protected endpoints
- [ ] Input validated before use
- [ ] SQL via prepared statements only
- [ ] File uploads checked by MIME, extension, size
- [ ] No sensitive data in logs or API responses

---

## 8. Task Dependency Graph

```
Phase 1 (Foundation)
  ├── BE-1.1–1.4 (DB setup)
  │     └── BE-1.5–1.6 (JWT core)
  │           └── BE-1.7–1.14 (Auth API)
  │
  └── FE-1.1–1.4 (Project setup)
        └── FE-1.5–1.9 (Auth infrastructure)
              └── FE-1.10–1.15 (Layouts, types)
                    ↓
Phase 2 (Public Site)
  ├── BE-2.1–2.19 (all public API endpoints)
  │       [parallel with FE using MSW mocks]
  │
  └── FE-2.1–2.25 (all public pages)
        [can start with mocks, switch to real API]
              ↓
Phase 3 (Admin Dashboard)
  ├── BE-3.1–3.20 (all admin API endpoints)
  │
  └── FE-3.1–3.29 (all admin pages and components)
        [MediaPicker needed before GalleryForm, BlogPostForm, etc.]
              ↓
Phase 4 (Advanced)
  ├── BE-4.1–4.10
  └── FE-4.1–4.9
              ↓
Phase 5 (Polish + Launch)
  └── PL-1–PL-19
```

**Critical path:** BE-1 → BE-2 (public data endpoints) → FE-2 (public pages) → LAUNCH VIABLE.

Admin features (Phase 3) can ship in a subsequent release if needed for a soft launch.

---

## 9. Risk Register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| PHP GD extension unavailable on prod server | Medium | High | Verify in Phase 1; provide Imagick fallback |
| JWT secret rotation causes mass logout | Low | Medium | Document rotation procedure; implement graceful token transition |
| React SPA not indexed by Google (SEO) | Medium | High | Implement React Helmet; consider Next.js migration if crawl issues confirmed post-launch |
| Large image uploads time out (PHP `max_execution_time`) | Medium | Medium | Set `max_execution_time=60` for upload requests; client-side size validation |
| Email delivery failures (SMTP issues) | Low | Medium | Log all email failures; use reputable SMTP provider; monitor delivery |
| Media upload directory permissions wrong on prod | Medium | High | Include chmod in deployment checklist; automated deployment script |
| Tiptap editor produces unsafe HTML | Low | Medium | Sanitize `body` on read with DOMPurify; also sanitize on write in backend |
| Token refresh race condition (parallel requests) | Low | Medium | Implemented: single-flight refresh queue in Axios interceptor |
| `deleted_at` filter missing from a query | Low | Medium | Code review checklist; integration tests cover soft-delete filtering |
| CORS misconfiguration blocking frontend | Medium | High | Phase 1 includes CORS setup; test from frontend dev server on day 1 |
