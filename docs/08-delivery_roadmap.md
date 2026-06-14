# Mesh Photography — Delivery Roadmap

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Planning Horizon:** 16 weeks (4 months)

---

## Table of Contents

1. [Roadmap Overview](#1-roadmap-overview)
2. [Milestone Summary](#2-milestone-summary)
3. [Phase 1 — Foundation (Weeks 1–2)](#3-phase-1--foundation-weeks-12)
4. [Phase 2 — Public Site (Weeks 3–5)](#4-phase-2--public-site-weeks-35)
5. [Phase 3 — Admin Dashboard (Weeks 6–10)](#5-phase-3--admin-dashboard-weeks-610)
6. [Phase 4 — Advanced Features (Weeks 11–13)](#6-phase-4--advanced-features-weeks-1113)
7. [Phase 5 — Polish and Launch (Weeks 14–16)](#7-phase-5--polish-and-launch-weeks-1416)
8. [Post-Launch Roadmap (Tier 2 & 3)](#8-post-launch-roadmap-tier-2--3)
9. [Release Strategy](#9-release-strategy)
10. [Team Capacity Assumptions](#10-team-capacity-assumptions)

---

## 1. Roadmap Overview

```
Week  1  2  3  4  5  6  7  8  9 10 11 12 13 14 15 16
      ├──┤  ├─────────┤  ├──────────────────┤  ├──────┤  ├─────────┤
      Phase 1  Phase 2     Phase 3              Phase 4  Phase 5
      Fnd.     Public Site Admin Dashboard      Advanced  Launch
```

| Phase | Weeks | Primary Deliverable |
|---|---|---|
| 1 — Foundation | 1–2 | Auth API, project scaffolds, DB running, CI configured |
| 2 — Public Site | 3–5 | All public-facing pages live on staging |
| 3 — Admin Dashboard | 6–10 | Full content management system operational |
| 4 — Advanced Features | 11–13 | Blog revisions, email notifications, search, export |
| 5 — Polish & Launch | 14–16 | Production deployment, SEO, performance, monitoring |

---

## 2. Milestone Summary

| Milestone | Target Date | Success Criteria |
|---|---|---|
| **M1: Auth working** | End of Week 2 | Login/logout/refresh works; admin dashboard loads |
| **M2: Public site live (staging)** | End of Week 5 | All public pages render real data; contact/booking forms work |
| **M3: Admin MVP** | End of Week 8 | Galleries, blog, media, inquiries manageable from admin |
| **M4: Full admin suite** | End of Week 10 | All admin modules complete; settings, pages, users |
| **M5: Feature-complete** | End of Week 13 | All P1 features implemented; blog revisions, notifications |
| **M6: Production launch** | End of Week 16 | Live on production; SEO verified; monitoring active |

---

## 3. Phase 1 — Foundation (Weeks 1–2)

### Week 1

#### Backend
- [ ] Project directory structure setup (`backend/`)
- [ ] `.env`, Composer, config files
- [ ] Database migrations (all 36 tables)
- [ ] Seeders: roles, permissions, core CMS, demo content
- [ ] `App\Core\JWT` implementation (HS256)
- [ ] `App\Services\JwtService` (issue, refresh, revoke)
- [ ] `user_refresh_tokens` migration
- [ ] `CorsMiddleware` + `config/cors.php`

#### Frontend
- [ ] Vite + React 18 + TypeScript scaffold
- [ ] Tailwind CSS + design system tokens (colors, fonts, spacing)
- [ ] Google Fonts integration
- [ ] React Router v6 route tree (auth, admin, public)
- [ ] ESLint + Prettier + TypeScript strict mode
- [ ] Base TypeScript types (`ApiResponse`, `AdminUser`)

### Week 2

#### Backend
- [ ] `JwtMiddleware`, `PermissionMiddleware`
- [ ] `Auth\AuthController` (login, logout, refresh, me)
- [ ] Auth routes in `routes/auth.php`
- [ ] `GET /api/v1/health`
- [ ] Apache `.htaccess` routing configuration
- [ ] JSON error responses for all API routes
- [ ] Login rate limiting
- [ ] Vite proxy configured for dev

#### Frontend
- [ ] `AuthContext` with token memory storage
- [ ] Axios client with JWT interceptor + refresh queue
- [ ] `LoginPage` with React Hook Form + Zod
- [ ] `PrivateRoute` component
- [ ] `AuthLayout` (centered, minimal)
- [ ] `AdminLayout` shell (sidebar + topbar, empty content area)
- [ ] `PublicLayout` shell (navbar + footer, empty content area)

**Milestone M1:** Login → redirect to admin dashboard → token refresh → logout ✓

---

## 4. Phase 2 — Public Site (Weeks 3–5)

### Week 3

#### Backend
- [ ] `GET /api/v1/settings/public`
- [ ] `GET /api/v1/hero-slides`
- [ ] `GET /api/v1/galleries` (with cover, categories, pagination, featured filter)
- [ ] `GET /api/v1/galleries/categories`
- [ ] `GET /api/v1/galleries/{slug}` (media, seo, prev/next)
- [ ] `GET /api/v1/services` and `/{slug}`
- [ ] `GET /api/v1/testimonials`

#### Frontend
- [ ] `usePublicSettings` hook
- [ ] Full `Navbar` with mobile menu
- [ ] `Footer` component
- [ ] `PageMeta` component (React Helmet)
- [ ] `HeroCarousel` with auto-rotate and keyboard support
- [ ] `OptimizedImage` component (lazy loading, fallback)
- [ ] `GalleryCard` and `GalleryGrid` with category filter tabs
- [ ] `PortfolioPage` and `GalleryDetailPage`
- [ ] Lightbox integration (yet-another-react-lightbox)

### Week 4

#### Backend
- [ ] `GET /api/v1/blog/posts` (paginated, category/tag filters)
- [ ] `GET /api/v1/blog/posts/{slug}` (body, related, prev/next)
- [ ] `GET /api/v1/blog/posts/search`
- [ ] `GET /api/v1/blog/categories`, `/{slug}`, tags
- [ ] `GET /api/v1/pages/{slug}` (CMS page + sections)
- [ ] `POST /api/v1/contact` with `InquiryService` + email
- [ ] `POST /api/v1/booking` with `BookingRequestService` + email

#### Frontend
- [ ] `BlogPage` with sidebar (categories, recent posts, tags)
- [ ] `BlogPostPage` with DOMPurify HTML rendering
- [ ] `BlogCategoryPage`, `BlogTagPage`, `BlogSearchPage`
- [ ] `ServicesPage` and `ServiceDetailPage`
- [ ] `TestimonialsPage`
- [ ] `ContactPage` with form + error handling
- [ ] `BookingPage` with form + event fields

### Week 5

#### Backend + Frontend
- [ ] `CmsPage` — dynamic CMS page section rendering
- [ ] `NotFoundPage` (404)
- [ ] `GET /api/v1/sitemap` (XML generation)
- [ ] JSON-LD structured data on blog posts and gallery pages
- [ ] Loading skeleton components for all public pages
- [ ] Full mobile responsive audit (320px, 375px, 768px, 1024px, 1440px)
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Accessibility: keyboard navigation, focus management, ARIA roles

**Milestone M2:** Public site live on staging with all pages functional ✓

---

## 5. Phase 3 — Admin Dashboard (Weeks 6–10)

### Week 6

#### Backend
- [ ] `GET /api/v1/admin/dashboard` (metrics)
- [ ] Media admin API: list, upload, update, archive, delete
- [ ] `MediaUploadService` complete pipeline

#### Frontend
- [ ] `DashboardPage` with metric cards
- [ ] `AdminSidebar` with permission-based nav links
- [ ] `AdminTopbar` with user menu
- [ ] `MediaLibraryPage` (grid, type filter, search)
- [ ] `MediaUploader` (drop zone, progress bar)
- [ ] `MediaPicker` modal (reused in all content forms)

### Week 7

#### Backend
- [ ] Gallery admin API (full CRUD + category sync + SEO meta)
- [ ] Gallery media API (attach, update, reorder, remove)
- [ ] Gallery categories admin API
- [ ] `GET /api/v1/admin/slug-check`

#### Frontend
- [ ] `GalleryListPage` (DataTable with status filter, actions)
- [ ] `GalleryFormPage` (create/edit, MediaPicker, category checkboxes, SEO panel)
- [ ] `GalleryMediaManager` (attach, caption, sort, remove)
- [ ] `SlugInput` with debounced check
- [ ] `SeoPanel` collapsible

### Week 8

#### Backend
- [ ] Blog post admin API (CRUD, autosave, taxonomy sync)
- [ ] Blog categories and tags admin API
- [ ] Inquiry admin API (list, detail, status update, notes)
- [ ] Booking admin API (list, detail, status update)

#### Frontend
- [ ] `BlogPostListPage` with status tabs
- [ ] `BlogPostFormPage` with Tiptap editor
- [ ] 30-second autosave implementation
- [ ] Blog category management page (inline CRUD)
- [ ] `InquiryListPage` with status tabs
- [ ] `InquiryDetailPage` with notes
- [ ] `BookingListPage` and `BookingDetailPage`

**Milestone M3:** Core admin modules (galleries, blog, media, inquiries) operational ✓

### Week 9

#### Backend
- [ ] Services admin API (CRUD)
- [ ] Testimonials admin API (CRUD)
- [ ] Hero slides admin API (CRUD)
- [ ] Pages admin API (CRUD)
- [ ] Settings admin API (get/put + cache invalidation)

#### Frontend
- [ ] `ServiceListPage` and `ServiceFormPage`
- [ ] `TestimonialListPage` + modal form
- [ ] `HeroSlideListPage` + form with sort order management
- [ ] `PagesListPage`
- [ ] `SettingsPage` with grouped sections

### Week 10

#### Backend
- [ ] User management admin API (list, create, update, soft delete)

#### Frontend
- [ ] `UsersPage` with role assignment
- [ ] `SortableList` drag-to-reorder for hero slides and gallery media
- [ ] `DataTable` reusable component (finalized)
- [ ] `ConfirmDialog` for all destructive actions
- [ ] `StatusBadge` component for all status values
- [ ] Admin route permission guards (hide nav links, return 403 on direct access)

**Milestone M4:** Full admin suite complete ✓

---

## 6. Phase 4 — Advanced Features (Weeks 11–13)

### Week 11

#### Backend
- [ ] Blog post revision: create on publish, list endpoint, restore endpoint
- [ ] Scheduled post publishing check (or cron instruction)
- [ ] Password reset: request token, verify, reset endpoints
- [ ] Password reset email template

#### Frontend
- [ ] Blog post revision history sidebar
- [ ] Restore revision action with confirmation
- [ ] `published_at` datetime picker (shown when status = 'scheduled')
- [ ] Password reset request page (`/admin/forgot-password`)
- [ ] Password reset form page (`/admin/reset-password?token=...`)

### Week 12

#### Backend
- [ ] Admin notification email on new inquiry (PHPMailer template)
- [ ] Admin notification email on new booking
- [ ] Blog RSS feed endpoint (`GET /api/v1/blog/feed.xml`)
- [ ] Inquiry CSV export endpoint

#### Frontend
- [ ] Social sharing buttons on blog post pages
- [ ] Gallery keyboard navigation (left/right arrows without lightbox)
- [ ] Blog archive widget (month/year groupings in sidebar)
- [ ] Inquiry CSV export button in admin
- [ ] RSS link in blog page `<head>`

### Week 13

- [ ] WebP conversion for image uploads (if PHP GD supports it on prod)
- [ ] Activity log viewer in admin (read-only table)
- [ ] Two-factor auth investigation (TOTP library evaluation)
- [ ] Performance optimization pass: identify slow API endpoints, add missing indexes
- [ ] TanStack Query prefetching on hover for nav links

**Milestone M5:** Feature-complete ✓

---

## 7. Phase 5 — Polish and Launch (Weeks 14–16)

### Week 14 — Security and Performance

- [ ] Implement all HTTP security headers in Apache or middleware
- [ ] Enable PHP OPcache with production config
- [ ] Browser asset caching headers for compiled JS/CSS
- [ ] Lighthouse CI on staging: all pages ≥85 mobile score
- [ ] PHP unit test suite pass (target ≥80% coverage on services)
- [ ] Playwright E2E test suite pass
- [ ] Review and tighten CSP for Google Fonts and uploads CDN

### Week 15 — Content and SEO

- [ ] Load all real photography content (galleries, services, blog posts)
- [ ] Admin training session with studio owner
- [ ] Set all SEO meta titles and descriptions
- [ ] Verify sitemap includes all published content
- [ ] Google Search Console: submit sitemap, set up property
- [ ] robots.txt correctness
- [ ] Canonical URLs on all pages
- [ ] Open Graph tags verified with Facebook debugger
- [ ] JSON-LD validation with Google Rich Results Test

### Week 16 — Production Launch

- [ ] Configure production Apache VirtualHost (HTTPS, headers)
- [ ] Let's Encrypt SSL certificate
- [ ] DNS cutover
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE=true`
- [ ] All environment variables set in production `.env`
- [ ] Run production migrations and seeders
- [ ] Verify all email flows (contact, booking confirmations)
- [ ] Verify file uploads (GD extension, permissions)
- [ ] Set up MySQL daily backup cron
- [ ] Set up log rotation
- [ ] UptimeRobot monitoring for `/api/v1/health`
- [ ] Sentry error tracking (PHP SDK + React SDK)
- [ ] 48-hour post-launch error log review

**Milestone M6: Production Launch ✓**

---

## 8. Post-Launch Roadmap (Tier 2 & 3)

### Tier 2 (Month 2–3 Post-Launch)

| Feature | Estimated Effort |
|---|---|
| WebP image conversion on upload | 1 week |
| Responsive srcset image variants (320px, 640px, 1200px) | 1 week |
| Blog scheduled publishing (cron trigger) | 3 days |
| Album management UI (model exists, no admin UI) | 1 week |
| GitHub Actions CI/CD pipeline | 3 days |
| Full-page caching for public API endpoints (5-min TTL) | 1 week |
| Redis session driver | 3 days |

### Tier 3 (Month 4–6 Post-Launch)

| Feature | Estimated Effort |
|---|---|
| Client proofing portal (password-protected gallery) | 3–4 weeks |
| Stripe payment/deposit integration | 2–3 weeks |
| Two-factor authentication (TOTP) | 1–2 weeks |
| S3/Cloudflare R2 media storage adapter | 1–2 weeks |
| Next.js migration for public site (SSG for SEO) | 3–5 weeks |
| E2E Playwright test suite (comprehensive) | 2 weeks |

---

## 9. Release Strategy

### Environments

| Environment | URL | Audience | Deploy Trigger |
|---|---|---|---|
| Development | `localhost:5173` | Developer | Manual |
| Staging | `staging.meshphoto.com` | Internal / QA | Manual push |
| Production | `meshphoto.com` | Public | Approved deploy |

### Deployment Steps (Manual, Pre-CI/CD)

```bash
# 1. Build frontend
cd frontend && npm run build

# 2. Upload dist/ to production server
rsync -av dist/ user@server:/var/www/mesh/frontend/dist/

# 3. Update backend
cd ../backend
composer install --no-dev --optimize-autoloader
php database/console.php migrate

# 4. Restart PHP FPM (if needed)
sudo systemctl restart php8.2-fpm

# 5. Reset OPcache
php -r "opcache_reset();"

# 6. Verify health endpoint
curl https://meshphoto.com/api/v1/health
```

### Rollback Plan

```bash
# Keep last 3 frontend builds on server:
# /var/www/mesh/frontend/dist-2026-06-14/
# /var/www/mesh/frontend/dist-2026-06-07/
# /var/www/mesh/frontend/dist-current/ (symlink)

# Roll back frontend:
ln -sfn /var/www/mesh/frontend/dist-2026-06-07 /var/www/mesh/frontend/dist-current

# Roll back backend:
git checkout v1.0.0
composer install --no-dev
php database/console.php migrate:rollback
```

---

## 10. Team Capacity Assumptions

This roadmap assumes:

| Role | Allocation | Focus |
|---|---|---|
| Backend Developer | Full-time (1 person) | PHP API, database, email, media |
| Frontend Developer | Full-time (1 person) | React SPA, admin dashboard, public site |
| Designer / QA | Part-time (0.5 person) | UI review, accessibility, cross-browser |
| Product Owner | Part-time (0.25 person) | Requirements, content, UAT sign-off |

**If team is smaller (1 developer total):** Extend timeline to 24 weeks. Backend phases first, then frontend.

**If team is larger (2 frontend developers):** Phase 3 can be parallelized between public-facing admin (inquiries/bookings) and content management (galleries/blog). Reduce to 12 weeks.
