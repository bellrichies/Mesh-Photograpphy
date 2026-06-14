# Mesh Photography — Build Blueprint

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Status:** Approved  
> **Architecture Model:** Decoupled — PHP REST API Backend + React SPA Frontend

---

## Table of Contents

1. [Architecture Decision Records](#1-architecture-decision-records)
2. [Technology Stack](#2-technology-stack)
3. [Repository Structure](#3-repository-structure)
4. [Backend Directory Structure](#4-backend-directory-structure)
5. [Frontend Directory Structure](#5-frontend-directory-structure)
6. [Environment Configuration](#6-environment-configuration)
7. [Build Toolchain](#7-build-toolchain)
8. [Development Workflow](#8-development-workflow)
9. [Coding Standards](#9-coding-standards)
10. [Dependency Management](#10-dependency-management)
11. [Definition of Done](#11-definition-of-done)

---

## 1. Architecture Decision Records

### ADR-001: Decoupled Frontend/Backend

**Decision:** The frontend (React SPA) and backend (PHP REST API) are separate applications communicating exclusively via JSON REST API.

**Rationale:**
- Clear separation of concerns between presentation and business logic
- Frontend and backend can be developed, deployed, and scaled independently
- Enables future native mobile clients, headless integrations, or SSG without backend changes
- React SPA provides a modern, responsive UX with superior state management

**Trade-offs:**
- Requires CORS configuration
- Public pages need SEO consideration (React with Helmet + meta management; SSR considered for Tier 2)
- JWT authentication adds token refresh complexity vs. simple session cookies

**Status:** Accepted

---

### ADR-002: PHP Custom MVC (No Laravel/Symfony)

**Decision:** Continue with the existing custom PHP 8.2+ MVC framework rather than adopting Laravel or Symfony.

**Rationale:**
- Existing architecture is already implemented and battle-tested within this project
- Zero dependency on opinionated framework upgrade cycles
- Full understanding and control of every component (Router, Session, Auth, Validator, etc.)
- PHPMailer and vlucas/phpdotenv are the only external PHP dependencies
- Lightweight and fast — no framework overhead

**Trade-offs:**
- More boilerplate than Laravel's Eloquent/Auth/Mail abstractions
- Fewer community packages; must implement missing features manually
- Requires disciplined architecture adherence without framework guardrails

**Status:** Accepted

---

### ADR-003: JWT Authentication for API

**Decision:** Use stateless JWT (HS256) access tokens for all React ↔ API communication.

**Rationale:**
- Stateless tokens eliminate server-side session storage for API clients
- React SPA stores access token in memory (not localStorage) for XSS protection
- Refresh token stored in `httpOnly` cookie for persistent sessions
- Admin panel PHP session authentication preserved for server-side CSRF; API auth uses JWT

**Token Lifecycle:**
- Access token: 15-minute TTL, stored in React memory state
- Refresh token: 7-day TTL, stored in `httpOnly` `SameSite=Strict` cookie
- Refresh happens silently via `POST /api/v1/auth/refresh`

**Status:** Accepted

---

### ADR-004: React 18+ SPA with React Router

**Decision:** Build the frontend as a React 18 SPA using React Router v6 for client-side routing.

**Rationale:**
- Industry-standard toolchain with rich ecosystem
- Component-driven architecture maps cleanly to the page structure defined in the blueprint
- React Query (TanStack Query) handles server state, caching, and background refetching
- Tailwind CSS (configured from blueprint) carries over cleanly

**SEO Note:** Since the public site must be crawlable, implement React Helmet Async for per-route `<title>` and `<meta>` tags. If Core Web Vitals or crawl issues arise post-launch, consider migrating the public routes to Next.js (SSG/SSR) while keeping the admin SPA in React.

**Status:** Accepted

---

### ADR-005: Tailwind CSS Design System Preserved

**Decision:** The Tailwind configuration, color tokens, and typography system defined in `docs/blueprint.md` §21 carry over to the React frontend verbatim.

**Brand tokens (preserved):**
- Colors: charcoal, ivory, bronze, gold, taupe, sand, pine, espresso, ink, ember, clay
- Typography: Cormorant Garamond (display), Inter (body)
- Custom shadow, border-radius, and spacing scale

**Status:** Accepted

---

## 2. Technology Stack

### Backend

| Component | Choice | Version | Notes |
|---|---|---|---|
| Language | PHP | 8.2+ | `declare(strict_types=1)` everywhere |
| Architecture | Custom OOP MVC | — | No Laravel/Symfony |
| Database | MySQL / MariaDB | 8.0+ / 10.5+ | PDO + prepared statements |
| Environment | vlucas/phpdotenv | ^5.6 | `.env` file management |
| Email | PHPMailer | ^6.10 | SMTP transactional email |
| Image processing | PHP GD | bundled | Thumbnail generation (640px JPEG) |
| Web Server | Apache 2.4+ | — | `mod_rewrite` required |
| PHP Extensions | pdo_mysql, mbstring, fileinfo, gd, openssl, json, intl | — | All required |
| Authentication | Custom JWT (HS256) | — | Implemented in `App\Core\JWT` |
| Session | File-based | — | Admin panel PHP sessions |
| Caching | File-based PHP | — | Settings cache; OPcache in prod |
| Logging | JSON file logs | — | `storage/logs/app-YYYY-MM-DD.log` |
| Package manager | Composer | 2.x | PSR-4 autoloading |

### Frontend

| Component | Choice | Version | Notes |
|---|---|---|---|
| Framework | React | 18.x | Hooks, concurrent features |
| Language | TypeScript | 5.x | Strict mode |
| Routing | React Router | 6.x | Client-side routing |
| Server state | TanStack Query (React Query) | 5.x | API data fetching, caching, mutations |
| UI / Styling | Tailwind CSS | 3.4.x | Blueprint design system |
| Forms | React Hook Form | 7.x | Performant uncontrolled forms |
| Form validation | Zod | 3.x | Schema-based validation |
| HTTP client | Axios | 1.x | Interceptors for JWT refresh |
| SEO | React Helmet Async | 2.x | Per-route meta tags |
| Gallery lightbox | Yet Another React Lightbox | 3.x | Keyboard-accessible image viewer |
| Rich text editor | Tiptap | 2.x | Blog post HTML editor |
| Icons | Lucide React | latest | Consistent icon set |
| Notifications | React Hot Toast | 2.x | Flash message equivalents |
| Build tool | Vite | 5.x | Fast HMR, ESM output |
| Package manager | npm | 10.x | `package-lock.json` committed |
| Linting | ESLint + TypeScript ESLint | — | Strict ruleset |
| Formatting | Prettier | 3.x | Consistent code style |

### Infrastructure

| Component | Choice | Notes |
|---|---|---|
| Web Server | Apache 2.4+ | Serves both API and frontend build |
| PHP Session Storage | File-based (`storage/sessions/`) | Admin panel auth |
| File Storage | Local filesystem | `public/uploads/` with S3 adapter planned |
| Cache | File-based PHP | `storage/cache/settings.php` |
| Logs | File-based JSON | `storage/logs/` |
| Process Manager | systemd / supervisor (prod) | Long-running processes if needed |
| SSL | Let's Encrypt / Custom Cert | Enforced in production |

---

## 3. Repository Structure

```
mesh/
├── backend/                      # PHP API server (renamed from root in new arch)
│   ├── app/                      # Application code
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                   # Apache document root
│   │   ├── index.php
│   │   ├── .htaccess
│   │   └── uploads/
│   ├── resources/
│   │   └── views/emails/         # Email templates only
│   ├── routes/
│   │   ├── api.php               # Public API routes
│   │   ├── admin-api.php         # Protected admin API routes
│   │   └── auth.php              # JWT auth routes
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── .env.example
│   └── composer.json
│
├── frontend/                     # React SPA
│   ├── public/
│   │   └── index.html
│   ├── src/
│   │   ├── api/                  # API client modules
│   │   ├── assets/               # Static assets (fonts, images)
│   │   ├── components/           # Reusable UI components
│   │   ├── hooks/                # Custom React hooks
│   │   ├── layouts/              # Page layout wrappers
│   │   ├── pages/                # Route-level page components
│   │   ├── store/                # Global state (React Context)
│   │   ├── types/                # TypeScript type definitions
│   │   ├── utils/                # Utility functions
│   │   ├── App.tsx
│   │   └── main.tsx
│   ├── .eslintrc.json
│   ├── .prettierrc
│   ├── tailwind.config.ts
│   ├── tsconfig.json
│   ├── vite.config.ts
│   └── package.json
│
└── docs/                         # All project documentation
    ├── blueprint.md
    ├── 01-business_requirement_docs.md
    ├── 02-build_blueprint.md
    ├── 03-system-architecture.md
    ├── 04-backend-architecture.md
    ├── 05-frontend-architecture.md
    ├── 06-api-design.md
    ├── 07-implementation-plan.md
    ├── 08-delivery_roadmap.md
    ├── 09-implementation_prompts.md
    └── prompts/
        ├── frontend.md
        └── backend.md
```

> **Note on migration:** The existing project root (`mesh/`) currently contains the PHP MVC with PHP template views. The new architecture refactors this as:
> - `backend/`: PHP application serving only the REST API (removes `resources/views/web/`, `resources/views/admin/`)
> - `frontend/`: New React application
> - The `public/` Apache document root can serve both: the React build from `/` and the PHP API from `/api/`

---

## 4. Backend Directory Structure

```
backend/
├── app/
│   ├── Controllers/
│   │   ├── Api/                          # Public API controllers
│   │   │   ├── GalleryController.php
│   │   │   ├── BlogController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── TestimonialController.php
│   │   │   ├── PageController.php
│   │   │   └── SettingController.php
│   │   ├── Admin/                        # Protected admin API controllers
│   │   │   ├── GalleryController.php
│   │   │   ├── BlogPostController.php
│   │   │   ├── MediaController.php
│   │   │   ├── InquiryController.php
│   │   │   ├── BookingController.php
│   │   │   ├── PageController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── TestimonialController.php
│   │   │   ├── HeroSlideController.php
│   │   │   ├── SettingsController.php
│   │   │   └── UserController.php
│   │   └── Auth/
│   │       └── AuthController.php        # JWT login/logout/refresh
│   ├── Core/
│   │   ├── Application.php
│   │   ├── Auth.php                      # Session auth (admin panel fallback)
│   │   ├── Config.php
│   │   ├── Controller.php
│   │   ├── CSRF.php
│   │   ├── Database.php
│   │   ├── ErrorHandler.php
│   │   ├── JWT.php                       # NEW: JWT encode/decode/verify
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── RouteDefinition.php
│   │   ├── Session.php
│   │   ├── Validator.php
│   │   ├── Exceptions/
│   │   │   └── HttpException.php
│   │   └── Middleware/
│   │       ├── MiddlewareInterface.php
│   │       ├── AuthMiddleware.php
│   │       ├── JwtMiddleware.php         # NEW: JWT Bearer token verification
│   │       ├── CorsMiddleware.php        # NEW: CORS headers for API
│   │       ├── GuestMiddleware.php
│   │       └── PermissionMiddleware.php
│   ├── Models/                           # 31 data-access classes (unchanged)
│   ├── Repositories/                     # 3 complex-query classes (unchanged)
│   ├── Services/                         # 20 business-logic classes (unchanged)
│   │   └── JwtService.php               # NEW: Token issuance and refresh
│   ├── Policies/
│   │   └── AdminPolicy.php
│   ├── Helpers/
│   │   └── UploadPathHelper.php
│   └── Validators/
├── bootstrap/
│   ├── app.php
│   └── helpers.php
├── config/
│   ├── app.php
│   ├── cors.php                          # NEW: CORS configuration
│   ├── database.php
│   ├── jwt.php                           # NEW: JWT secret and TTL config
│   ├── mail.php
│   ├── permissions.php
│   ├── session.php
│   └── uploads.php
├── database/
│   ├── console.php
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── index.php                         # Single entry point
│   ├── .htaccess
│   └── uploads/                          # All uploaded media
├── resources/
│   └── views/
│       └── emails/                       # Email templates only
├── routes/
│   ├── api.php                           # Public REST API routes
│   ├── admin-api.php                     # JWT-protected admin API routes
│   └── auth.php                          # /api/v1/auth/* routes
├── storage/
│   ├── cache/
│   ├── logs/
│   └── sessions/
├── vendor/
├── .env
├── .env.example
└── composer.json
```

---

## 5. Frontend Directory Structure

```
frontend/
├── public/
│   ├── index.html
│   ├── robots.txt                        # Static fallback (API serves canonical)
│   └── favicon.ico
├── src/
│   ├── api/                              # All API communication
│   │   ├── client.ts                     # Axios instance with interceptors
│   │   ├── auth.ts                       # Login, logout, refresh
│   │   ├── galleries.ts
│   │   ├── blog.ts
│   │   ├── services.ts
│   │   ├── testimonials.ts
│   │   ├── pages.ts
│   │   ├── media.ts
│   │   ├── inquiries.ts
│   │   ├── bookings.ts
│   │   ├── settings.ts
│   │   └── users.ts
│   ├── assets/
│   │   ├── fonts/                        # Self-hosted font fallbacks
│   │   └── images/                       # UI images (logo, placeholders)
│   ├── components/
│   │   ├── ui/                           # Generic UI primitives
│   │   │   ├── Button.tsx
│   │   │   ├── Input.tsx
│   │   │   ├── Select.tsx
│   │   │   ├── Textarea.tsx
│   │   │   ├── Badge.tsx
│   │   │   ├── Card.tsx
│   │   │   ├── Modal.tsx
│   │   │   ├── Spinner.tsx
│   │   │   ├── Pagination.tsx
│   │   │   └── Toast.tsx
│   │   ├── layout/                       # Layout chrome components
│   │   │   ├── Navbar.tsx
│   │   │   ├── Footer.tsx
│   │   │   ├── AdminSidebar.tsx
│   │   │   ├── AdminTopbar.tsx
│   │   │   └── Breadcrumbs.tsx
│   │   ├── public/                       # Public site-specific components
│   │   │   ├── HeroCarousel.tsx
│   │   │   ├── GalleryGrid.tsx
│   │   │   ├── GalleryCard.tsx
│   │   │   ├── Lightbox.tsx
│   │   │   ├── BlogPostCard.tsx
│   │   │   ├── TestimonialCard.tsx
│   │   │   ├── ServiceCard.tsx
│   │   │   ├── ContactForm.tsx
│   │   │   ├── BookingForm.tsx
│   │   │   └── CategoryFilter.tsx
│   │   └── admin/                        # Admin-specific components
│   │       ├── MediaPicker.tsx
│   │       ├── MediaUploader.tsx
│   │       ├── MediaGrid.tsx
│   │       ├── RichTextEditor.tsx
│   │       ├── SlugInput.tsx
│   │       ├── SeoPanel.tsx
│   │       ├── StatusBadge.tsx
│   │       ├── DataTable.tsx
│   │       ├── ConfirmDialog.tsx
│   │       └── SortableList.tsx
│   ├── hooks/
│   │   ├── useAuth.ts                    # Auth context accessor
│   │   ├── useDebounce.ts
│   │   ├── useSlugGenerator.ts
│   │   ├── useMediaPicker.ts
│   │   └── usePermission.ts
│   ├── layouts/
│   │   ├── PublicLayout.tsx              # Header + Footer + <Outlet>
│   │   ├── AdminLayout.tsx               # Sidebar + Topbar + <Outlet>
│   │   └── AuthLayout.tsx               # Centered login layout
│   ├── pages/
│   │   ├── public/
│   │   │   ├── HomePage.tsx
│   │   │   ├── PortfolioPage.tsx
│   │   │   ├── GalleryDetailPage.tsx
│   │   │   ├── ServicesPage.tsx
│   │   │   ├── ServiceDetailPage.tsx
│   │   │   ├── BlogPage.tsx
│   │   │   ├── BlogPostPage.tsx
│   │   │   ├── BlogCategoryPage.tsx
│   │   │   ├── BlogTagPage.tsx
│   │   │   ├── BlogSearchPage.tsx
│   │   │   ├── TestimonialsPage.tsx
│   │   │   ├── ContactPage.tsx
│   │   │   ├── BookingPage.tsx
│   │   │   ├── CmsPage.tsx              # Dynamic CMS pages
│   │   │   └── NotFoundPage.tsx
│   │   ├── admin/
│   │   │   ├── DashboardPage.tsx
│   │   │   ├── galleries/
│   │   │   │   ├── GalleryListPage.tsx
│   │   │   │   └── GalleryFormPage.tsx
│   │   │   ├── blog/
│   │   │   │   ├── BlogPostListPage.tsx
│   │   │   │   ├── BlogPostFormPage.tsx
│   │   │   │   └── BlogCategoriesPage.tsx
│   │   │   ├── pages/
│   │   │   │   ├── PagesListPage.tsx
│   │   │   │   └── PageFormPage.tsx
│   │   │   ├── media/
│   │   │   │   └── MediaLibraryPage.tsx
│   │   │   ├── inquiries/
│   │   │   │   ├── InquiryListPage.tsx
│   │   │   │   └── InquiryDetailPage.tsx
│   │   │   ├── bookings/
│   │   │   │   ├── BookingListPage.tsx
│   │   │   │   └── BookingDetailPage.tsx
│   │   │   ├── services/
│   │   │   │   ├── ServiceListPage.tsx
│   │   │   │   └── ServiceFormPage.tsx
│   │   │   ├── testimonials/
│   │   │   │   ├── TestimonialListPage.tsx
│   │   │   │   └── TestimonialFormPage.tsx
│   │   │   ├── hero-slides/
│   │   │   │   ├── HeroSlideListPage.tsx
│   │   │   │   └── HeroSlideFormPage.tsx
│   │   │   ├── settings/
│   │   │   │   └── SettingsPage.tsx
│   │   │   └── users/
│   │   │       └── UsersPage.tsx
│   │   └── auth/
│   │       └── LoginPage.tsx
│   ├── store/
│   │   ├── AuthContext.tsx               # JWT auth state + actions
│   │   └── ToastContext.tsx              # Global toast notifications
│   ├── types/
│   │   ├── api.ts                        # API response types
│   │   ├── models.ts                     # Domain model types
│   │   └── auth.ts                       # Auth state types
│   ├── utils/
│   │   ├── format.ts                     # Date, number, text formatting
│   │   ├── slugify.ts
│   │   ├── classnames.ts
│   │   └── constants.ts
│   ├── App.tsx                           # Root router configuration
│   └── main.tsx                          # React DOM entry point
├── .eslintrc.json
├── .prettierrc
├── index.html
├── tailwind.config.ts
├── tsconfig.json
├── vite.config.ts
└── package.json
```

---

## 6. Environment Configuration

### Backend `.env.example`

```bash
# Application
APP_NAME="Mesh Photography"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mesh_photo
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# JWT
JWT_SECRET=your-256-bit-secret-key-here
JWT_ACCESS_TTL=900          # 15 minutes in seconds
JWT_REFRESH_TTL=604800      # 7 days in seconds
JWT_REFRESH_COOKIE=mesh_refresh_token

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:5173,https://meshphoto.com
CORS_ALLOW_CREDENTIALS=true

# Session (admin panel)
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=Lax
SESSION_IDLE_TIMEOUT=60

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=hello@meshphoto.com
MAIL_FROM_NAME="Mesh Photography"

# Admin
ADMIN_PATH=/cms
CSRF_TOKEN_NAME=_token
APP_SETTINGS_CACHE_TTL=300

# Uploads
UPLOAD_MAX_FILE_SIZE_MB=10
UPLOAD_MAX_IMAGE_WIDTH=6000
UPLOAD_MAX_IMAGE_HEIGHT=6000
UPLOAD_ALLOWED_IMAGE_MIMES=image/jpeg,image/png,image/webp,image/gif
UPLOAD_ALLOWED_DOCUMENT_MIMES=application/pdf
UPLOAD_ALLOWED_VIDEO_MIMES=video/mp4,video/quicktime
```

### Frontend `.env.example`

```bash
# API Base URL
VITE_API_BASE_URL=http://localhost:8000/api/v1

# Public uploads base URL (for media file references)
VITE_UPLOADS_BASE_URL=http://localhost:8000/uploads

# App metadata
VITE_APP_NAME="Mesh Photography"
VITE_APP_URL=http://localhost:5173

# Feature flags
VITE_ENABLE_BLOG=true
VITE_ENABLE_BOOKING=true
```

---

## 7. Build Toolchain

### Backend

```bash
# Install dependencies
composer install

# Run development server (PHP built-in, or Apache)
php -S localhost:8000 -t public/

# Database
composer migrate                    # Run pending migrations
composer migrate:rollback           # Roll back last batch
composer migrate:status             # Show migration status
composer seed:roles-permissions     # Seed roles and permissions
composer seed:core-cms              # Seed core pages and settings
composer seed:demo-content          # Seed demo content

# Code quality
composer test                       # PHPUnit test suite
composer lint                       # PHP_CodeSniffer
```

### Frontend

```bash
# Install dependencies
npm install

# Development server (Vite, port 5173)
npm run dev

# Production build
npm run build

# Preview production build
npm run preview

# Type checking
npm run typecheck

# Lint
npm run lint

# Lint + fix
npm run lint:fix

# Format
npm run format
```

### `package.json` scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "tsc && vite build",
    "preview": "vite preview",
    "typecheck": "tsc --noEmit",
    "lint": "eslint src --ext .ts,.tsx",
    "lint:fix": "eslint src --ext .ts,.tsx --fix",
    "format": "prettier --write src/**/*.{ts,tsx,css}"
  }
}
```

---

## 8. Development Workflow

### Local Development Setup

```bash
# 1. Backend
cd backend
cp .env.example .env
# Edit .env with local DB credentials
composer install
php database/console.php migrate
php database/console.php seed:roles-permissions
php database/console.php seed:core-cms
php database/console.php seed:demo-content
# Start server (or configure Apache/XAMPP)
php -S localhost:8000 -t public/

# 2. Frontend
cd ../frontend
cp .env.example .env
# Edit VITE_API_BASE_URL=http://localhost:8000/api/v1
npm install
npm run dev
# Visit http://localhost:5173
```

### Vite Proxy Configuration

```typescript
// vite.config.ts
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
      '/uploads': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
});
```

### Git Branching Strategy

```
main              → Production-ready code
develop           → Integration branch
feature/<name>    → Feature branches (from develop)
fix/<name>        → Bug fix branches (from develop)
hotfix/<name>     → Critical fixes (from main)
release/<version> → Release preparation (from develop)
```

---

## 9. Coding Standards

### PHP (Backend)

- `declare(strict_types=1)` in every file
- PSR-4 autoloading; namespace prefix: `App\`
- Visibility always explicit (`public`, `protected`, `private`)
- All method parameters and return types declared
- No raw `$_GET`/`$_POST`/`$_SERVER` outside `Request` class
- No SQL in controllers — delegate to models/repositories
- Constructor injection for all dependencies
- Readonly properties for immutable constructor arguments
- Comments only for non-obvious decisions (not what, but why)

### TypeScript (Frontend)

- `strict: true` in `tsconfig.json`
- No `any` type — use `unknown` and type guards
- All exported functions have explicit return types
- Prefer named exports over default exports (except page components)
- Props interfaces co-located with their component file
- `type` for unions/intersections, `interface` for object shapes
- No `@ts-ignore` without an explanation comment

### React Conventions

- Functional components only (no class components)
- Custom hooks prefixed with `use`
- One component per file
- Component file and directory names: PascalCase
- Utility file names: camelCase
- Event handlers: `handle<EventName>` (e.g., `handleSubmit`)
- Boolean props: `is`/`has`/`can` prefix (e.g., `isLoading`, `hasError`)
- Avoid prop drilling beyond 2 levels — use Context or pass callbacks

### CSS / Tailwind

- Mobile-first utilities
- No inline `style` prop unless truly dynamic (use Tailwind arbitrary values)
- No `!important`
- Class order: layout → sizing → spacing → color → typography → effects → state → responsive

---

## 10. Dependency Management

### Backend (PHP)

```json
{
  "require": {
    "php": "^8.2",
    "vlucas/phpdotenv": "^5.6",
    "phpmailer/phpmailer": "^6.10"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0"
  }
}
```

> JWT implementation is hand-coded in `App\Core\JWT` (no external library needed for HS256 with PHP's built-in `hash_hmac`).

### Frontend (npm)

Core runtime dependencies:
- `react`, `react-dom` — ^18.x
- `react-router-dom` — ^6.x
- `@tanstack/react-query` — ^5.x
- `axios` — ^1.x
- `react-hook-form` — ^7.x
- `zod` — ^3.x
- `@hookform/resolvers` — ^3.x (Zod adapter)
- `react-helmet-async` — ^2.x
- `yet-another-react-lightbox` — ^3.x
- `@tiptap/react`, `@tiptap/starter-kit` — ^2.x
- `lucide-react` — latest
- `react-hot-toast` — ^2.x
- `clsx`, `tailwind-merge` — utilities

Dev dependencies:
- `vite`, `@vitejs/plugin-react` — build
- `typescript`, `@types/react`, `@types/react-dom`
- `tailwindcss`, `autoprefixer`, `postcss`
- `eslint`, `@typescript-eslint/eslint-plugin`, `@typescript-eslint/parser`
- `prettier`, `eslint-config-prettier`

---

## 11. Definition of Done

A feature is complete when:

### Backend

- [ ] `declare(strict_types=1)` in all new files
- [ ] All methods typed (params + return)
- [ ] No raw SQL in controllers
- [ ] Input validated before use
- [ ] JWT verified on all protected endpoints
- [ ] CORS headers present on all API responses
- [ ] Consistent JSON response envelope (`ok`, `data`, `message`, `errors`, `meta`)
- [ ] HTTP status codes match semantics (200/201/204/400/401/403/404/422/500)
- [ ] Sensitive data excluded from logs
- [ ] PHPUnit test written for service logic (if testable in isolation)

### Frontend

- [ ] TypeScript compiles with no errors (`tsc --noEmit`)
- [ ] No ESLint violations
- [ ] All API calls go through `src/api/` modules (no direct `axios` in components)
- [ ] Loading, error, and empty states handled
- [ ] Forms validate via Zod schema before submission
- [ ] Error messages displayed to user (Toast + inline field errors)
- [ ] Page renders correctly on 320px, 768px, 1280px breakpoints
- [ ] No browser console errors
- [ ] React Helmet sets correct page title and meta description
- [ ] Images have `alt` attributes
- [ ] Keyboard navigation works for interactive elements
