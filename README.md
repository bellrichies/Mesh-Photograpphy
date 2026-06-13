# Mesh Photography

Premium photography portfolio and CMS platform built with a custom PHP OOP MVC architecture.

## Project Goal

Build an elegant public-facing photography website and a practical admin CMS that enables non-technical content management, secure operations, and long-term scalability.

## Stack

- Backend: Custom PHP OOP MVC (no full-stack framework)
- Frontend: HTML5, Tailwind CSS, vanilla JS, AJAX
- Database: MySQL
- Dependency Management: Composer with PSR-4
- Libraries: vlucas/phpdotenv, PHPMailer
- Optional (phase-specific): Flatpickr, Chart.js

## Architecture Summary

- Pattern: MVC + service/repository layering
- Controllers: thin orchestration only
- Services: business rules and workflows
- Repositories: reusable query and persistence logic
- Models: entity representation and simple persistence concerns
- Validators: request validation and normalized error outputs
- Middleware: auth, guest, role/permission, CSRF, rate limiting
- Public/admin separation: strict route, controller, and view boundaries

## Primary Documentation

- docs/project-rules.md
- docs/architecture-overview.md
- docs/content-model.md
- docs/database-plan.md
- docs/deployment.md
- docs/qa-checklist.md
- docs/uat-guide.md
- docs/upload-security-notes.md
- docs/workflow.md
- docs/phase-by-phase-workflow.md
- docs/prompt-pack.md
- docs/blueprint.md

## Phase and Module Execution

Use these guides in this order:

1. docs/blueprint.md as product and architecture source of truth
2. docs/phase-by-phase-workflow.md for phase-level execution
3. docs/prompt-pack.md for module-level implementation order

Execution rule:

- complete one module fully
- verify done criteria
- request permission before starting the next module

## Current Build Status

- Pre-module documentation guardrails: completed
- Module 1 (project skeleton and repository foundation): completed
- Module 2 (application kernel and core MVC classes): completed
- Module 3 (configuration system and environment loading): completed
- Module 4 (database connection layer and migration runner): completed
- Module 5 (base layouts, shared partials, Tailwind/jQuery setup): completed
- Module 6 (authentication system for admin users): completed
- Module 7 (roles, permissions, policies, and authorization checks): completed
- Module 8 (core CMS tables, seeders, and base models): completed
- Module 9 (media library schema and models): completed
- Module 10 (media upload service and secure file handling): completed
- Module 11 (media library admin UI and AJAX workflows): completed
- Module 12 (settings module and global site configuration UI): completed
- Module 13 (pages module and structured CMS editing): completed
- Module 14 (page sections module for homepage and flexible content blocks): completed
- Module 15 (reusable blocks module): completed
- Module 16 (portfolio categories, galleries, and gallery media data layer): completed
- Module 17 (portfolio admin CRUD and gallery management UI): completed
- Module 18 (public portfolio listing and gallery detail pages): completed
- Module 19 (services module): completed
- Module 20 (testimonials module): completed
- Module 21 (blog categories, tags, posts, and revisions data layer): completed
- Module 22 (blog admin CMS and editorial workflow): completed
- Module 23 (public blog listing, single post, archives, and search): completed
- Module 24 (inquiry and contact form system): completed
- Module 25 (PHPMailer integration and email notification workflow): completed
- Module 26 (optional booking / availability request module): completed
- Module 27 (public homepage composition from CMS data): completed
- Module 28 (public about, services, testimonials, contact, and static pages): completed
- Module 29 (navigation, footer, global components, and responsive UX polish): completed
- Module 30 (AJAX layer for admin productivity): completed
- Module 31 (SEO output layer and metadata rendering): completed
- Module 32 (security hardening pass): completed
- Module 33 (performance optimization pass): completed
- Module 34 (accessibility review and UX refinement): completed
- Module 35 (seeder expansion and demo content population): completed
- Module 36 (404, error pages, and operational reliability): completed
- Module 37 (deployment readiness and production configuration): completed
- Module 38 (QA checklist and manual test support): completed
- Project module sequence complete through Module 38

Recent QA and handoff highlights:

- added `docs/qa-checklist.md` with structured coverage across authentication, permissions, settings, content modules, SEO, responsiveness, failure states, and launch-day smoke checks
- added `docs/uat-guide.md` as a non-technical owner walkthrough for public review, admin review, content editing validation, and sign-off
- documented explicit failure-state checks so validation includes invalid submissions, missing routes, auth failures, and operational error experiences rather than only happy paths

Recent deployment readiness highlights:

- `.env.example` now defaults to production-safe values, including `APP_DEBUG=false`, secure session cookies, explicit SMTP placeholders, and configurable session/upload path settings
- file-backed sessions now support a configurable `SESSION_FILE_PATH`, with automatic directory creation when the configured path is relative to the project root
- upload storage paths are now environment-driven through `UPLOAD_*_PATH` settings instead of fixed config literals
- a dedicated deployment guide now documents required PHP extensions, writable directories, Apache and Nginx front-controller setup, SSL expectations, migration and seeding commands, and current cron expectations for scheduled publishing

Recent operational reliability highlights:

- centralized exception handling now chooses safe JSON or HTML responses based on request type and request context instead of always rendering a generic PHP error file
- fatal PHP shutdown errors are now captured and rendered through the same handler so production failures still receive a controlled 500 response
- application errors now log structured JSON lines with request ID, method, path, IP, exception class, and stack trace into dated files under `storage/logs`
- the public 404 page and separate public/admin 500 templates are now premium-styled and expose request IDs while keeping production error details private unless debug mode is enabled

Recent seeding highlights:

- added a rerunnable `DemoContentSeeder` and `DemoContentFactory` for realistic premium-brand demo data across users, settings, pages, media, services, galleries, blog content, inquiries, and booking requests
- demo media now includes a fuller thumbnail-aware image set so the public homepage, portfolio, blog, and settings surface can render believable sample content immediately
- seeded admin/editor/content-manager accounts are role-assigned automatically, and demo content relationships are resynced safely on repeat runs
- seeding now clears the settings cache so updated demo configuration is visible on the next request

Recent accessibility and UX highlights:

- the public mobile navigation now supports proper dialog-like keyboard behavior, overlay close, escape-to-close, and stateful `aria-*` attributes
- public, auth, and admin shells now expose stronger focus-visible treatment plus reduced-motion fallbacks
- flash messages now announce status and errors through assistive technology and can auto-dismiss success states accessibly
- login, contact, and booking forms now use explicit field labels, IDs, helper copy, improved autocomplete hints, and clearer input semantics

Recent hardening highlights:

- authenticated admin sessions now enforce idle timeout and session fingerprint validation
- login throttling now keys on account plus request context and admin auth events are logged
- important admin mutations are written to the activity log, including settings, publishing, uploads, and media deletes
- upload directories now receive stronger Apache hardening rules automatically

Recent performance highlights:

- public gallery and blog cards now prefer generated thumbnail variants over original assets
- gallery detail pages paginate attached media to avoid rendering very large sets in one response
- settings now use a simple file-backed cache in `storage/cache` for low-volatility lookups
- the public layout no longer downloads jQuery just to support nav toggles and flash dismissal

## Migration Commands

- `php database/console.php migrate`
- `php database/console.php migrate:rollback`
- `php database/console.php status`
- `php database/console.php seed:roles-permissions`
- `php database/console.php seed:core-cms`
- `php database/console.php seed:media-sample`
- `php database/console.php seed:demo-content`
- `php cli migrate`

Composer shortcuts:

- `composer migrate`
- `composer migrate:rollback`
- `composer migrate:status`
- `composer seed:roles-permissions`
- `composer seed:core-cms`
- `composer seed:media-sample`
- `composer seed:demo-content`

## Frontend Asset Commands

- `npm install`
- `npm run build:css`
- `npm run watch:css`

Tailwind utilities are compiled into `public/assets/css/tailwind.css`; the app does not load Tailwind from the CDN.

Suggested seeding order for a full local demo environment:

1. `php database/console.php migrate`
2. `php database/console.php seed:demo-content`

Seeded admin credentials for local review:

- `ava.stone@mesh.local` / `Password123!`
- `nolan.reyes@mesh.local` / `Password123!`
- `mia.carter@mesh.local` / `Password123!`

Note: migration commands require the configured MySQL database to exist first.

## Non-Negotiables

- Do not introduce Laravel, Symfony, or other full frameworks
- Use prepared statements for DB operations
- Escape output in views
- Use password_hash and password_verify for auth
- Enforce CSRF on state-changing requests
- Keep uploads security-hardened and validated server-side

## License

Internal project scaffold and implementation documentation for Mesh Photography.
