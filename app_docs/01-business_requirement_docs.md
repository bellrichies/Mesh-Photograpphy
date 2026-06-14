# Mesh Photography — Business Requirements Document

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Status:** Approved for Implementation  
> **Source of Truth:** `docs/blueprint.md`

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Business Objectives](#2-business-objectives)
3. [User Personas](#3-user-personas)
4. [Functional Requirements](#4-functional-requirements)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [User Stories](#6-user-stories)
7. [Feature Priority Matrix](#7-feature-priority-matrix)
8. [Business Rules](#8-business-rules)
9. [Constraints and Assumptions](#9-constraints-and-assumptions)
10. [Success Metrics](#10-success-metrics)
11. [Out of Scope](#11-out-of-scope)
12. [Glossary](#12-glossary)

---

## 1. Executive Summary

**Mesh Photography** is a premium, full-stack photography portfolio and studio management platform serving two concurrent audiences: public visitors who discover and engage with the photographer's work, and studio administrators who manage all content and client interactions without requiring technical knowledge.

The platform is architected as a **decoupled system**: a PHP 8.2+ custom MVC backend exposing a secure REST API, consumed by a React 18+ single-page application for both the public-facing marketing website and the private admin dashboard.

The brand positions itself at the high-end of the market — weddings, portraits, and commercial photography — where trust, elegance, and visual presentation are the primary conversion factors.

---

## 2. Business Objectives

### Primary Objectives

| # | Objective | KPI |
|---|---|---|
| BO-1 | Showcase photography work to attract new clients | Portfolio page views, bounce rate < 40% |
| BO-2 | Convert visitors into inquiries and booking requests | Inquiry form conversion rate ≥ 3% |
| BO-3 | Provide a professional first impression that builds trust | Time on site > 3 minutes |
| BO-4 | Enable the studio owner to manage all content independently | Admin task completion without developer assistance |
| BO-5 | Establish search engine visibility for photography services | Organic search traffic growth month-over-month |
| BO-6 | Streamline the client inquiry-to-booking workflow | Inquiry response time < 24 hours |

### Secondary Objectives

- Build a blog presence to attract long-tail search traffic on photography-related topics
- Showcase client testimonials to reinforce social proof
- Provide a clear services and pricing overview to qualify leads before inquiry
- Enable future extensibility: client proofing galleries, payment integration, and multi-photographer support

---

## 3. User Personas

### Persona 1: The Public Visitor — Prospective Client

**Name:** Sarah (Wedding Client)  
**Age:** 28–40  
**Device:** Mobile-first (65% mobile, 30% desktop, 5% tablet)  
**Goals:**
- Quickly assess the photographer's style and quality
- Understand pricing and service offerings
- Make an inquiry or booking request
- Read testimonials from past clients

**Pain Points:**
- Slow image loading on mobile
- Hidden or unclear pricing
- Complex booking forms
- No clear sense of the photographer's style

**Success Criteria:** Visitor browses portfolio, reads at least one testimonial, and submits a contact or booking inquiry.

---

### Persona 2: The Public Visitor — Casual Browser

**Name:** Mark (Blog Reader)  
**Age:** 22–45  
**Device:** Mixed desktop/mobile  
**Goals:**
- Read photography tips and stories
- Discover the photographer's work through content marketing
- Share interesting posts on social media

**Success Criteria:** Reads at least two blog posts, follows a CTA to the portfolio or contact page.

---

### Persona 3: The Studio Administrator — Photographer / Owner

**Name:** Alex (Studio Owner)  
**Role:** Primary CMS user, no technical background required  
**Goals:**
- Upload and organize photos into galleries
- Write and publish blog posts and update portfolio
- View and respond to contact inquiries and booking requests
- Update pricing, services, and testimonials
- Control the public website without writing code

**Pain Points:**
- Complex UIs that require technical knowledge
- Having to contact a developer for content updates
- Losing track of client inquiries

**Success Criteria:** Can independently manage all website content, monitor inquiries, and update settings within minutes.

---

### Persona 4: The Editor / Content Manager

**Name:** Jordan (Part-time Assistant)  
**Role:** Secondary CMS user with limited permissions  
**Goals:**
- Write and publish blog posts
- Upload media and manage galleries
- Cannot access settings, users, or financial data

**Success Criteria:** Can create and publish blog content and gallery updates without needing super-admin access.

---

## 4. Functional Requirements

### 4.1 Public Website

#### FR-PUB-01: Homepage
- Display a full-screen hero carousel with overlay text and CTA buttons
- Show featured portfolio galleries in a responsive grid
- Display a services overview teaser section
- Show recent blog posts (limit 3)
- Display client testimonials carousel
- Include a call-to-action section linking to contact/booking
- All content managed from the admin CMS

#### FR-PUB-02: Portfolio
- Display all published galleries in a responsive masonry/grid layout
- Support filtering by gallery category
- Each gallery shows a cover image, title, and category badge
- Gallery detail page shows all images in sort order with lightbox viewer
- Previous/next gallery navigation

#### FR-PUB-03: Services
- List all published services with feature image, title, short description, and price display
- Individual service detail page with full description and CTA
- Link each service to the booking form

#### FR-PUB-04: Blog
- Paginated post listing with cover images and excerpts
- Full post detail with related posts, author attribution, and social sharing
- Category and tag filtering
- Search functionality (title, excerpt, body)
- Monthly archive navigation
- Previous/next post navigation

#### FR-PUB-05: Contact
- CSRF-protected contact form (name, email, phone, subject, message)
- Input validation with inline error display
- Success confirmation with flash message
- Email confirmation to visitor
- Admin notification on new inquiry (planned)

#### FR-PUB-06: Booking
- CSRF-protected booking request form (name, email, phone, event type, event date, event location, message)
- Input validation with inline error display
- Confirmation message and admin notification
- Status tracking in admin

#### FR-PUB-07: Testimonials
- Public testimonials page listing all published testimonials
- Display client name, role, rating, portrait, and quote

#### FR-PUB-08: CMS Pages
- Dynamic CMS-managed pages served by slug (About, Privacy, Terms, FAQ, etc.)
- Pages composed of typed sections (text, image, gallery embed, CTA, etc.)
- Supports custom page templates

#### FR-PUB-09: SEO
- `<title>`, `<meta description>`, Open Graph, and canonical URL on every page
- JSON-LD structured data support
- XML sitemap at `/sitemap.xml`
- `robots.txt` at `/robots.txt`

---

### 4.2 Admin Dashboard

#### FR-ADM-01: Authentication
- Secure login with CSRF protection
- Password: bcrypt hashing
- Login throttling (max attempts, lockout duration)
- Session-based auth with fingerprinting and idle timeout
- Logout with session destruction and regeneration
- Password reset via email token (Tier 1 roadmap item)

#### FR-ADM-02: Role-Based Access Control
- Roles: `super-admin`, `editor`, `content-manager`
- Permissions: `manage-users`, `manage-settings`, `manage-media`, `manage-pages`, `manage-galleries`, `manage-blog`, `manage-testimonials`, `manage-inquiries`, `manage-services`
- UI elements hidden based on user permissions

#### FR-ADM-03: Dashboard
- At-a-glance metrics: pages, galleries, blog posts, media files, services, testimonials, hero slides, inquiries (new/in-progress), bookings (new/quoted)
- Quick-action links to common tasks
- Recent activity feed

#### FR-ADM-04: Gallery Management
- Create, edit, delete (soft) galleries
- Slug auto-generation with AJAX availability check
- Assign cover image via media picker
- Attach, caption, reorder, and remove media from gallery
- Assign gallery to categories
- Set featured status and sort order

#### FR-ADM-05: Blog Management
- Create, edit, delete (soft) blog posts
- Rich text body editor with AJAX autosave (30-second intervals)
- Attach cover image and inline media
- Assign categories and tags
- Status management: draft → scheduled → published → archived
- Revision history with restore capability
- Category and tag CRUD with slug management

#### FR-ADM-06: Pages Management
- Create, edit, delete (soft) CMS pages
- Assign to parent for hierarchy
- Section manager: add, edit, reorder, toggle, delete sections
- Reusable content blocks by key
- SEO metadata panel per page

#### FR-ADM-07: Media Library
- Upload via drag-and-drop or file selector
- Auto-generates 640px JPEG thumbnail on upload
- Grid/list view with type filtering (images/videos/documents)
- Search by filename, alt text, title
- Edit alt text, title, and caption
- Archive (soft) and hard-delete with usage check
- Media picker modal used by all content editors

#### FR-ADM-08: Services Management
- Create, edit, delete (soft) services
- Slug management, sort order, status toggle
- Feature image via media picker

#### FR-ADM-09: Testimonials Management
- Create, edit, delete (soft) testimonials
- Portrait image via media picker
- Rating (1–5 stars), client name, role, quote body

#### FR-ADM-10: Hero Slides Management
- Create, edit, delete (soft) hero carousel slides
- Background image via media picker
- Title, subtitle, CTA label, and CTA URL
- Sort order and status toggle

#### FR-ADM-11: Inquiry Management
- List all contact inquiries with status tabs (new, in_progress, replied, closed)
- Filter by date range and keyword
- Detail view with full message and internal CRM notes
- AJAX status updates and note-adding

#### FR-ADM-12: Booking Management
- List all booking requests with status pipeline (new → contacted → quoted → booked → cancelled)
- Detail view with event info
- AJAX status updates

#### FR-ADM-13: Settings Management
- Grouped settings UI: site identity, contact info, SEO defaults, social links, booking policy
- Settings cached for performance (PHP file cache)
- Cache invalidated on save

#### FR-ADM-14: User Management
- List all admin users
- Create, edit, deactivate users
- Assign roles
- Soft delete

---

### 4.3 REST API

#### FR-API-01: Public API Endpoints
- Portfolio galleries and images
- Services list and detail
- Blog posts, categories, tags
- Testimonials
- CMS pages
- Site settings (public subset)

#### FR-API-02: Authentication API
- `POST /api/v1/auth/login` → returns JWT access token + refresh token
- `POST /api/v1/auth/refresh` → refreshes access token
- `POST /api/v1/auth/logout` → invalidates refresh token

#### FR-API-03: Admin API Endpoints
- Protected by JWT Bearer token
- Full CRUD for all content types
- Media upload and management
- Inquiry and booking management
- Settings management

#### FR-API-04: API Standards
- JSON responses with consistent envelope (`ok`, `data`, `message`, `errors`, `meta`)
- Pagination via `?page=N&per_page=N`
- HTTP status codes follow RFC 7231
- `X-Request-Id` header for log correlation

---

## 5. Non-Functional Requirements

### 5.1 Performance

| Requirement | Target |
|---|---|
| Page load time (LCP) | < 2.5 seconds (mobile 4G) |
| Time to First Byte (TTFB) | < 300ms |
| Core Web Vitals (LCP, FID, CLS) | All "Good" thresholds |
| Image optimization | WebP preferred, JPEG fallback; thumbnails for list views |
| API response time (p95) | < 200ms for read, < 500ms for write |
| Concurrent users | Support 500 concurrent visitors without degradation |

### 5.2 Security

| Requirement | Implementation |
|---|---|
| Authentication | JWT (RS256 or HS256) for API; bcrypt for password storage |
| CSRF protection | Token-based for all state-changing requests |
| SQL injection | Parameterized queries only (PDO prepared statements) |
| XSS | Output escaping; Content-Security-Policy header |
| File upload | MIME detection via `finfo`, extension whitelist, `.htaccess` execution block |
| Session security | Fingerprinting, idle timeout, secure cookies |
| Rate limiting | Login throttle; API rate limiting (planned) |
| Security headers | CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy |
| HTTPS | Enforced in production; HSTS header |
| Sensitive data | Never logged; passwords never stored in plaintext |

### 5.3 Scalability

| Requirement | Approach |
|---|---|
| Database | Indexed queries, connection pooling, query optimization |
| Caching | Settings PHP file cache; OPcache for PHP bytecode; CDN for static assets |
| Storage | Local filesystem (dev/staging); S3-compatible (production option) |
| Images | Responsive srcset, lazy loading, WebP conversion |
| Horizontal scaling | Stateless API (JWT); shared file storage or S3 for multi-server |

### 5.4 Accessibility

- WCAG 2.1 AA compliance target
- Semantic HTML5 elements throughout
- ARIA roles and labels on all interactive components
- Keyboard navigable (carousels, modals, menus)
- Minimum 4.5:1 color contrast for body text
- All images have meaningful alt text
- Form error messages linked via `aria-describedby`
- Skip-to-content link in main layout

### 5.5 SEO

- Server-rendered HTML or pre-rendered pages for all public routes (React SSR or static export considered)
- Unique `<title>` and `<meta description>` per page
- Open Graph tags for social sharing
- JSON-LD structured data (Organization, LocalBusiness, Article, BreadcrumbList)
- Clean URL slugs with no query parameters for canonical content
- XML sitemap auto-generated from published content
- `robots.txt` with admin path disallowed
- 301 redirects for changed URLs

### 5.6 Maintainability

- Backend: PSR-4 autoloading, strict types, clean architecture layers (Controller → Service → Repository → Model)
- Frontend: Component-driven architecture, TypeScript, consistent naming conventions
- Documentation: In-code comments for non-obvious decisions only; architecture documented in `/docs`
- Test coverage: Unit tests for services/validators; integration tests for API endpoints; E2E for critical flows

### 5.7 Usability (Admin)

- Non-technical users can manage all content independently
- Consistent UI patterns (flash messages, validation errors, loading states)
- Destructive actions require confirmation dialogs
- Autosave on blog posts prevents content loss
- Media picker reused consistently across all content forms

---

## 6. User Stories

### Public Visitor Stories

| ID | As a... | I want to... | So that... | Priority |
|---|---|---|---|---|
| US-001 | Visitor | View the homepage hero with strong imagery | I can immediately assess the photographer's style | Must Have |
| US-002 | Visitor | Browse portfolio galleries filtered by category | I can find work relevant to my event type | Must Have |
| US-003 | Visitor | View a gallery lightbox with full-screen images | I can appreciate image quality | Must Have |
| US-004 | Visitor | View services and pricing | I can qualify my budget before contacting | Must Have |
| US-005 | Visitor | Submit a contact inquiry | I can ask questions before committing | Must Have |
| US-006 | Visitor | Submit a booking request with event details | I can reserve the photographer for my date | Must Have |
| US-007 | Visitor | Read blog posts for photography inspiration | I can discover the photographer's work organically | Should Have |
| US-008 | Visitor | Search blog posts by keyword | I can find relevant articles quickly | Should Have |
| US-009 | Visitor | Read client testimonials | I can build trust in the service | Must Have |
| US-010 | Visitor | Share blog posts on social media | I can tell others about the content | Should Have |
| US-011 | Visitor | Find the site via Google search | I discover the photographer through organic search | Must Have |
| US-012 | Visitor | Have the site load quickly on mobile | I don't abandon the page due to slow load | Must Have |

### Admin Stories

| ID | As an... | I want to... | So that... | Priority |
|---|---|---|---|---|
| US-013 | Admin | Log in securely to the dashboard | I can manage content safely | Must Have |
| US-014 | Admin | Upload photos and organize into galleries | I can showcase new work quickly | Must Have |
| US-015 | Admin | Create and publish blog posts with images | I can maintain an active content presence | Must Have |
| US-016 | Admin | View and respond to inquiries with internal notes | I can manage client communications | Must Have |
| US-017 | Admin | Update booking status through the workflow | I can track where each booking stands | Must Have |
| US-018 | Admin | Create and manage CMS pages with sections | I can update site content without code | Must Have |
| US-019 | Admin | Update site settings (logo, contact info, social) | I can keep information current | Must Have |
| US-020 | Admin | Set SEO metadata for pages and posts | I can optimize search engine rankings | Must Have |
| US-021 | Admin | Control what content is published vs draft | I can prepare content before going live | Must Have |
| US-022 | Admin | See dashboard metrics at a glance | I can understand site activity quickly | Should Have |
| US-023 | Super-Admin | Create and manage user accounts with roles | I can delegate tasks with appropriate access | Should Have |
| US-024 | Editor | Write blog posts without seeing settings or users | I can focus on content tasks only | Should Have |
| US-025 | Admin | Autosave blog post drafts automatically | I don't lose work during long editing sessions | Should Have |
| US-026 | Admin | Restore a previous version of a blog post | I can recover from accidental edits | Should Have |

---

## 7. Feature Priority Matrix

### Must Have (MVP)

- Homepage hero carousel, featured galleries, testimonials, CTA
- Portfolio index, category filter, gallery detail with lightbox
- Services index and detail
- Contact inquiry form with CSRF and validation
- Booking request form
- Blog index, post detail, category/tag pages
- Admin authentication with RBAC
- Admin: gallery, blog, pages, media, services, testimonials, hero slides management
- Admin: inquiry and booking management
- Admin: settings management
- REST API for all public content
- JWT authentication API
- SEO metadata, sitemap, robots.txt
- Mobile-responsive design
- Image thumbnails and lazy loading

### Should Have (Post-MVP)

- Blog search functionality
- Password reset via email
- Admin notification emails on new inquiry/booking
- Admin user management UI (create, edit, assign roles)
- Revision history and restore for blog posts
- Blog post autosave
- Scheduled blog post publishing
- Blog RSS feed
- Activity log viewer in admin

### Could Have (Future)

- Client proofing portal (password-protected gallery delivery)
- Two-factor authentication
- Payment/deposit integration (Stripe)
- WebP image conversion
- Responsive image srcset (multiple sizes)
- S3/CDN media storage
- Full-page caching
- Redis session driver
- E2E test suite (Playwright)
- Multi-language support

---

## 8. Business Rules

| ID | Rule |
|---|---|
| BR-001 | A gallery must have at least one image before it can be published |
| BR-002 | A blog post must have a title and body before publishing |
| BR-003 | Soft-deleted content is never shown on the public site |
| BR-004 | Media in active use cannot be hard-deleted without archiving first |
| BR-005 | All image uploads are validated for MIME type, extension, size (≤10MB), and dimensions (≤6000×6000px) |
| BR-006 | PHP and script files are blocked in all upload directories |
| BR-007 | Admin sessions expire after 60 minutes of inactivity |
| BR-008 | Login is locked for a configurable duration after N failed attempts |
| BR-009 | Slug values are unique per content type and URL-safe |
| BR-010 | All state-changing requests include a valid CSRF token |
| BR-011 | Published settings changes are cached; cache is invalidated immediately on update |
| BR-012 | JWT access tokens expire after 15 minutes; refresh tokens after 7 days |
| BR-013 | Booking status can only advance forward in the defined pipeline |
| BR-014 | Inquiry notes are append-only and attributed to the creating admin user |
| BR-015 | Super-admin cannot be deleted or stripped of the super-admin role |

---

## 9. Constraints and Assumptions

### Technical Constraints

- Backend: PHP 8.2+, custom MVC (no Laravel/Symfony/CodeIgniter)
- Frontend: React 18+ SPA (no Next.js/Remix unless SEO requires SSR)
- Database: MySQL 8.0+ or MariaDB 10.5+
- Web server: Apache 2.4+ with `mod_rewrite`
- No external CMS dependencies (headless WordPress, Contentful, etc.)
- File storage: Local filesystem (dev); S3-compatible adapter planned for production scale

### Business Constraints

- All content managed through the admin panel — no direct database access for content operations
- No live chat or real-time features in MVP scope
- No e-commerce or payment gateway in MVP
- Single photographer studio (multi-user support is role-restricted, not multi-tenant)

### Assumptions

- The photographer uses SMTP for transactional email (Mailgun, Postmark, SendGrid, or similar)
- The production server provides PHP 8.2+, GD extension (for thumbnail generation), PDO MySQL
- SSL/TLS certificate is available in production
- Image uploads are managed by the studio owner; volume is expected in the hundreds to low thousands per year
- The admin dashboard is used by a small team (1–5 users)

---

## 10. Success Metrics

### Launch Metrics (First 30 Days)

| Metric | Target |
|---|---|
| Core Web Vitals | All green (LCP < 2.5s, FID < 100ms, CLS < 0.1) |
| Mobile PageSpeed score | ≥ 85 |
| Sitemap indexed by Google | Within 7 days of launch |
| Admin users trained | 100% of studio staff onboarded |
| Content migrated | All existing galleries, services, and blog posts published |

### Growth Metrics (90 Days Post-Launch)

| Metric | Target |
|---|---|
| Organic search impressions | +20% month-over-month |
| Inquiry conversion rate | ≥ 3% of portfolio visitors |
| Average session duration | > 3 minutes |
| Bounce rate | < 45% |
| Mobile traffic share | Tracked, optimized toward |

### Operational Metrics (Ongoing)

| Metric | Target |
|---|---|
| Inquiry response rate | 100% within 24 hours |
| Platform uptime | 99.9% |
| Admin content update time | < 5 minutes for standard updates |
| Error rate (5xx) | < 0.1% of requests |

---

## 11. Out of Scope

The following features are explicitly out of scope for the initial release:

- Online payment processing or deposit collection
- Client-facing portal for gallery proofing or image download
- Multi-tenant / multi-photographer support
- Live booking calendar with availability management
- CRM integration (HubSpot, Salesforce, etc.)
- Social media auto-publishing
- Video hosting beyond file upload (YouTube/Vimeo embedding via content blocks)
- Print or product ordering
- E-commerce / shop functionality
- Mobile native app (iOS/Android)
- Multi-language / i18n support

---

## 12. Glossary

| Term | Definition |
|---|---|
| Gallery | A named collection of photos representing a shoot or project |
| Gallery Category | A classification for galleries (Wedding, Portrait, Commercial, etc.) |
| Service | A photography service offering with description and price display |
| Inquiry | A general contact form submission from a public visitor |
| Booking Request | A structured form submission requesting to book a specific photography session |
| CMS Page | An admin-managed page composed of typed sections (not a gallery or blog post) |
| Page Section | A typed content block within a CMS page (text, image, CTA, embed, etc.) |
| Reusable Block | A named content block used by key in multiple places (e.g., studio bio, CTA text) |
| Hero Slide | A full-screen carousel item on the homepage with background image and overlay text |
| Media Library | The central repository of all uploaded files (images, documents, videos) |
| Media Variant | A server-generated alternate size of an uploaded image (e.g., 640px thumbnail) |
| JWT | JSON Web Token — stateless authentication credential issued by the API |
| CSRF | Cross-Site Request Forgery — attack protection using session-tied tokens |
| Soft Delete | Marking a record as deleted via `deleted_at` timestamp without removing it from the database |
| Slug | A URL-safe identifier derived from a content title (e.g., `my-wedding-gallery`) |
| RBAC | Role-Based Access Control — permission system based on assigned user roles |
