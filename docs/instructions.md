# Mesh Photography — Developer Setup & Workflow Guide

> **For:** Backend and frontend engineers joining the project  
> **Read time:** ~15 minutes  
> **Prerequisite reading:** None — this is the starting point

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Prerequisites](#2-prerequisites)
3. [Repository Structure](#3-repository-structure)
4. [Backend Setup](#4-backend-setup)
5. [Frontend Setup](#5-frontend-setup)
6. [Running Locally](#6-running-locally)
7. [Environment Variables Reference](#7-environment-variables-reference)
8. [Database Setup & Migrations](#8-database-setup--migrations)
9. [Development Workflow](#9-development-workflow)
10. [Making API Requests](#10-making-api-requests)
11. [Admin Access](#11-admin-access)
12. [Code Standards at a Glance](#12-code-standards-at-a-glance)
13. [Key Documentation Map](#13-key-documentation-map)
14. [Deployment](#14-deployment)

---

## 1. Project Overview

Mesh Photography is a decoupled web application:

| Layer | Tech | Port | Responsibility |
|---|---|---|---|
| **Backend** | PHP 8.2+, custom MVC | `8000` | REST API, file uploads, email, JWT auth |
| **Frontend** | React 18, TypeScript 5, Vite 5 | `5173` | All UI, routing, admin dashboard |
| **Database** | MySQL 8.0+ / MariaDB 10.5+ | `3306` | All persistent data (36 tables) |

The backend **only serves JSON**. There are no PHP templates. The frontend **only calls the API**. There is no server-side rendering.

---

## 2. Prerequisites

### Required — Both

| Tool | Minimum Version | Check |
|---|---|---|
| Git | 2.x | `git --version` |
| MySQL or MariaDB | 8.0 / 10.5 | `mysql --version` |

### Required — Backend Only

| Tool | Minimum Version | Check |
|---|---|---|
| PHP | 8.2 | `php --version` |
| Composer | 2.x | `composer --version` |
| PHP extensions | `pdo_mysql`, `gd`, `mbstring`, `exif`, `fileinfo`, `openssl` | `php -m` |

To check all extensions at once:
```bash
php -r "foreach (['pdo_mysql','gd','mbstring','exif','fileinfo','openssl'] as \$e) echo \$e . ': ' . (extension_loaded(\$e) ? 'OK' : 'MISSING') . PHP_EOL;"
```

### Required — Frontend Only

| Tool | Minimum Version | Check |
|---|---|---|
| Node.js | 20.x LTS | `node --version` |
| npm | 10.x | `npm --version` |

---

## 3. Repository Structure

```
mesh-photo/
├── agent.md                    ← AI coding assistant instructions (read this)
├── docs/
│   ├── blueprint.md            ← Master technical blueprint (source of truth)
│   ├── instructions.md         ← This file
│   ├── homepage.md             ← Homepage UI/UX specification
│   ├── 01-business_requirement_docs.md
│   ├── 02-build_blueprint.md
│   ├── 03-system-architecture.md
│   ├── 04-backend-architecture.md  ← PHP patterns, JWT, all code stubs
│   ├── 05-frontend-architecture.md ← React patterns, hooks, component stubs
│   ├── 06-api-design.md            ← All 60+ endpoints with request/response shapes
│   ├── 07-implementation-plan.md
│   ├── 08-delivery_roadmap.md
│   ├── 09-implementation_prompts.md
│   └── prompts/
│       ├── backend.md          ← Full PHP implementation prompt (for AI or devs)
│       └── frontend.md         ← Full React implementation prompt (for AI or devs)
├── backend/                    ← PHP application
└── frontend/                   ← React application
```

---

## 4. Backend Setup

### Step 1 — Install dependencies

```bash
cd backend
composer install
```

### Step 2 — Configure environment

```bash
cp .env.example .env
```

Open `.env` and fill in the required values. See [Environment Variables Reference](#7-environment-variables-reference) for all required keys.

At minimum you must set:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `JWT_SECRET` — generate with: `php -r "echo bin2hex(random_bytes(32));"`
- `APP_URL` — your local server URL, e.g. `http://localhost:8000`
- `CORS_ALLOWED_ORIGINS` — your frontend URL, e.g. `http://localhost:5173`

### Step 3 — Run migrations

```bash
php artisan migrate
# or, if using the custom migration runner:
php bin/migrate.php
```

Migrations are numbered SQL files in `backend/migrations/`. They run in order. See [Database Setup & Migrations](#8-database-setup--migrations) for details.

### Step 4 — Seed initial admin user

```bash
php bin/seed.php
# Creates: admin@meshphoto.com / password: changeme
# CHANGE THIS PASSWORD immediately after first login
```

### Step 5 — Configure uploads directory

```bash
mkdir -p backend/public/uploads
chmod 755 backend/public/uploads
```

### Step 6 — Verify

```bash
curl http://localhost:8000/api/v1/health
# Expected: {"status":"ok","timestamp":"..."}
```

---

## 5. Frontend Setup

### Step 1 — Install dependencies

```bash
cd frontend
npm install
```

### Step 2 — Configure environment

```bash
cp .env.example .env.local
```

Edit `.env.local`:

```bash
VITE_API_BASE_URL=http://localhost:8000/api/v1
VITE_UPLOADS_BASE_URL=http://localhost:8000/uploads
```

These values must match your local backend server. Do not commit `.env.local`.

### Step 3 — Verify TypeScript

```bash
npm run typecheck
# Should exit 0 with no errors
```

---

## 6. Running Locally

### Backend

```bash
cd backend
php -S localhost:8000 -t public/
```

Or if Apache/Nginx is configured locally, point the document root to `backend/public/`.

### Frontend

```bash
cd frontend
npm run dev
# Opens at http://localhost:5173
```

### Both at once (from project root)

If you have `concurrently` or similar, or use two terminal tabs:

```bash
# Terminal 1
cd backend && php -S localhost:8000 -t public/

# Terminal 2
cd frontend && npm run dev
```

---

## 7. Environment Variables Reference

### Backend `.env`

```bash
# Application
APP_ENV=local                        # local | staging | production
APP_URL=http://localhost:8000
APP_DEBUG=true

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=mesh_photography
DB_USER=root
DB_PASS=

# JWT
JWT_SECRET=                          # 32+ random bytes, hex-encoded
JWT_ACCESS_TTL=900                   # Access token TTL in seconds (default: 15 min)
JWT_REFRESH_TTL=604800               # Refresh token TTL in seconds (default: 7 days)

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:5173

# Email (PHPMailer)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=hello@meshphoto.com
MAIL_FROM_NAME="Mesh Photography"

# File uploads
UPLOAD_MAX_SIZE_MB=20
UPLOAD_PATH=/absolute/path/to/backend/public/uploads
```

### Frontend `.env.local`

```bash
VITE_API_BASE_URL=http://localhost:8000/api/v1
VITE_UPLOADS_BASE_URL=http://localhost:8000/uploads
```

---

## 8. Database Setup & Migrations

### Create the database

```sql
CREATE DATABASE mesh_photography CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'meshapp'@'localhost' IDENTIFIED BY 'your-password';
GRANT ALL PRIVILEGES ON mesh_photography.* TO 'meshapp'@'localhost';
FLUSH PRIVILEGES;
```

### Run migrations

Migrations live in `backend/migrations/` and are numbered sequentially (`001_create_users.sql`, `002_create_galleries.sql`, etc.). Run them in order.

If a migration runner script is available:

```bash
php bin/migrate.php
```

To run manually:

```bash
mysql -u meshapp -p mesh_photography < backend/migrations/001_create_users.sql
mysql -u meshapp -p mesh_photography < backend/migrations/002_create_galleries.sql
# ... continue for all 36 migration files
```

### Schema notes

- **All content tables have `deleted_at TIMESTAMP NULL`** — rows are never hard-deleted
- **`user_refresh_tokens`** table is required for JWT refresh token rotation — do not skip migration `036_create_user_refresh_tokens.sql`
- The `media` table tracks all uploaded files; deleting files from disk without updating this table will cause 404s in the frontend

### Checking schema state

```sql
SHOW TABLES;
-- Should list 36+ tables
DESCRIBE galleries;
-- Check that deleted_at column exists
```

---

## 9. Development Workflow

### Git branch strategy

```
main        ← Production-only. Never commit directly.
staging     ← Pre-production integration branch
dev         ← Active development integration
feature/*   ← Feature branches, branched from dev
fix/*       ← Bug fix branches
```

### Starting a feature

```bash
git checkout dev
git pull origin dev
git checkout -b feature/gallery-lightbox
```

### Commit style

```
feat: add hero carousel auto-rotation with pause control
fix: correct missing deleted_at filter in gallery index query
refactor: extract media URL helpers to src/utils/media.ts
docs: add API response format to agent.md
```

Prefixes: `feat`, `fix`, `refactor`, `style`, `docs`, `test`, `chore`.

### Before pushing

**Backend:**
```bash
cd backend
composer run lint          # PHP CS Fixer or similar
composer run test          # PHPUnit
```

**Frontend:**
```bash
cd frontend
npm run typecheck          # tsc --noEmit
npm run lint               # ESLint
npm run test               # Vitest
npm run build              # Ensure production build succeeds
```

Fix all TypeScript errors and lint violations before opening a PR. The build must succeed.

---

## 10. Making API Requests

### Base URL

```
http://localhost:8000/api/v1/
```

### Authentication

Public endpoints require no token. Admin endpoints require a `Bearer` token in the `Authorization` header.

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@meshphoto.com","password":"changeme"}'
```

Response:
```json
{
  "data": {
    "access_token": "eyJ...",
    "user": { "id": 1, "name": "Admin", "email": "admin@meshphoto.com", "role": "super-admin" }
  }
}
```

The `access_token` expires in 15 minutes. A `refresh_token` is set as an `httpOnly` cookie automatically.

**Using the token:**
```bash
curl http://localhost:8000/api/v1/admin/galleries \
  -H "Authorization: Bearer eyJ..."
```

**Refresh the token:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  --cookie "refresh_token=..." \
  # Cookie is sent automatically by the browser; pass it manually in curl testing
```

### Response envelope

All responses use this shape:

```json
// Single resource
{ "data": { "id": 1, "title": "..." } }

// Collection
{ "data": [...], "meta": { "current_page": 1, "per_page": 20, "total": 84, "last_page": 5 } }

// Error
{ "message": "Validation failed", "errors": { "title": ["Title is required"] } }
```

### Full API reference

See [`docs/06-api-design.md`](06-api-design.md) for all 60+ endpoints with request and response shapes.

---

## 11. Admin Access

After seeding, the admin panel is available at:

```
http://localhost:5173/admin/login
Email:    admin@meshphoto.com
Password: changeme
```

**Change this password immediately after first login.**

Admin roles:

| Role | What they can do |
|---|---|
| `super-admin` | Full access to all features and settings |
| `editor` | Manage galleries, blog posts, media |
| `content-manager` | Manage blog posts and testimonials only |

Permissions are defined in `backend/config/permissions.php`. New permissions must be added there before they can be assigned to roles.

---

## 12. Code Standards at a Glance

### PHP

- `declare(strict_types=1)` at the top of every file
- PSR-4 autoloading; namespace mirrors directory path under `app/`
- Type declarations on all method parameters and return types
- PDO prepared statements only — no string concatenation in SQL
- Password hashing: `password_hash($password, PASSWORD_BCRYPT)` only
- No `die()`, no `exit()`, no `var_dump()` in committed code

### TypeScript / React

- Strict mode on — no `any`, no `@ts-ignore`
- All API calls through TanStack Query hooks in `src/api/`
- No `useEffect` for data fetching — use query hooks
- No `localStorage` for auth tokens
- `DOMPurify.sanitize()` required before any `dangerouslySetInnerHTML`
- `cn()` from `src/utils/cn.ts` for all conditional class names
- Custom design token names only — no raw hex values in JSX

### Shared

- No commented-out code committed
- No `console.log` committed
- No hardcoded secrets, URLs, or credentials
- English only in code, comments, and commit messages

---

## 13. Key Documentation Map

| I need to know... | Read this |
|---|---|
| Business requirements and user stories | `docs/01-business_requirement_docs.md` |
| Technology choices and why | `docs/02-build_blueprint.md` |
| System architecture and data flows | `docs/03-system-architecture.md` |
| PHP backend patterns, JWT, middleware, services | `docs/04-backend-architecture.md` |
| React architecture, auth context, component patterns | `docs/05-frontend-architecture.md` |
| All API endpoints (request/response shapes) | `docs/06-api-design.md` |
| What to build, in what order | `docs/07-implementation-plan.md` |
| Timeline and milestones | `docs/08-delivery_roadmap.md` |
| Homepage layout, sections, and component specs | `docs/homepage.md` |
| Everything about the DB schema and existing models | `docs/blueprint.md` §3–§14 |
| Design system (colors, fonts, spacing tokens) | `docs/blueprint.md` §21 |
| Full PHP implementation prompt (backend engineers) | `docs/prompts/backend.md` |
| Full React implementation prompt (frontend engineers) | `docs/prompts/frontend.md` |
| AI coding assistant rules for this project | `agent.md` (root) |

---

## 14. Deployment

### Target environments

| Environment | Branch | URL |
|---|---|---|
| Development | `dev` | `http://localhost` |
| Staging | `staging` | `https://staging.meshphoto.com` |
| Production | `main` | `https://meshphoto.com` |

### Backend deployment steps

```bash
# 1. Pull the latest code
git pull origin main

# 2. Install/update dependencies (no dev dependencies in production)
composer install --no-dev --optimize-autoloader

# 3. Run new migrations
php bin/migrate.php

# 4. Reset OPcache to pick up new PHP files
# Via web: curl https://meshphoto.com/opcache-reset.php (protected endpoint)
# Via CLI: php -r "opcache_reset();"

# 5. Verify health check
curl https://meshphoto.com/api/v1/health
```

### Frontend deployment steps

```bash
# 1. Set production environment variables
cp .env.production .env.local
# Edit VITE_API_BASE_URL=https://meshphoto.com/api/v1

# 2. Build
npm run build
# Output: frontend/dist/

# 3. Deploy dist/ to the web server
rsync -avz --delete dist/ user@server:/var/www/meshphoto/frontend/

# 4. Verify — visit https://meshphoto.com and confirm the SPA loads
```

### Apache/Nginx — SPA fallback

The frontend is a single-page app. All URLs must fall back to `index.html`:

**Apache** (`backend/public/.htaccess` or a separate frontend vhost):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### Rollback

If a deployment breaks production:

```bash
# Backend — revert to previous release using symlink strategy
ln -sfn /var/www/releases/previous /var/www/meshphoto/backend/current

# Frontend — redeploy previous dist build
rsync -avz --delete /var/www/releases/frontend-previous/ user@server:/var/www/meshphoto/frontend/

# Verify
curl https://meshphoto.com/api/v1/health
```

Keep the two most recent releases available on the server at all times.

### Security checklist before each production deploy

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_ENV=production` in production `.env`
- [ ] `JWT_SECRET` is 32+ bytes, randomly generated, not the default
- [ ] Database user has minimal required permissions (no `DROP`, no `GRANT`)
- [ ] `public/uploads/` is writable but not executable (`chmod 755`)
- [ ] CORS `ALLOWED_ORIGINS` lists only the production frontend domain
- [ ] HTTPS enforced on both API and frontend domains
- [ ] Security headers present: `X-Content-Type-Options`, `X-Frame-Options`, `Content-Security-Policy`
