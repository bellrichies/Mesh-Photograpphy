# Mesh Photography — API Design

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Base URL:** `https://meshphoto.com/api/v1`  
> **Format:** JSON REST API  
> **Authentication:** JWT Bearer token (protected endpoints)

---

## Table of Contents

1. [API Conventions](#1-api-conventions)
2. [Authentication Endpoints](#2-authentication-endpoints)
3. [Public Endpoints](#3-public-endpoints)
4. [Admin — Gallery Management](#4-admin--gallery-management)
5. [Admin — Blog Management](#5-admin--blog-management)
6. [Admin — Media Management](#6-admin--media-management)
7. [Admin — Services Management](#7-admin--services-management)
8. [Admin — Testimonials Management](#8-admin--testimonials-management)
9. [Admin — Hero Slides Management](#9-admin--hero-slides-management)
10. [Admin — Pages Management](#10-admin--pages-management)
11. [Admin — Inquiry Management](#11-admin--inquiry-management)
12. [Admin — Booking Management](#12-admin--booking-management)
13. [Admin — Settings Management](#13-admin--settings-management)
14. [Admin — User Management](#14-admin--user-management)
15. [Admin — Dashboard](#15-admin--dashboard)
16. [Utility Endpoints](#16-utility-endpoints)
17. [Data Schemas](#17-data-schemas)
18. [Error Reference](#18-error-reference)

---

## 1. API Conventions

### Base URL

All endpoints are prefixed with `/api/v1/`.

### Response Envelope

Every response uses the same JSON envelope:

```json
{
  "ok": true,
  "message": "Success",
  "data": {},
  "errors": {},
  "meta": {}
}
```

| Field | Type | Description |
|---|---|---|
| `ok` | boolean | `true` on success, `false` on any error |
| `message` | string | Human-readable result summary |
| `data` | object\|array\|null | Response payload |
| `errors` | object | Field-level validation errors (key: field name, value: error message) |
| `meta` | object | Pagination, timestamps, or other metadata |

### HTTP Status Codes

| Code | Meaning | When Used |
|---|---|---|
| `200` | OK | Successful read, update |
| `201` | Created | Successful resource creation |
| `204` | No Content | Successful delete (no body) |
| `400` | Bad Request | Malformed request (not validation) |
| `401` | Unauthorized | Missing or invalid JWT token |
| `403` | Forbidden | Valid token but insufficient permission |
| `404` | Not Found | Resource does not exist |
| `422` | Unprocessable Entity | Validation failure |
| `429` | Too Many Requests | Rate limit exceeded |
| `500` | Server Error | Unhandled exception |

### Pagination

All list endpoints support pagination via query parameters:

```
GET /api/v1/galleries?page=2&per_page=12
```

Paginated responses include `meta`:

```json
{
  "meta": {
    "current_page": 2,
    "per_page": 12,
    "total": 47,
    "last_page": 4,
    "from": 13,
    "to": 24
  }
}
```

### Filtering and Sorting

Common query parameters across list endpoints:

| Parameter | Type | Description |
|---|---|---|
| `page` | int | Page number (default: 1) |
| `per_page` | int | Items per page (default: 20, max: 100) |
| `sort` | string | Sort field (default: varies by endpoint) |
| `order` | `asc`\|`desc` | Sort direction (default: `asc`) |
| `status` | string | Filter by status (endpoint-specific values) |
| `q` | string | Keyword search |

### Authentication

Protected endpoints require:
```
Authorization: Bearer <access_token>
```

The refresh token is sent automatically as an `httpOnly` cookie (`mesh_refresh_token`).

### Request Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer <token>   (protected endpoints)
X-Request-Id: <uuid>            (optional, for log correlation)
```

### CORS

All responses include:
```
Access-Control-Allow-Origin: https://meshphoto.com
Access-Control-Allow-Credentials: true
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-Id
```

---

## 2. Authentication Endpoints

### `POST /api/v1/auth/login`

Login with email and password. Returns JWT access token.

**Rate limit:** 10 requests per 15 minutes per IP.

**Request:**
```json
{
  "email": "admin@meshphoto.com",
  "password": "SecurePassword123"
}
```

**Response `200`:**
```json
{
  "ok": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiJ9...",
    "expires_in": 900,
    "user": {
      "id": 1,
      "email": "admin@meshphoto.com",
      "first_name": "Alex",
      "last_name": "Studio",
      "roles": ["super-admin"],
      "permissions": ["manage-users", "manage-settings", "manage-media", "manage-pages", "manage-galleries", "manage-blog", "manage-testimonials", "manage-inquiries", "manage-services"]
    }
  }
}
```
> Refresh token set as `httpOnly SameSite=Strict` cookie.

**Response `401` (invalid credentials):**
```json
{
  "ok": false,
  "message": "Invalid email or password",
  "data": null,
  "errors": {}
}
```

**Response `429` (rate limited):**
```json
{
  "ok": false,
  "message": "Too many login attempts. Please try again in 8 minutes.",
  "data": { "retry_after": 480 }
}
```

---

### `POST /api/v1/auth/refresh`

Exchange refresh token cookie for a new access token.

**Request:** No body — refresh token sent via cookie automatically.

**Response `200`:**
```json
{
  "ok": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiJ9...",
    "expires_in": 900,
    "user": { ... }
  }
}
```

---

### `POST /api/v1/auth/logout`

**Auth required.** Revokes the refresh token and clears the cookie.

**Response `200`:**
```json
{ "ok": true, "message": "Logged out successfully" }
```

---

### `GET /api/v1/auth/me`

**Auth required.** Returns the currently authenticated user.

**Response `200`:**
```json
{
  "ok": true,
  "data": {
    "id": 1,
    "email": "admin@meshphoto.com",
    "first_name": "Alex",
    "last_name": "Studio",
    "roles": ["super-admin"],
    "permissions": [...]
  }
}
```

---

## 3. Public Endpoints

### `GET /api/v1/galleries`

**Query params:** `?category=<slug>&featured=true&page=1&per_page=12`

**Response `200`:**
```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "title": "Summer Elopement",
      "slug": "summer-elopement",
      "description": "An intimate ceremony...",
      "cover": {
        "url": "https://meshphoto.com/uploads/images/2026/06/...",
        "thumb_url": "https://meshphoto.com/uploads/variants/2026/06/...",
        "alt_text": "Couple exchanging vows"
      },
      "is_featured": true,
      "media_count": 28,
      "categories": [
        { "id": 1, "name": "Wedding", "slug": "wedding" }
      ]
    }
  ],
  "meta": { "current_page": 1, "per_page": 12, "total": 24, "last_page": 2 }
}
```

---

### `GET /api/v1/galleries/categories`

Returns all gallery categories with gallery counts.

**Response `200`:**
```json
{
  "ok": true,
  "data": [
    { "id": 1, "name": "Wedding", "slug": "wedding", "gallery_count": 12 },
    { "id": 2, "name": "Portrait", "slug": "portrait", "gallery_count": 8 }
  ]
}
```

---

### `GET /api/v1/galleries/{slug}`

**Response `200`:**
```json
{
  "ok": true,
  "data": {
    "id": 1,
    "title": "Summer Elopement",
    "slug": "summer-elopement",
    "description": "...",
    "categories": [...],
    "media": [
      {
        "id": 42,
        "url": "...",
        "thumb_url": "...",
        "alt_text": "...",
        "caption": "...",
        "width": 4000,
        "height": 2667,
        "sort_order": 1
      }
    ],
    "seo": {
      "meta_title": "Summer Elopement | Mesh Photography",
      "meta_description": "...",
      "og_image_url": "...",
      "canonical_url": "..."
    },
    "prev_gallery": { "slug": "autumn-portraits", "title": "Autumn Portraits" },
    "next_gallery": { "slug": "winter-ceremony", "title": "Winter Ceremony" }
  }
}
```

---

### `GET /api/v1/services`

**Response `200`:**
```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "title": "Wedding Photography",
      "slug": "wedding-photography",
      "short_description": "Full-day coverage...",
      "price_display": "From $2,500",
      "cover": { "url": "...", "thumb_url": "...", "alt_text": "..." },
      "sort_order": 1
    }
  ]
}
```

---

### `GET /api/v1/services/{slug}`

Returns full service with `description` (HTML body).

---

### `GET /api/v1/testimonials`

**Query params:** `?limit=3`

```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "client_name": "Sarah & James",
      "client_role": "Wedding Clients",
      "body": "Alex captured every precious moment...",
      "rating": 5,
      "portrait": { "url": "...", "alt_text": "Sarah and James" }
    }
  ]
}
```

---

### `GET /api/v1/blog/posts`

**Query params:** `?page=1&per_page=10&category=<slug>&tag=<slug>`

```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "title": "10 Tips for Perfect Wedding Portraits",
      "slug": "10-tips-wedding-portraits",
      "excerpt": "...",
      "cover": { "url": "...", "thumb_url": "...", "alt_text": "..." },
      "published_at": "2026-06-01T10:00:00Z",
      "categories": [{ "id": 2, "name": "Wedding Tips", "slug": "wedding-tips" }],
      "tags": [{ "id": 3, "name": "portraits", "slug": "portraits" }]
    }
  ],
  "meta": { ... }
}
```

---

### `GET /api/v1/blog/posts/search`

**Query params:** `?q=wedding+tips&page=1`

```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "title": "10 Tips for Perfect Wedding Portraits",
      "slug": "...",
      "excerpt": "...",
      "cover": { ... }
    }
  ],
  "meta": { "total": 3, "query": "wedding tips" }
}
```

---

### `GET /api/v1/blog/posts/{slug}`

Returns full post with `body` (HTML), related posts, and previous/next navigation.

```json
{
  "ok": true,
  "data": {
    "id": 1,
    "title": "...",
    "slug": "...",
    "excerpt": "...",
    "body": "<p>Full HTML content...</p>",
    "cover": { ... },
    "published_at": "2026-06-01T10:00:00Z",
    "author": { "id": 1, "name": "Alex Studio" },
    "categories": [...],
    "tags": [...],
    "seo": { ... },
    "related_posts": [...],
    "prev_post": { "slug": "...", "title": "..." },
    "next_post": { "slug": "...", "title": "..." }
  }
}
```

---

### `GET /api/v1/blog/categories`

Returns categories with post counts.

---

### `GET /api/v1/blog/categories/{slug}`

Returns category info + paginated posts in that category.

---

### `GET /api/v1/blog/tags`

Returns all tags with post counts.

---

### `GET /api/v1/blog/tags/{slug}`

Returns tag info + paginated posts with that tag.

---

### `GET /api/v1/pages/{slug}`

Returns a published CMS page with its sections.

```json
{
  "ok": true,
  "data": {
    "id": 5,
    "title": "About Us",
    "slug": "about",
    "template": null,
    "sections": [
      {
        "id": 1,
        "section_type": "hero",
        "title": "About Mesh Photography",
        "content": "<p>...</p>",
        "settings": { "background": "ivory-warm" },
        "sort_order": 1
      }
    ],
    "seo": { ... }
  }
}
```

---

### `GET /api/v1/hero-slides`

Returns all published hero slides in sort order.

```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "title": "Timeless Moments",
      "subtitle": "Capturing love stories...",
      "background_image": { "url": "...", "alt_text": "..." },
      "cta_label": "View Portfolio",
      "cta_url": "/portfolio",
      "sort_order": 1
    }
  ]
}
```

---

### `GET /api/v1/settings/public`

Returns the public-safe subset of site settings (site name, logo, tagline, contact info, social links).

```json
{
  "ok": true,
  "data": {
    "site": {
      "name": "Mesh Photography",
      "tagline": "Capturing timeless moments",
      "logo_url": "...",
      "favicon_url": "..."
    },
    "contact": {
      "phone": "+1 555 000 1234",
      "email": "hello@meshphoto.com",
      "address": "San Francisco, CA"
    },
    "social": {
      "instagram": "https://instagram.com/meshphoto",
      "facebook": "..."
    },
    "seo": {
      "default_title": "Mesh Photography | Professional Photography Studio",
      "default_description": "..."
    }
  }
}
```

---

### `POST /api/v1/contact`

Submits a contact inquiry.

**Request:**
```json
{
  "name": "Sarah Johnson",
  "email": "sarah@example.com",
  "phone": "+1 555 123 4567",
  "subject": "Wedding Photography Inquiry",
  "message": "Hi, I'm interested in your wedding packages..."
}
```

**Response `201`:**
```json
{
  "ok": true,
  "message": "Thank you! Your message has been sent. We'll be in touch soon.",
  "data": { "inquiry_id": 47 }
}
```

**Response `422`:**
```json
{
  "ok": false,
  "message": "Validation failed.",
  "errors": {
    "email": "Please enter a valid email address",
    "message": "Message must be at least 10 characters"
  }
}
```

---

### `POST /api/v1/booking`

**Request:**
```json
{
  "name": "James & Emma",
  "email": "james@example.com",
  "phone": "+1 555 999 8888",
  "event_type": "Wedding",
  "event_date": "2026-09-15",
  "event_location": "Napa Valley, CA",
  "message": "We're looking for all-day wedding coverage..."
}
```

**Response `201`:**
```json
{
  "ok": true,
  "message": "Your booking request has been received! We'll contact you within 24 hours.",
  "data": { "booking_id": 12 }
}
```

---

### `GET /api/v1/health`

Health check for uptime monitoring.

**Response `200`:**
```json
{
  "ok": true,
  "data": {
    "status": "healthy",
    "db": "connected",
    "version": "1.0.0",
    "timestamp": "2026-06-14T12:00:00Z"
  }
}
```

---

## 4. Admin — Gallery Management

### `GET /api/v1/admin/galleries`

**Auth + Permission: `manage-galleries`**  
**Query params:** `?page=1&per_page=20&status=published&q=wedding&sort=sort_order&order=asc`

**Response `200`:** Paginated gallery list (includes media count, category names, cover image).

---

### `POST /api/v1/admin/galleries`

**Request:**
```json
{
  "title": "Autumn Portraits 2026",
  "slug": "autumn-portraits-2026",
  "description": "A golden-hour portrait session...",
  "cover_media_id": 15,
  "status": "draft",
  "is_featured": false,
  "sort_order": 10,
  "category_ids": [1, 3],
  "seo": {
    "meta_title": "Autumn Portraits 2026 | Mesh Photography",
    "meta_description": "...",
    "og_image_id": 15,
    "canonical_url": "",
    "robots": "index,follow"
  }
}
```

**Response `201`:**
```json
{
  "ok": true,
  "message": "Gallery created successfully.",
  "data": { "id": 8, "slug": "autumn-portraits-2026", ... }
}
```

---

### `GET /api/v1/admin/galleries/{id}`

Returns gallery with all media, categories, and SEO meta.

---

### `PUT /api/v1/admin/galleries/{id}`

Same request body as `POST`. Fully replaces editable fields.

---

### `DELETE /api/v1/admin/galleries/{id}`

Soft delete. **Response `200`.**

---

### `POST /api/v1/admin/galleries/{id}/media`

Attach a media item to a gallery.

**Request:** `{ "media_id": 42, "caption": "Sunrise over the vineyard" }`  
**Response `201`:** `{ "ok": true, "data": { "gallery_id": 8, "media_id": 42, "sort_order": 5 } }`

---

### `PUT /api/v1/admin/galleries/{id}/media/{mediaId}`

Update caption for a gallery-media attachment.

**Request:** `{ "caption": "Updated caption" }`  
**Response `200`.**

---

### `DELETE /api/v1/admin/galleries/{id}/media/{mediaId}`

Remove a media item from a gallery (does NOT delete the media record).

**Response `200`.**

---

### `POST /api/v1/admin/galleries/{id}/media/reorder`

**Request:** `{ "media_id": 42, "direction": "up" }`  
**Response `200`.**

---

## 5. Admin — Blog Management

### `GET /api/v1/admin/blog/posts`

**Query:** `?status=draft&category=wedding-tips&tag=portraits&q=sunrise&page=1`

---

### `POST /api/v1/admin/blog/posts`

**Request:**
```json
{
  "title": "Golden Hour Photography Tips",
  "slug": "golden-hour-photography-tips",
  "excerpt": "Learn how to use the last hour of light...",
  "body": "<h2>What is Golden Hour?</h2><p>...</p>",
  "cover_media_id": 28,
  "status": "draft",
  "published_at": null,
  "category_ids": [2, 4],
  "tag_ids": [5, 7],
  "seo": { ... }
}
```

**Response `201`:** `{ "ok": true, "data": { "id": 12, "slug": "...", ... } }`

---

### `GET /api/v1/admin/blog/posts/{id}`

Returns full post with media, categories, tags, revisions list, and SEO meta.

---

### `PUT /api/v1/admin/blog/posts/{id}`

Full update. Same body as `POST`.

---

### `DELETE /api/v1/admin/blog/posts/{id}`

Soft delete.

---

### `POST /api/v1/admin/blog/posts/{id}/autosave`

**Request:** `{ "body": "<p>Latest draft content...</p>" }`  
**Response `200`:** `{ "ok": true, "data": { "saved_at": "2026-06-14T14:32:00Z" } }`

---

### Blog Categories

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/admin/blog/categories` | List all categories |
| `POST` | `/api/v1/admin/blog/categories` | Create category `{ name, slug, description, parent_id }` |
| `PUT` | `/api/v1/admin/blog/categories/{id}` | Update |
| `DELETE` | `/api/v1/admin/blog/categories/{id}` | Delete (must have no posts) |

### Blog Tags

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/admin/blog/tags` | List all tags |
| `POST` | `/api/v1/admin/blog/tags` | Create tag `{ name, slug }` |
| `PUT` | `/api/v1/admin/blog/tags/{id}` | Update |
| `DELETE` | `/api/v1/admin/blog/tags/{id}` | Delete |

---

## 6. Admin — Media Management

### `GET /api/v1/admin/media`

**Query:** `?type=image&q=wedding&page=1&per_page=24`

**Response:**
```json
{
  "ok": true,
  "data": [
    {
      "id": 42,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "original_name": "wedding-ceremony.jpg",
      "url": "...",
      "thumb_url": "...",
      "mime_type": "image/jpeg",
      "file_type": "image",
      "size_bytes": 2457600,
      "width": 4000,
      "height": 2667,
      "alt_text": "Couple exchanging rings",
      "title": "Wedding Ceremony",
      "caption": "",
      "status": "active",
      "is_in_use": true,
      "uploaded_by": { "id": 1, "name": "Alex Studio" },
      "created_at": "2026-06-14T10:00:00Z"
    }
  ],
  "meta": { ... }
}
```

---

### `POST /api/v1/admin/media/upload`

**Content-Type:** `multipart/form-data`  
**Fields:** `file` (binary), `context` (optional string)

**Response `201`:**
```json
{
  "ok": true,
  "message": "File uploaded successfully.",
  "data": {
    "id": 43,
    "uuid": "...",
    "url": "...",
    "thumb_url": "...",
    "mime_type": "image/jpeg",
    "file_type": "image",
    "size_bytes": 1234567,
    "width": 3000,
    "height": 2000,
    "checksum": "abc123..."
  }
}
```

**Response `422`:**
```json
{
  "ok": false,
  "message": "File type not allowed. Accepted types: JPEG, PNG, WebP, GIF.",
  "errors": { "file": "Invalid file type: application/pdf" }
}
```

---

### `PUT /api/v1/admin/media/{id}`

Update media metadata.

**Request:** `{ "alt_text": "Updated alt text", "title": "New title", "caption": "A caption" }`

---

### `DELETE /api/v1/admin/media/{id}`

Hard delete. Fails if media is in use.

**Response `409` (in use):**
```json
{
  "ok": false,
  "message": "Cannot delete media that is in use. Archive it first."
}
```

---

### `POST /api/v1/admin/media/{id}/archive`

Soft-archive (marks as `status='archived'`). Only succeeds if not in use.

---

## 7. Admin — Services Management

| Method | Path | Permission | Description |
|---|---|---|---|
| `GET` | `/api/v1/admin/services` | `manage-services` | Paginated list |
| `POST` | `/api/v1/admin/services` | `manage-services` | Create |
| `GET` | `/api/v1/admin/services/{id}` | `manage-services` | Single |
| `PUT` | `/api/v1/admin/services/{id}` | `manage-services` | Update |
| `DELETE` | `/api/v1/admin/services/{id}` | `manage-services` | Soft delete |

**Request body (POST/PUT):**
```json
{
  "title": "Wedding Photography",
  "slug": "wedding-photography",
  "short_description": "Full-day wedding coverage",
  "description": "<p>Our wedding photography service includes...</p>",
  "media_id": 5,
  "price_display": "From $2,500",
  "status": "published",
  "sort_order": 1,
  "seo": { ... }
}
```

---

## 8. Admin — Testimonials Management

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/admin/testimonials` | Paginated list |
| `POST` | `/api/v1/admin/testimonials` | Create |
| `GET` | `/api/v1/admin/testimonials/{id}` | Single |
| `PUT` | `/api/v1/admin/testimonials/{id}` | Update |
| `DELETE` | `/api/v1/admin/testimonials/{id}` | Soft delete |

**Request body:**
```json
{
  "client_name": "Sarah & James",
  "client_role": "Wedding Clients, 2026",
  "body": "Alex captured every precious moment of our wedding day...",
  "rating": 5,
  "portrait_media_id": 7,
  "status": "published",
  "sort_order": 1
}
```

---

## 9. Admin — Hero Slides Management

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/admin/hero-slides` | All slides (sorted) |
| `POST` | `/api/v1/admin/hero-slides` | Create |
| `GET` | `/api/v1/admin/hero-slides/{id}` | Single |
| `PUT` | `/api/v1/admin/hero-slides/{id}` | Update |
| `DELETE` | `/api/v1/admin/hero-slides/{id}` | Soft delete |

**Request body:**
```json
{
  "title": "Timeless Moments",
  "subtitle": "Professional photography for life's most meaningful events",
  "media_id": 3,
  "cta_label": "View Portfolio",
  "cta_url": "/portfolio",
  "status": "published",
  "sort_order": 1
}
```

---

## 10. Admin — Pages Management

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/admin/pages` | Paginated list |
| `POST` | `/api/v1/admin/pages` | Create |
| `GET` | `/api/v1/admin/pages/{id}` | Single with sections |
| `PUT` | `/api/v1/admin/pages/{id}` | Update |
| `DELETE` | `/api/v1/admin/pages/{id}` | Soft delete |

**Request body:**
```json
{
  "title": "About the Studio",
  "slug": "about",
  "template": null,
  "status": "published",
  "parent_id": null,
  "sort_order": 2,
  "seo": { ... }
}
```

**Page sections** are managed as sub-resources of pages (inline in `GET /{id}` response); create/update/delete via dedicated section endpoints (or inline in page `PUT`).

---

## 11. Admin — Inquiry Management

### `GET /api/v1/admin/inquiries`

**Query:** `?status=new&q=james&page=1&from=2026-06-01&to=2026-06-30`

**Response:** Paginated inquiry list with status badge data.

---

### `GET /api/v1/admin/inquiries/{id}`

Returns full inquiry with notes.

```json
{
  "ok": true,
  "data": {
    "id": 47,
    "name": "Sarah Johnson",
    "email": "sarah@example.com",
    "phone": "+1 555 123 4567",
    "subject": "Wedding Photography",
    "message": "Hi, I'm interested...",
    "status": "new",
    "ip_address": "203.0.113.42",
    "created_at": "2026-06-14T09:00:00Z",
    "notes": [
      {
        "id": 1,
        "note": "Called Sarah, leaving voicemail",
        "created_by": { "id": 1, "name": "Alex Studio" },
        "created_at": "2026-06-14T10:00:00Z"
      }
    ]
  }
}
```

---

### `PATCH /api/v1/admin/inquiries/{id}/status`

**Request:** `{ "status": "in_progress" }`  
Valid status values: `new`, `in_progress`, `replied`, `closed`  
**Response `200`.**

---

### `POST /api/v1/admin/inquiries/{id}/notes`

**Request:** `{ "note": "Sent follow-up email with package details" }`  
**Response `201`:** `{ "ok": true, "data": { "id": 2, "note": "...", "created_at": "..." } }`

---

## 12. Admin — Booking Management

### `GET /api/v1/admin/bookings`

**Query:** `?status=new&event_type=Wedding&page=1`

---

### `GET /api/v1/admin/bookings/{id}`

```json
{
  "ok": true,
  "data": {
    "id": 12,
    "name": "James & Emma",
    "email": "james@example.com",
    "phone": "+1 555 999 8888",
    "event_type": "Wedding",
    "event_date": "2026-09-15",
    "event_location": "Napa Valley, CA",
    "message": "All-day coverage...",
    "status": "new",
    "ip_address": "...",
    "created_at": "2026-06-14T12:00:00Z"
  }
}
```

---

### `PATCH /api/v1/admin/bookings/{id}/status`

**Request:** `{ "status": "quoted" }`  
Valid values: `new`, `contacted`, `quoted`, `booked`, `cancelled`  
**Response `200`.**

---

## 13. Admin — Settings Management

### `GET /api/v1/admin/settings`

Returns all settings grouped by namespace.

```json
{
  "ok": true,
  "data": {
    "site": {
      "name": { "value": "Mesh Photography", "type": "text", "label": "Site Name" },
      "tagline": { "value": "Capturing timeless moments", "type": "text", "label": "Tagline" },
      "logo": { "value": "...", "type": "image", "label": "Logo" }
    },
    "contact": { ... },
    "seo": { ... },
    "social": { ... },
    "booking": { ... }
  }
}
```

---

### `PUT /api/v1/admin/settings`

Saves all settings. Cache is invalidated server-side.

**Request:**
```json
{
  "site": {
    "name": "Mesh Photography Studio",
    "tagline": "Moments that last forever",
    "logo_media_id": 2
  },
  "contact": {
    "phone": "+1 555 000 1234",
    "email": "hello@meshphoto.com"
  }
}
```

**Response `200`:** `{ "ok": true, "message": "Settings saved successfully." }`

---

## 14. Admin — User Management

### `GET /api/v1/admin/users`

**Permission: `manage-users`**  
Returns paginated user list.

---

### `POST /api/v1/admin/users`

**Request:**
```json
{
  "first_name": "Jordan",
  "last_name": "Smith",
  "email": "jordan@meshphoto.com",
  "password": "SecurePassword123",
  "role_ids": [2]
}
```

---

### `PUT /api/v1/admin/users/{id}`

Update user profile or reassign roles. Cannot change own super-admin role.

---

### `DELETE /api/v1/admin/users/{id}`

Soft delete. Cannot delete own account or last super-admin.

---

## 15. Admin — Dashboard

### `GET /api/v1/admin/dashboard`

**Auth required.**

```json
{
  "ok": true,
  "data": {
    "pages": { "total": 8, "published": 6 },
    "galleries": { "total": 24, "published": 20 },
    "blog_posts": { "total": 42, "published": 38 },
    "media": { "total": 312 },
    "services": { "total": 5, "published": 5 },
    "testimonials": { "total": 12, "published": 10 },
    "hero_slides": { "total": 3, "published": 3 },
    "inquiries": { "total": 89, "new": 4, "in_progress": 2 },
    "bookings": { "total": 31, "new": 2, "quoted": 3 }
  }
}
```

---

## 16. Utility Endpoints

### `GET /api/v1/admin/slug-check`

**Auth required.**  
**Query:** `?value=my-gallery-title&type=gallery&exclude_id=5`

**Response `200`:**
```json
{
  "ok": true,
  "data": {
    "slug": "my-gallery-title",
    "available": true
  }
}
```

---

### `GET /api/v1/sitemap`

Returns XML sitemap. Response `Content-Type: application/xml`.

Apache proxies `/sitemap.xml` to this endpoint.

---

## 17. Data Schemas

### `MediaRecord`

```typescript
interface MediaRecord {
  id: number;
  uuid: string;
  url: string;
  thumb_url: string | null;
  original_name: string;
  stored_name: string;
  directory: string;
  mime_type: string;
  file_type: 'image' | 'video' | 'document';
  size_bytes: number;
  width: number | null;
  height: number | null;
  alt_text: string | null;
  title: string | null;
  caption: string | null;
  status: 'active' | 'archived';
  is_in_use: boolean;
  created_at: string;
}
```

### `Gallery`

```typescript
interface Gallery {
  id: number;
  title: string;
  slug: string;
  description: string | null;
  cover: MediaRecord | null;
  is_featured: boolean;
  status: 'draft' | 'published';
  media_count: number;
  categories: GalleryCategory[];
  sort_order: number;
  created_at: string;
  updated_at: string;
}

interface GalleryDetail extends Gallery {
  media: (MediaRecord & { sort_order: number; caption: string | null })[];
  seo: SeoMeta;
  prev_gallery: { slug: string; title: string } | null;
  next_gallery: { slug: string; title: string } | null;
}
```

### `BlogPost`

```typescript
interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  cover: MediaRecord | null;
  published_at: string | null;
  status: 'draft' | 'published' | 'archived' | 'scheduled';
  categories: BlogCategory[];
  tags: BlogTag[];
}

interface BlogPostDetail extends BlogPost {
  body: string;
  author: { id: number; name: string } | null;
  seo: SeoMeta;
  related_posts: BlogPost[];
  prev_post: { slug: string; title: string } | null;
  next_post: { slug: string; title: string } | null;
}
```

### `SeoMeta`

```typescript
interface SeoMeta {
  meta_title: string | null;
  meta_description: string | null;
  og_title: string | null;
  og_description: string | null;
  og_image_url: string | null;
  canonical_url: string | null;
  robots: string | null;
  schema_markup: string | null;
}
```

### `Inquiry`

```typescript
interface Inquiry {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  subject: string | null;
  message: string;
  status: 'new' | 'in_progress' | 'replied' | 'closed';
  ip_address: string | null;
  created_at: string;
  notes: InquiryNote[];
}
```

### `AdminUser`

```typescript
interface AdminUser {
  id: number;
  email: string;
  first_name: string;
  last_name: string;
  roles: string[];
  permissions: string[];
  status: 'active' | 'inactive';
  last_login_at: string | null;
}
```

---

## 18. Error Reference

| Code | `message` Example | Cause |
|---|---|---|
| `401` | "Authentication required" | No `Authorization` header |
| `401` | "Token expired" | JWT `exp` in the past |
| `401` | "Invalid token signature" | Token tampered or wrong secret |
| `401` | "Invalid or expired refresh token" | Refresh token revoked or expired |
| `403` | "You do not have permission to manage galleries" | Missing required permission |
| `404` | "Gallery not found" | Slug/ID doesn't match a published/existing record |
| `409` | "Cannot delete media that is in use" | Media has `MediaUsageMap` records |
| `409` | "Slug already in use" | Non-unique slug for entity type |
| `422` | "Validation failed." + `errors` object | Input fails Validator rules |
| `429` | "Too many login attempts. Try again in N minutes." | Rate limit exceeded |
| `500` | "Something went wrong on our side." (production) | Unhandled server exception |
