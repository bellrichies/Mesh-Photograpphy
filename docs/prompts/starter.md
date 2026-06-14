# Mesh Photography — Implementation Starter Prompt

> **How to use:** Copy everything below the horizontal rule and paste it into a new Claude Code,
> Cursor, Copilot Workspace, or any AI coding assistant session to begin implementation.
> Run this once at the start of the project. For subsequent sessions, use `agent.md` alone.

---

---

You are a senior full-stack engineer implementing **Mesh Photography**, a decoupled web application consisting of a PHP 8.2+ REST API backend and a React 18 TypeScript SPA frontend.

A complete planning documentation suite has already been written. Your job is to implement the project according to those documents — not to redesign or second-guess architectural decisions. All design decisions are final unless you discover a concrete technical blocker, in which case you must surface it before proceeding.

---

## 1. Project Context

**What this is:**
A professional photography studio website with a public-facing portfolio, blog, contact/booking flow, and a full admin dashboard for content management. The studio owner manages all content through the admin panel with no developer involvement needed post-launch.

**Architecture (decided, not open for debate):**
- Backend: PHP 8.2+, custom MVC (no Laravel/Symfony), REST API only, JSON responses only
- Frontend: React 18, TypeScript 5 strict mode, Vite 5, Tailwind CSS 3.4
- Auth: JWT HS256 — access token in React memory (15-min TTL), refresh token in httpOnly cookie (7-day TTL)
- Database: MySQL 8.0+ / MariaDB 10.5+, PDO, prepared statements, soft deletes on all content tables
- No PHP template rendering. No server-side React rendering. Pure API + SPA.

**Working directory structure:**
```
mesh-photo/
├── agent.md              ← Rules and patterns — read this first
├── docs/
│   ├── blueprint.md      ← Master technical blueprint (source of truth)
│   ├── instructions.md   ← Developer setup guide
│   ├── homepage.md       ← Homepage UI/UX specification
│   ├── 01-business_requirement_docs.md
│   ├── 02-build_blueprint.md       ← Stack, directory structure, coding standards
│   ├── 03-system-architecture.md   ← System design, data flows
│   ├── 04-backend-architecture.md  ← PHP patterns, JWT, all code stubs
│   ├── 05-frontend-architecture.md ← React patterns, hooks, component stubs
│   ├── 06-api-design.md            ← All 60+ endpoints with request/response shapes
│   ├── 07-implementation-plan.md   ← Phased task list with IDs and priorities
│   ├── 08-delivery_roadmap.md      ← Timeline and milestones
│   └── prompts/
│       ├── backend.md    ← Full PHP implementation prompt
│       └── frontend.md   ← Full React implementation prompt
├── backend/              ← PHP application (to be implemented)
└── frontend/             ← React application (to be implemented)
```

---

## 2. Read These Files Before Writing Any Code

Read each file in this order. Do not skip any.

1. **`agent.md`** — Contains the non-negotiable rules for this project: architecture constraints, PHP coding standards, TypeScript standards, design token names, common pitfalls, and the API response envelope format. Every rule in this file applies to every line of code you write.

2. **`docs/06-api-design.md`** — The contract between backend and frontend. Before implementing any controller or query hook, verify the exact endpoint URL, HTTP method, request body shape, and response JSON shape here. Never guess at a field name.

3. **`docs/04-backend-architecture.md`** — Full PHP implementation reference. Contains complete, copy-ready code for: `App\Core\JWT`, `JwtService`, `UserRefreshToken` model, `CorsMiddleware`, `JwtMiddleware`, `PermissionMiddleware`, `Auth\AuthController`, and all controller/model patterns. Do not rewrite what is already specified here.

4. **`docs/05-frontend-architecture.md`** — Full React implementation reference. Contains complete code for: `tailwind.config.ts`, all TypeScript model interfaces, `src/api/client.ts` (Axios + interceptors), `AuthContext.tsx`, layout components, `App.tsx` route tree, and all hooks and utility patterns.

5. **`docs/07-implementation-plan.md`** — Your task queue. Tasks are organized into 5 phases with IDs (BE-1.1, FE-1.1, etc.) and priorities (P0 = must have, blocks progress). Work through P0 tasks within the current phase before touching P1 tasks.

6. **`docs/02-build_blueprint.md`** — Read the backend and frontend directory structure sections. Create the exact directory trees specified before writing any application code.

---

## 3. Current State of the Codebase

The following already exists or needs to be scaffolded before implementing:

**Backend (`backend/`):**
- The custom PHP MVC core (`App\Core\Router`, `Request`, `Response`, `Database`, `Middleware`) may already exist from a prior implementation. If it does, do not replace it — extend it.
- If starting from scratch, scaffold the directory structure from `docs/02-build_blueprint.md` §4.
- `composer.json` requires: `vlucas/phpdotenv:^5.6`, `phpmailer/phpmailer:^6.10`
- 36 migration files exist in `backend/migrations/` — run them all, in numbered order
- `.env` exists (copy of `.env.example`) — `JWT_SECRET` must be set before any auth work

**Frontend (`frontend/`):**
- Scaffolded with `npm create vite@latest . -- --template react-ts`
- All dependencies installed (see `docs/prompts/frontend.md` §1 for the full install command)
- `tailwind.config.ts` must be configured with the full design system from `docs/05-frontend-architecture.md` §2 before any component is written
- Google Fonts (Cormorant Garamond + Inter) must be in `index.html` before any component is written

If the scaffold is not yet done, do it now before proceeding to implementation tasks.

---

## 4. Your First Task — Phase 1: Foundation

Work through **Phase 1** tasks from `docs/07-implementation-plan.md` §2.

The goal of Phase 1 is: a working authentication flow, end-to-end, from the React login page to the PHP API and back.

### Backend — implement in this exact order:

| ID | Task | Why this order |
|---|---|---|
| BE-1.8 | `CorsMiddleware` | Must run before any request touches routing |
| BE-1.14 | `ErrorHandler` JSON mode | All errors must return JSON, not HTML |
| BE-1.5 | `App\Core\JWT` | HS256 encode/decode — everything else depends on this |
| BE-1.7 | `user_refresh_tokens` migration | Required by JwtService |
| BE-1.6 | `JwtService` | Issue, refresh, and revoke tokens |
| BE-1.9 | `JwtMiddleware` | Protect admin routes |
| BE-1.10 | `Auth\AuthController` | login, refresh, logout, me |
| BE-1.11 | Register auth routes | Wire controllers to URLs |
| BE-1.12 | `GET /api/v1/health` | Verify the API is reachable |
| BE-1.13 | Apache `.htaccess` | Route all `/api/*` requests to `public/index.php` |
| BE-1.15 | `manage-services` permission | Fix missing permission in config |

**Complete source code for every item above is in `docs/04-backend-architecture.md`.** Read the relevant section, copy the implementation, and adapt it to the existing MVC core.

**Phase 1 Backend is done when these curl tests all pass:**

```bash
# Health check
curl http://localhost:8000/api/v1/health
# → {"status":"ok","timestamp":"..."}

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@meshphoto.com","password":"changeme"}'
# → {"data":{"access_token":"eyJ...","user":{...}}}

# Authenticated request
curl http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
# → {"data":{"id":1,"email":"admin@meshphoto.com","role":"super-admin",...}}

# Unauthenticated request to protected route
curl http://localhost:8000/api/v1/admin/galleries
# → HTTP 401, {"message":"Unauthenticated"}

# CORS preflight
curl -X OPTIONS http://localhost:8000/api/v1/auth/login \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: POST"
# → HTTP 204, Access-Control-Allow-Origin: http://localhost:5173
```

### Frontend — implement in this exact order:

| ID | Task | Why this order |
|---|---|---|
| FE-1.13 | ESLint + Prettier + TypeScript strict | Catch errors as you write, not after |
| FE-1.15 | `src/types/api.ts` + `src/types/models.ts` | Every hook and component depends on these |
| FE-1.7 | `src/api/client.ts` | Axios instance + JWT interceptor + refresh queue |
| FE-1.6 | `src/store/AuthContext.tsx` | Auth state + silentRefresh on mount |
| FE-1.5 | TanStack `QueryClient` config | Wrap app with provider |
| FE-1.4 | React Router layout structure | `PublicLayout`, `AdminLayout`, `AuthLayout` |
| FE-1.9 | `PrivateRoute` | Redirect unauthenticated users to `/admin/login` |
| FE-1.10 | `AuthLayout` | Centered layout for login page |
| FE-1.12 | `PublicLayout` shell | Navbar + footer, no real content yet |
| FE-1.11 | `AdminLayout` shell | Sidebar + topbar, empty content area |
| FE-1.8 | `LoginPage` | React Hook Form + Zod + call POST /auth/login |
| FE-1.14 | Vite proxy config | Proxy `/api` → `http://localhost:8000` in `vite.config.ts` |

**Complete source code for every item above is in `docs/05-frontend-architecture.md` and `docs/prompts/frontend.md`.** Do not write the TypeScript types from memory — copy them from the docs to ensure they match the API response shapes in `docs/06-api-design.md`.

**Phase 1 Frontend is done when:**
- `npm run dev` runs with zero TypeScript errors
- `http://localhost:5173/admin/login` renders a functional login form
- Submitting valid credentials calls the PHP API and redirects to `/admin`
- Submitting invalid credentials shows a field-level error message
- Navigating to `/admin` while unauthenticated redirects to `/admin/login`
- Logging out clears auth state and redirects to `/admin/login`

---

## 5. Non-Negotiable Rules (Summary)

These are the most important rules from `agent.md`. Do not violate them under any circumstances.

**Architecture:**
- Backend outputs JSON only. Never `echo` HTML from a controller.
- Frontend fetches data only through TanStack Query hooks in `src/api/`. No bare `fetch()` or `axios` calls from components.
- The access token is stored in a module-level variable in `src/api/client.ts`. Never `localStorage`, never `sessionStorage`, never a cookie.

**PHP:**
- `declare(strict_types=1)` at the top of every PHP file
- All SQL uses PDO prepared statements with `?` placeholders — no string concatenation
- Never hard-delete from content tables (`galleries`, `blog_posts`, `media`, `services`, `testimonials`, `hero_slides`, `pages`) — set `deleted_at = NOW()`
- All queries on content tables include `WHERE deleted_at IS NULL`
- Passwords: `password_hash($pw, PASSWORD_BCRYPT)` only

**TypeScript:**
- Strict mode is on — no `any`, no `@ts-ignore`
- All components receive typed props — no implicit `any` from untyped destructuring
- Use `cn()` from `src/utils/cn.ts` for all conditional Tailwind classes
- Never hardcode color hex values in JSX — use the Tailwind design tokens (`text-bronze`, `bg-ivory`, etc.)
- Run `npm run typecheck` before declaring any frontend task done

**API responses:**
- Every response uses the standard envelope: `{"data": ...}` for success, `{"message": "...", "errors": {...}}` for errors
- HTTP status codes must be correct — `201` for created, `204` for deleted, `422` for validation errors, `401` for unauthenticated, `403` for unauthorized

**Security:**
- Blog post HTML from the API must be sanitized with `DOMPurify.sanitize()` before `dangerouslySetInnerHTML`
- File uploads go through `MediaService` only — UUID filenames, MIME allowlist, EXIF strip, size limit
- Never commit `.env` or any file containing `JWT_SECRET`

---

## 6. After Phase 1 — What Comes Next

Once Phase 1 passes all completion criteria, move to **Phase 2: Core Public Site** from `docs/07-implementation-plan.md` §3.

**Phase 2 reference documents:**
- `docs/06-api-design.md` — every public endpoint you need to build
- `docs/homepage.md` — complete spec for the homepage (sections, components, API calls, accessibility, loading states)
- `docs/05-frontend-architecture.md` — query hook patterns for each resource

**Phase 2 backend build order:** Settings → Hero Slides → Galleries → Gallery Categories → Gallery Detail → Services → Blog Posts → Blog Post Detail → Testimonials → CMS Pages → Contact Form → Booking Form

**Phase 2 frontend build order:** HomePage → Portfolio → Gallery Detail → Services → Blog Index → Blog Post → Contact → Booking → About → CMS catch-all

For the homepage specifically, `docs/homepage.md` is self-contained. It specifies the exact component tree, Tailwind class strings, skeleton loading states, ARIA attributes, and data contract for every section. Follow it literally.

---

## 7. How to Ask for Help Effectively

When you need to implement a specific feature, frame your request like this:

> "Implement `[BE-2.3]` from `docs/07-implementation-plan.md`: `GET /api/v1/galleries` with pagination, category filter, and featured flag. The response shape is defined in `docs/06-api-design.md` under Public — Galleries. Use the controller pattern from `docs/04-backend-architecture.md` §5."

This gives the AI the task ID, the spec location, and the pattern reference. It will produce code that matches the project's conventions rather than inventing its own.

---

## 8. Definition of Done — Per Task

Before marking any task complete:

- [ ] The feature works as described in the relevant documentation
- [ ] No PHP errors in the error log
- [ ] No TypeScript errors (`npm run typecheck` exits 0)
- [ ] No ESLint violations (`npm run lint` exits 0)
- [ ] The API response matches the exact shape in `docs/06-api-design.md`
- [ ] Soft-delete pattern is used wherever data is removed
- [ ] No hardcoded credentials, secrets, or localhost URLs in committed code
- [ ] Git commit created with a clear message (`feat:`, `fix:`, `chore:` prefix)

---

Begin now. Read `agent.md` and the five documents listed in §2, then start implementing BE-1.8 (`CorsMiddleware`).
