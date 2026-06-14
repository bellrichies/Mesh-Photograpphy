# Mesh Photography — Frontend Implementation Prompt

> **For:** React Developer / AI Coding Agent  
> **Project:** Mesh Photography SPA  
> **Stack:** React 18 · TypeScript 5 · Tailwind CSS 3.4 · TanStack Query 5 · Vite 5  
> **Architecture Reference:** `docs/05-frontend-architecture.md`  
> **API Reference:** `docs/06-api-design.md`  
> **Design System Reference:** `docs/blueprint.md` §21

---

## Your Mission

You are implementing the **React SPA frontend** for Mesh Photography, a premium photography portfolio and studio management platform. Your output is a production-ready React application that:

1. Renders all public-facing pages (homepage, portfolio, blog, services, contact, booking)
2. Renders a full admin dashboard for content management
3. Consumes all data from the PHP REST API described in `docs/06-api-design.md`
4. Implements JWT authentication with automatic token refresh
5. Follows the precise design system defined in `docs/blueprint.md` §21

Read every section of this prompt before writing a single component. The architecture is intentional — deviating from it will cause integration failures.

---

## Source of Truth Documents

You must study these documents before implementing:

1. `docs/blueprint.md` §21 — Design system: colors, typography, spacing. Tailwind tokens are defined there and must be reproduced exactly.
2. `docs/05-frontend-architecture.md` — Application structure, routing, AuthContext, data fetching strategy, component categories, all page specifications.
3. `docs/06-api-design.md` — Every API endpoint, request body, response shape, error format.
4. `docs/02-build_blueprint.md` — Frontend directory structure, npm dependencies, coding standards.

---

## Non-Negotiable Requirements

Before building any page or feature, these architectural foundations must be in place:

### 1. TypeScript strict mode

```json
// tsconfig.json
{
  "compilerOptions": {
    "strict": true,
    "noImplicitAny": true,
    "strictNullChecks": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true
  }
}
```

No `any` type. No `@ts-ignore` without a comment explaining why.

### 2. Access token stored in memory only

The JWT access token is NEVER stored in `localStorage` or `sessionStorage`. It lives in React state inside `AuthContext` only. This is the primary XSS protection.

The refresh token is sent automatically via `httpOnly` cookie managed by the browser — React code never reads or writes it directly.

### 3. All API calls go through `src/api/` modules

Components never import Axios directly. They use query hooks from `src/api/` modules. All server communication is centralized.

### 4. All loading, error, and empty states handled

Every data-dependent component renders a distinct state for:
- **Loading** — skeleton placeholder (not a spinner that shifts layout)
- **Error** — user-friendly error message with retry option
- **Empty** — helpful empty state with CTA if appropriate

### 5. Design system tokens used exclusively

No hardcoded colors, font sizes, or spacing values. Every style uses Tailwind classes from the configured token set (charcoal, ivory, bronze, gold, etc.). No inline `style` prop for colors or typography.

---

## Implementation Order

Follow this exact order. Each step depends on the previous.

---

## Step 1: Project Scaffold

```bash
npm create vite@latest frontend -- --template react-ts
cd frontend
npm install
```

### Install all dependencies

```bash
# Runtime
npm install \
  react-router-dom@6 \
  @tanstack/react-query@5 \
  axios \
  react-hook-form \
  zod \
  @hookform/resolvers \
  react-helmet-async \
  yet-another-react-lightbox \
  @tiptap/react \
  @tiptap/starter-kit \
  @tiptap/extension-image \
  @tiptap/extension-link \
  lucide-react \
  react-hot-toast \
  clsx \
  tailwind-merge \
  dompurify \
  @dnd-kit/core \
  @dnd-kit/sortable \
  @dnd-kit/utilities

# Dev
npm install -D \
  typescript \
  @types/react \
  @types/react-dom \
  @types/dompurify \
  tailwindcss \
  autoprefixer \
  postcss \
  @vitejs/plugin-react \
  eslint \
  @typescript-eslint/eslint-plugin \
  @typescript-eslint/parser \
  eslint-config-prettier \
  prettier
```

---

## Step 2: Tailwind Configuration

```typescript
// tailwind.config.ts
import type { Config } from 'tailwindcss';

const config: Config = {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        charcoal:        '#1a1a1a',
        'charcoal-light':'#2d2d2d',
        ivory:           '#faf9f7',
        'ivory-warm':    '#f5f1eb',
        cream:           '#ebe5dc',
        bronze:          '#9a7b5c',
        'bronze-light':  '#b8956f',
        'bronze-dark':   '#7a5f42',
        gold:            '#c4a77d',
        taupe:           '#a8998a',
        sand:            '#f6f1ea',
        parchment:       '#f4efe8',
        pine:            '#2f4c45',
        moss:            '#3f5a4f',
        espresso:        '#1b1714',
        ink:             '#171411',
        ember:           '#b08968',
        clay:            '#a67f63',
      },
      fontFamily: {
        display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        body:    ['Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft: '0 10px 30px -16px rgba(15, 23, 42, 0.28)',
      },
      borderRadius: {
        '2xl': '1rem',
        '3xl': '1.5rem',
        '4xl': '2rem',
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '26': '6.5rem',
        '30': '7.5rem',
      },
    },
  },
  plugins: [],
};

export default config;
```

Add to `index.html`:
```html
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
```

---

## Step 3: TypeScript Types

Create `src/types/api.ts`:

```typescript
export interface ApiResponse<T> {
  ok: boolean;
  message: string;
  data: T;
  errors: Record<string, string>;
  meta: PaginationMeta & Record<string, unknown>;
}

export interface PaginationMeta {
  current_page?: number;
  per_page?: number;
  total?: number;
  last_page?: number;
  from?: number;
  to?: number;
}

export interface ApiErrorResponse extends ApiResponse<null> {
  ok: false;
}
```

Create `src/types/models.ts` with all domain model types matching the schemas in `docs/06-api-design.md` Section 17:

```typescript
export interface MediaRecord {
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

export interface SeoMeta {
  meta_title: string | null;
  meta_description: string | null;
  og_title: string | null;
  og_description: string | null;
  og_image_url: string | null;
  canonical_url: string | null;
  robots: string | null;
  schema_markup: string | null;
}

export interface GalleryCategory {
  id: number;
  name: string;
  slug: string;
  gallery_count?: number;
}

export interface Gallery {
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

export interface GalleryMedia extends MediaRecord {
  sort_order: number;
  caption: string | null;
}

export interface GalleryDetail extends Gallery {
  media: GalleryMedia[];
  seo: SeoMeta;
  prev_gallery: { slug: string; title: string } | null;
  next_gallery: { slug: string; title: string } | null;
}

export interface BlogCategory {
  id: number;
  name: string;
  slug: string;
  post_count?: number;
}

export interface BlogTag {
  id: number;
  name: string;
  slug: string;
  post_count?: number;
}

export interface BlogPost {
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

export interface BlogPostDetail extends BlogPost {
  body: string;
  author: { id: number; name: string } | null;
  seo: SeoMeta;
  related_posts: BlogPost[];
  prev_post: { slug: string; title: string } | null;
  next_post: { slug: string; title: string } | null;
}

export interface Service {
  id: number;
  title: string;
  slug: string;
  short_description: string | null;
  description?: string | null;
  cover: MediaRecord | null;
  price_display: string | null;
  status: 'draft' | 'published';
  sort_order: number;
  seo?: SeoMeta;
}

export interface Testimonial {
  id: number;
  client_name: string;
  client_role: string | null;
  body: string;
  rating: number | null;
  portrait: MediaRecord | null;
  status: 'draft' | 'published';
  sort_order: number;
}

export interface HeroSlide {
  id: number;
  title: string;
  subtitle: string | null;
  background_image: MediaRecord | null;
  cta_label: string | null;
  cta_url: string | null;
  sort_order: number;
}

export interface CmsPageSection {
  id: number;
  section_type: string;
  title: string | null;
  content: string | null;
  settings: Record<string, unknown> | null;
  sort_order: number;
}

export interface CmsPage {
  id: number;
  title: string;
  slug: string;
  template: string | null;
  sections: CmsPageSection[];
  seo: SeoMeta;
}

export interface AdminUser {
  id: number;
  email: string;
  first_name: string;
  last_name: string;
  roles: string[];
  permissions: string[];
  status: 'active' | 'inactive';
  last_login_at: string | null;
}

export interface InquiryNote {
  id: number;
  note: string;
  created_by: { id: number; name: string } | null;
  created_at: string;
}

export interface Inquiry {
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

export interface BookingRequest {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  event_type: string | null;
  event_date: string | null;
  event_location: string | null;
  message: string | null;
  status: 'new' | 'contacted' | 'quoted' | 'booked' | 'cancelled';
  ip_address: string | null;
  created_at: string;
}

export interface PublicSettings {
  site: {
    name: string;
    tagline: string | null;
    logo_url: string | null;
    favicon_url: string | null;
  };
  contact: {
    phone: string | null;
    email: string | null;
    address: string | null;
  };
  social: {
    instagram: string | null;
    facebook: string | null;
    twitter: string | null;
    pinterest: string | null;
  };
  seo: {
    default_title: string | null;
    default_description: string | null;
  };
}

export interface DashboardMetrics {
  pages: { total: number; published: number };
  galleries: { total: number; published: number };
  blog_posts: { total: number; published: number };
  media: { total: number };
  services: { total: number; published: number };
  testimonials: { total: number; published: number };
  hero_slides: { total: number; published: number };
  inquiries: { total: number; new: number; in_progress: number };
  bookings: { total: number; new: number; quoted: number };
}
```

---

## Step 4: API Client and Auth

### `src/api/client.ts`

```typescript
import axios, { AxiosError } from 'axios';
import type { ApiErrorResponse } from '../types/api';

let accessToken: string | null = null;
let isRefreshing = false;
let refreshQueue: Array<(token: string | null) => void> = [];

export function setAccessToken(token: string | null): void {
  accessToken = token;
}

export function getAccessToken(): string | null {
  return accessToken;
}

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL as string,
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' },
});

apiClient.interceptors.request.use((config) => {
  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`;
  }
  return config;
});

apiClient.interceptors.response.use(
  (res) => res,
  async (error: AxiosError<ApiErrorResponse>) => {
    const original = error.config!;

    if (error.response?.status === 401 && !(original as any)._retry) {
      (original as any)._retry = true;

      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          refreshQueue.push((token) => {
            if (token) {
              original.headers!.Authorization = `Bearer ${token}`;
              resolve(apiClient(original));
            } else {
              reject(error);
            }
          });
        });
      }

      isRefreshing = true;
      try {
        const { data } = await axios.post<{ data: { access_token: string } }>(
          `${import.meta.env.VITE_API_BASE_URL}/auth/refresh`,
          {},
          { withCredentials: true }
        );
        const newToken = data.data.access_token;
        setAccessToken(newToken);
        refreshQueue.forEach((resolve) => resolve(newToken));
        refreshQueue = [];
        original.headers!.Authorization = `Bearer ${newToken}`;
        return apiClient(original);
      } catch {
        setAccessToken(null);
        refreshQueue.forEach((resolve) => resolve(null));
        refreshQueue = [];
        window.location.href = '/admin/login';
        return Promise.reject(error);
      } finally {
        isRefreshing = false;
      }
    }

    return Promise.reject(error);
  }
);

import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,
      gcTime: 10 * 60 * 1000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});
```

### `src/store/AuthContext.tsx`

```typescript
import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { apiClient, setAccessToken } from '../api/client';
import type { AdminUser } from '../types/models';

interface AuthState {
  user: AdminUser | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthState | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AdminUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const silentRefresh = useCallback(async () => {
    try {
      const { data } = await apiClient.post<{
        data: { access_token: string; user: AdminUser };
      }>('/auth/refresh');
      setAccessToken(data.data.access_token);
      setUser(data.data.user);
    } catch {
      setAccessToken(null);
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    silentRefresh();
  }, [silentRefresh]);

  const login = useCallback(async (email: string, password: string) => {
    const { data } = await apiClient.post<{
      data: { access_token: string; user: AdminUser };
    }>('/auth/login', { email, password });
    setAccessToken(data.data.access_token);
    setUser(data.data.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await apiClient.post('/auth/logout');
    } finally {
      setAccessToken(null);
      setUser(null);
    }
  }, []);

  return (
    <AuthContext.Provider value={{
      user,
      isLoading,
      isAuthenticated: Boolean(user),
      login,
      logout,
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
```

### `src/api/auth.ts`

```typescript
import { apiClient } from './client';
import type { ApiResponse } from '../types/api';
import type { AdminUser } from '../types/models';

interface LoginResponse {
  access_token: string;
  expires_in: number;
  user: AdminUser;
}

export const authApi = {
  login: (credentials: { email: string; password: string }) =>
    apiClient.post<ApiResponse<LoginResponse>>('/auth/login', credentials),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get<ApiResponse<AdminUser>>('/auth/me'),
};
```

---

## Step 5: Utility Functions

### `src/utils/cn.ts`

```typescript
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}
```

### `src/utils/media.ts`

```typescript
import type { MediaRecord } from '../types/models';

const UPLOADS_BASE = import.meta.env.VITE_UPLOADS_BASE_URL as string;

export function mediaUrl(directory: string, storedName: string): string {
  return `${UPLOADS_BASE}/${directory}/${storedName}`;
}

export function thumbUrl(media: Pick<MediaRecord, 'directory' | 'stored_name'> & {
  thumb?: { directory: string; stored_name: string } | null;
}): string {
  if (media.thumb) return mediaUrl(media.thumb.directory, media.thumb.stored_name);
  return mediaUrl(media.directory, media.stored_name);
}
```

### `src/utils/api-errors.ts`

```typescript
import axios from 'axios';
import type { ApiErrorResponse } from '../types/api';

export function extractApiErrors(error: unknown): Record<string, string> {
  if (axios.isAxiosError<ApiErrorResponse>(error)) {
    const data = error.response?.data;
    if (data?.errors && typeof data.errors === 'object' && Object.keys(data.errors).length > 0) {
      return data.errors as Record<string, string>;
    }
    if (data?.message) {
      return { _root: data.message };
    }
  }
  return { _root: 'An unexpected error occurred. Please try again.' };
}

export function getErrorMessage(error: unknown): string {
  const errors = extractApiErrors(error);
  return errors._root ?? Object.values(errors)[0] ?? 'An unexpected error occurred.';
}
```

### `src/utils/format.ts`

```typescript
export function formatDate(dateString: string | null, options?: Intl.DateTimeFormatOptions): string {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    ...options,
  });
}

export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function truncate(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text;
  return text.slice(0, maxLength).trimEnd() + '…';
}
```

---

## Step 6: Query Hooks

Create a query hook file for each API domain. Follow this pattern:

### `src/api/galleries.ts`

```typescript
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from './client';
import type { ApiResponse } from '../types/api';
import type { Gallery, GalleryDetail, GalleryCategory } from '../types/models';
import toast from 'react-hot-toast';

// Query keys
export const galleryKeys = {
  all:        () => ['galleries'] as const,
  public:     (filters?: GalleryFilters) => ['galleries', 'public', filters] as const,
  detail:     (slug: string) => ['galleries', 'detail', slug] as const,
  categories: () => ['galleries', 'categories'] as const,
  admin:      (filters?: AdminGalleryFilters) => ['galleries', 'admin', filters] as const,
  adminOne:   (id: number) => ['galleries', 'admin', id] as const,
};

// Public hooks
export function useGalleries(filters: GalleryFilters = {}) {
  return useQuery({
    queryKey: galleryKeys.public(filters),
    queryFn: () =>
      apiClient.get<ApiResponse<Gallery[]>>('/galleries', { params: filters })
        .then((r) => ({ items: r.data.data, meta: r.data.meta })),
  });
}

export function useGallery(slug: string) {
  return useQuery({
    queryKey: galleryKeys.detail(slug),
    queryFn: () =>
      apiClient.get<ApiResponse<GalleryDetail>>(`/galleries/${slug}`)
        .then((r) => r.data.data),
    enabled: Boolean(slug),
  });
}

export function useGalleryCategories() {
  return useQuery({
    queryKey: galleryKeys.categories(),
    queryFn: () =>
      apiClient.get<ApiResponse<GalleryCategory[]>>('/galleries/categories')
        .then((r) => r.data.data),
  });
}

// Admin hooks
export function useAdminGalleries(filters: AdminGalleryFilters = {}) {
  return useQuery({
    queryKey: galleryKeys.admin(filters),
    staleTime: 60 * 1000,
    queryFn: () =>
      apiClient.get<ApiResponse<Gallery[]>>('/admin/galleries', { params: filters })
        .then((r) => ({ items: r.data.data, meta: r.data.meta })),
  });
}

export function useCreateGallery() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (data: CreateGalleryInput) =>
      apiClient.post<ApiResponse<Gallery>>('/admin/galleries', data).then((r) => r.data.data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: galleryKeys.all() });
      toast.success('Gallery created successfully');
    },
    onError: (err) => {
      toast.error(getErrorMessage(err));
    },
  });
}

// ... useUpdateGallery, useDeleteGallery, useAttachGalleryMedia, etc.
```

**Create similar hook files for:**
- `src/api/blog.ts` — posts, categories, tags
- `src/api/services.ts`
- `src/api/testimonials.ts`
- `src/api/hero-slides.ts`
- `src/api/pages.ts`
- `src/api/media.ts`
- `src/api/inquiries.ts`
- `src/api/bookings.ts`
- `src/api/settings.ts`
- `src/api/users.ts`
- `src/api/dashboard.ts`
- `src/api/contact.ts` — submit inquiry and booking

---

## Step 7: Layout Components

### `src/layouts/PublicLayout.tsx`

```tsx
import { Outlet } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import Navbar from '../components/layout/Navbar';
import Footer from '../components/layout/Footer';
import { usePublicSettings } from '../api/settings';

export default function PublicLayout() {
  const { data: settings } = usePublicSettings();

  return (
    <>
      <Helmet>
        <title>{settings?.seo?.default_title ?? 'Mesh Photography'}</title>
        {settings?.seo?.default_description && (
          <meta name="description" content={settings.seo.default_description} />
        )}
      </Helmet>
      <a
        href="#main-content"
        className="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-bronze focus:text-ivory focus:rounded focus:outline-none"
      >
        Skip to content
      </a>
      <Navbar settings={settings} />
      <main id="main-content">
        <Outlet />
      </main>
      <Footer settings={settings} />
    </>
  );
}
```

### `src/layouts/AdminLayout.tsx`

```tsx
import { Outlet } from 'react-router-dom';
import { useState } from 'react';
import AdminSidebar from '../components/layout/AdminSidebar';
import AdminTopbar from '../components/layout/AdminTopbar';
import { useAuth } from '../store/AuthContext';

export default function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { user, logout } = useAuth();

  return (
    <div className="flex h-screen bg-ivory-warm font-body">
      <AdminSidebar
        isOpen={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
        userPermissions={user?.permissions ?? []}
      />
      <div className="flex flex-1 flex-col overflow-hidden">
        <AdminTopbar
          user={user}
          onMenuClick={() => setSidebarOpen(true)}
          onLogout={logout}
        />
        <main className="flex-1 overflow-auto">
          <div className="p-6 max-w-7xl mx-auto">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}
```

### `src/layouts/AuthLayout.tsx`

```tsx
import { Outlet } from 'react-router-dom';

export default function AuthLayout() {
  return (
    <div className="min-h-screen bg-ivory-warm flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <h1 className="font-display text-4xl text-charcoal">Mesh Photography</h1>
          <p className="text-taupe mt-1">Studio Management</p>
        </div>
        <div className="bg-white rounded-2xl shadow-soft p-8">
          <Outlet />
        </div>
      </div>
    </div>
  );
}
```

---

## Step 8: UI Components

Implement all components in `src/components/ui/`. Every component must:
- Accept a `className` prop for customization
- Use `cn()` for class merging
- Have explicit TypeScript props interface
- Handle disabled/loading states where relevant

### `src/components/ui/Button.tsx`

```tsx
import { cn } from '../../utils/cn';
import { Loader2 } from 'lucide-react';
import React from 'react';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
}

const variants = {
  primary:   'bg-bronze text-ivory hover:bg-bronze-light active:bg-bronze-dark focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2',
  secondary: 'border border-bronze text-bronze hover:bg-bronze hover:text-ivory focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2',
  ghost:     'text-bronze hover:text-bronze-dark underline-offset-2 hover:underline focus:outline-none',
  danger:    'bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2',
};

const sizes = {
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2 text-sm',
  lg: 'px-6 py-3 text-base',
};

export function Button({
  variant = 'primary',
  size = 'md',
  isLoading,
  children,
  className,
  disabled,
  ...props
}: ButtonProps) {
  return (
    <button
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-lg font-body font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed',
        variants[variant],
        sizes[size],
        className
      )}
      disabled={isLoading || disabled}
      {...props}
    >
      {isLoading && <Loader2 className="h-4 w-4 animate-spin" />}
      {children}
    </button>
  );
}
```

### `src/components/ui/Input.tsx`

```tsx
import { cn } from '../../utils/cn';
import React from 'react';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  hasError?: boolean;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, hasError, ...props }, ref) => (
    <input
      ref={ref}
      className={cn(
        'w-full rounded-lg border px-3 py-2 text-sm font-body text-charcoal bg-white',
        'placeholder:text-taupe',
        'focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-1',
        'disabled:bg-cream disabled:cursor-not-allowed',
        'transition-colors duration-150',
        hasError
          ? 'border-red-500 focus:ring-red-500'
          : 'border-cream hover:border-taupe',
        className
      )}
      {...props}
    />
  )
);
Input.displayName = 'Input';
```

**Implement remaining UI primitives:**
- `Textarea.tsx` — same pattern as Input but `<textarea>`
- `Select.tsx` — native `<select>` with consistent styling
- `Badge.tsx` — for status, category tags
- `Card.tsx` — white card with shadow-soft and rounded-2xl
- `Modal.tsx` — centered overlay dialog with focus trap
- `Spinner.tsx` — animated loading indicator
- `Pagination.tsx` — page navigation with prev/next and page numbers
- `Skeleton.tsx` — animated grey placeholder

---

## Step 9: Public Pages

### `src/components/public/PageMeta.tsx`

```tsx
import { Helmet } from 'react-helmet-async';

interface PageMetaProps {
  title?: string;
  description?: string;
  ogImage?: string | null;
  ogType?: string;
  canonical?: string;
  noIndex?: boolean;
  jsonLd?: object;
}

export function PageMeta({
  title,
  description,
  ogImage,
  ogType = 'website',
  canonical,
  noIndex,
  jsonLd,
}: PageMetaProps) {
  const siteName = 'Mesh Photography';
  const fullTitle = title ? `${title} | ${siteName}` : siteName;

  return (
    <Helmet>
      <title>{fullTitle}</title>
      {description && <meta name="description" content={description} />}
      {canonical && <link rel="canonical" href={canonical} />}
      {noIndex && <meta name="robots" content="noindex,nofollow" />}
      <meta property="og:title" content={fullTitle} />
      {description && <meta property="og:description" content={description} />}
      <meta property="og:type" content={ogType} />
      {ogImage && <meta property="og:image" content={ogImage} />}
      <meta property="og:site_name" content={siteName} />
      {jsonLd && (
        <script type="application/ld+json">{JSON.stringify(jsonLd)}</script>
      )}
    </Helmet>
  );
}
```

### `src/pages/public/HomePage.tsx`

```tsx
import { useHeroSlides } from '../../api/hero-slides';
import { useGalleries } from '../../api/galleries';
import { useBlogPosts } from '../../api/blog';
import { useTestimonials } from '../../api/testimonials';
import { usePublicSettings } from '../../api/settings';
import { PageMeta } from '../../components/public/PageMeta';
import { HeroCarousel } from '../../components/public/HeroCarousel';
import { GalleryGrid } from '../../components/public/GalleryGrid';
import { BlogPostCard } from '../../components/public/BlogPostCard';
import { TestimonialCard } from '../../components/public/TestimonialCard';
import { Button } from '../../components/ui/Button';
import { Link } from 'react-router-dom';

export default function HomePage() {
  const { data: heroSlides }   = useHeroSlides();
  const { data: galleries }    = useGalleries({ featured: 'true', per_page: '6' });
  const { data: blogData }     = useBlogPosts({ per_page: '3' });
  const { data: testimonials } = useTestimonials({ limit: '3' });
  const { data: settings }     = usePublicSettings();

  return (
    <>
      <PageMeta
        title={settings?.seo?.default_title ?? undefined}
        description={settings?.seo?.default_description ?? undefined}
      />

      {/* Hero */}
      <HeroCarousel slides={heroSlides ?? []} />

      {/* Brand Statement */}
      <section className="py-16 lg:py-24 bg-ivory text-center px-4">
        <p className="font-display text-2xl lg:text-3xl text-charcoal max-w-2xl mx-auto leading-relaxed">
          {settings?.site?.tagline ?? 'Capturing timeless moments'}
        </p>
      </section>

      {/* Featured Galleries */}
      <section className="py-16 bg-ivory-warm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-end justify-between mb-8">
            <h2 className="font-display text-3xl text-charcoal">Portfolio</h2>
            <Link to="/portfolio" className="text-bronze hover:text-bronze-dark text-sm font-medium">
              View all galleries →
            </Link>
          </div>
          <GalleryGrid galleries={galleries?.items ?? []} columns={3} />
        </div>
      </section>

      {/* Testimonials */}
      {(testimonials?.length ?? 0) > 0 && (
        <section className="py-16 bg-ivory">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 className="font-display text-3xl text-charcoal text-center mb-10">What Clients Say</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {testimonials!.map((t) => (
                <TestimonialCard key={t.id} testimonial={t} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Recent Blog Posts */}
      {(blogData?.items?.length ?? 0) > 0 && (
        <section className="py-16 bg-parchment">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex items-end justify-between mb-8">
              <h2 className="font-display text-3xl text-charcoal">Stories</h2>
              <Link to="/blog" className="text-bronze hover:text-bronze-dark text-sm font-medium">
                Read all posts →
              </Link>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {blogData!.items.map((post) => (
                <BlogPostCard key={post.id} post={post} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      <section className="py-20 bg-espresso text-center">
        <div className="max-w-2xl mx-auto px-4">
          <h2 className="font-display text-4xl text-ivory mb-4">Let's Create Something Beautiful</h2>
          <p className="text-taupe mb-8">Your story deserves to be told beautifully. Get in touch to discuss your vision.</p>
          <div className="flex gap-4 justify-center">
            <Link to="/booking">
              <Button variant="primary" size="lg">Book a Session</Button>
            </Link>
            <Link to="/contact">
              <Button variant="secondary" size="lg" className="border-ivory text-ivory hover:bg-ivory hover:text-espresso">
                Contact Us
              </Button>
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
```

**Implement all other public pages** following the same pattern:
- `PortfolioPage` — gallery grid with category filter, `useGalleries`, `useGalleryCategories`
- `GalleryDetailPage` — gallery detail with Lightbox
- `ServicesPage` — service cards grid
- `ServiceDetailPage` — full service with CTA
- `BlogPage` — paginated posts with sidebar
- `BlogPostPage` — full post with DOMPurify body, related posts
- `BlogCategoryPage`, `BlogTagPage`, `BlogSearchPage`
- `TestimonialsPage`
- `ContactPage` — `ContactForm` with React Hook Form
- `BookingPage` — `BookingForm`
- `CmsPage` — renders `CmsPageSection[]` by type
- `NotFoundPage`

---

## Step 10: Admin Pages

Implement all admin pages following this pattern for list pages:

```tsx
// Generic admin list page pattern
export default function GalleryListPage() {
  const [filters, setFilters] = useState<AdminGalleryFilters>({});
  const { data, isLoading } = useAdminGalleries(filters);
  const deleteGallery = useDeleteGallery();

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h1 className="font-display text-2xl text-charcoal">Galleries</h1>
        <Link to="/admin/galleries/new">
          <Button><Plus className="h-4 w-4" /> New Gallery</Button>
        </Link>
      </div>

      {/* Filters */}
      <div className="flex gap-3 mb-4">
        <Select
          value={filters.status ?? ''}
          onChange={(e) => setFilters((f) => ({ ...f, status: e.target.value || undefined }))}
          options={[
            { value: '', label: 'All Statuses' },
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
          ]}
        />
        {/* search input */}
      </div>

      <DataTable
        isLoading={isLoading}
        data={data?.items ?? []}
        columns={[
          { header: 'Gallery', render: (g) => (
            <div className="flex items-center gap-3">
              {g.cover && <img src={g.cover.thumb_url ?? g.cover.url} className="w-10 h-10 object-cover rounded" />}
              <div>
                <div className="font-medium text-charcoal">{g.title}</div>
                <div className="text-xs text-taupe">{g.media_count} images</div>
              </div>
            </div>
          )},
          { header: 'Status', render: (g) => <StatusBadge status={g.status} /> },
          { header: 'Actions', render: (g) => (
            <div className="flex gap-2">
              <Link to={`/admin/galleries/${g.id}/edit`}>
                <Button size="sm" variant="secondary">Edit</Button>
              </Link>
              <ConfirmDialog
                trigger={<Button size="sm" variant="danger">Delete</Button>}
                title="Delete Gallery"
                description={`Are you sure you want to delete "${g.title}"? This cannot be undone.`}
                onConfirm={() => deleteGallery.mutate(g.id)}
              />
            </div>
          )},
        ]}
      />

      <Pagination
        currentPage={filters.page ?? 1}
        lastPage={data?.meta.last_page ?? 1}
        onPageChange={(page) => setFilters((f) => ({ ...f, page }))}
      />
    </div>
  );
}
```

**Admin form pages** follow this pattern — load existing data (for edit), use React Hook Form + Zod, submit via mutation:

```tsx
// Generic admin form pattern
export default function GalleryFormPage() {
  const { id } = useParams<{ id: string }>();
  const isEdit  = Boolean(id);
  const navigate = useNavigate();

  const { data: existing, isLoading } = useAdminGallery(Number(id), { enabled: isEdit });
  const createGallery = useCreateGallery();
  const updateGallery = useUpdateGallery();

  const form = useForm<GalleryFormValues>({
    resolver: zodResolver(gallerySchema),
    values: existing ? mapToFormValues(existing) : undefined,
  });

  const onSubmit = form.handleSubmit(async (data) => {
    try {
      if (isEdit) {
        await updateGallery.mutateAsync({ id: Number(id), ...data });
      } else {
        const created = await createGallery.mutateAsync(data);
        navigate(`/admin/galleries/${created.id}/edit`);
      }
    } catch (err) {
      const errors = extractApiErrors(err);
      Object.entries(errors).forEach(([field, msg]) => {
        if (field !== '_root') {
          form.setError(field as keyof GalleryFormValues, { message: msg });
        }
      });
    }
  });

  if (isEdit && isLoading) return <FormSkeleton />;

  return (
    <form onSubmit={onSubmit} className="max-w-2xl space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="font-display text-2xl text-charcoal">
          {isEdit ? 'Edit Gallery' : 'New Gallery'}
        </h1>
        <Button type="submit" isLoading={form.formState.isSubmitting}>
          {isEdit ? 'Save Changes' : 'Create Gallery'}
        </Button>
      </div>

      <FormField name="title" label="Gallery Title" form={form} required />
      <SlugInput name="slug" entityType="gallery" titleField="title" form={form} />
      <FormTextarea name="description" label="Description" form={form} rows={4} />
      <MediaPicker name="cover_media_id" label="Cover Image" form={form} accept="image" />

      <div className="grid grid-cols-2 gap-4">
        <FormSelect name="status" label="Status" form={form}
          options={[{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }]} />
        <FormField name="sort_order" label="Sort Order" type="number" form={form} />
      </div>

      <SeoPanel form={form} />
    </form>
  );
}
```

---

## Step 11: Key Admin Components

### `src/components/admin/SlugInput.tsx`

```tsx
// Debounced slug availability check
// On title change → debounce 500ms → slugify → GET /api/v1/admin/slug-check?value=...&type=gallery
// Show green checkmark if available, red X if taken
```

### `src/components/admin/MediaPicker.tsx`

```tsx
// Opens a modal with the full media library
// Filters: All / Images / Documents / Videos
// Search input with debounced query
// Grid of MediaRecord items with selection highlight
// On select: calls onChange(mediaRecord)
// Shows selected thumbnail in the form field
// "Remove" button clears selection
```

### `src/components/admin/RichTextEditor.tsx`

```tsx
// Tiptap editor with:
// - Bold, Italic, Heading (H2, H3), Bullet List, Ordered List
// - Link (insert/edit/remove)
// - Image (via MediaPicker integration)
// - Block quote
// Output: HTML string via editor.getHTML()
// Input: initial HTML string content
// Autosave: on change callback (used by BlogPostFormPage every 30s)
```

### `src/components/admin/SeoPanel.tsx`

```tsx
// Collapsible section with:
// - meta_title (text input + character counter: 0/60 chars, turns red at >60)
// - meta_description (textarea + character counter: 0/160 chars)
// - og_title, og_description
// - og_image (MediaPicker)
// - canonical_url
// - robots (select: index,follow / noindex,nofollow / noindex,follow)
```

---

## Step 12: `App.tsx` Router Configuration

```tsx
import React, { Suspense, lazy } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import PublicLayout from './layouts/PublicLayout';
import AdminLayout from './layouts/AdminLayout';
import AuthLayout from './layouts/AuthLayout';
import { useAuth } from './store/AuthContext';
import { Spinner } from './components/ui/Spinner';

// Private route guard
function PrivateRoute({ children }: { children: React.ReactNode }) {
  const { user, isLoading } = useAuth();
  const location = useLocation();
  if (isLoading) return <div className="min-h-screen flex items-center justify-center"><Spinner /></div>;
  if (!user) return <Navigate to="/admin/login" state={{ from: location }} replace />;
  return <>{children}</>;
}

// Lazy imports for all pages
const HomePage           = lazy(() => import('./pages/public/HomePage'));
const PortfolioPage      = lazy(() => import('./pages/public/PortfolioPage'));
const GalleryDetailPage  = lazy(() => import('./pages/public/GalleryDetailPage'));
// ... etc.

const LoginPage          = lazy(() => import('./pages/auth/LoginPage'));
const DashboardPage      = lazy(() => import('./pages/admin/DashboardPage'));
// ... etc.

export default function App() {
  return (
    <Suspense fallback={<div className="min-h-screen flex items-center justify-center bg-ivory"><Spinner /></div>}>
      <Routes>
        {/* Auth */}
        <Route element={<AuthLayout />}>
          <Route path="/admin/login" element={<LoginPage />} />
        </Route>

        {/* Admin */}
        <Route path="/admin" element={<PrivateRoute><AdminLayout /></PrivateRoute>}>
          <Route index element={<DashboardPage />} />
          <Route path="galleries" element={<GalleryListPage />} />
          <Route path="galleries/new" element={<GalleryFormPage />} />
          <Route path="galleries/:id/edit" element={<GalleryFormPage />} />
          <Route path="blog" element={<BlogPostListPage />} />
          <Route path="blog/new" element={<BlogPostFormPage />} />
          <Route path="blog/:id/edit" element={<BlogPostFormPage />} />
          <Route path="blog/categories" element={<BlogCategoriesPage />} />
          <Route path="media" element={<MediaLibraryPage />} />
          <Route path="pages" element={<PagesListPage />} />
          <Route path="pages/new" element={<PageFormPage />} />
          <Route path="pages/:id/edit" element={<PageFormPage />} />
          <Route path="services" element={<ServiceListPage />} />
          <Route path="services/new" element={<ServiceFormPage />} />
          <Route path="services/:id/edit" element={<ServiceFormPage />} />
          <Route path="testimonials" element={<TestimonialListPage />} />
          <Route path="hero-slides" element={<HeroSlideListPage />} />
          <Route path="inquiries" element={<InquiryListPage />} />
          <Route path="inquiries/:id" element={<InquiryDetailPage />} />
          <Route path="bookings" element={<BookingListPage />} />
          <Route path="bookings/:id" element={<BookingDetailPage />} />
          <Route path="settings" element={<SettingsPage />} />
          <Route path="users" element={<UsersPage />} />
          <Route path="*" element={<Navigate to="/admin" replace />} />
        </Route>

        {/* Public */}
        <Route element={<PublicLayout />}>
          <Route path="/" element={<HomePage />} />
          <Route path="/portfolio" element={<PortfolioPage />} />
          <Route path="/portfolio/category/:slug" element={<PortfolioPage />} />
          <Route path="/portfolio/:slug" element={<GalleryDetailPage />} />
          <Route path="/services" element={<ServicesPage />} />
          <Route path="/services/:slug" element={<ServiceDetailPage />} />
          <Route path="/blog" element={<BlogPage />} />
          <Route path="/blog/search" element={<BlogSearchPage />} />
          <Route path="/blog/category/:slug" element={<BlogCategoryPage />} />
          <Route path="/blog/tag/:slug" element={<BlogTagPage />} />
          <Route path="/blog/:slug" element={<BlogPostPage />} />
          <Route path="/testimonials" element={<TestimonialsPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/booking" element={<BookingPage />} />
          <Route path="/:slug" element={<CmsPage />} />
        </Route>

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  );
}
```

---

## Step 13: `src/main.tsx`

```tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { QueryClientProvider } from '@tanstack/react-query';
import { HelmetProvider } from 'react-helmet-async';
import { Toaster } from 'react-hot-toast';
import { AuthProvider } from './store/AuthContext';
import { queryClient } from './api/client';
import App from './App';
import './index.css';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <BrowserRouter>
      <HelmetProvider>
        <QueryClientProvider client={queryClient}>
          <AuthProvider>
            <App />
            <Toaster
              position="top-right"
              toastOptions={{
                className: 'font-body text-sm',
                duration: 4000,
                success: { iconTheme: { primary: '#9a7b5c', secondary: '#faf9f7' } },
              }}
            />
          </AuthProvider>
        </QueryClientProvider>
      </HelmetProvider>
    </BrowserRouter>
  </React.StrictMode>
);
```

---

## Accessibility Requirements

Every interactive component must meet these requirements:

1. **Buttons** — visible focus ring (`focus:ring-2 focus:ring-bronze`), `aria-label` if no text
2. **Images** — always provide `alt` attribute; empty string `alt=""` for decorative images
3. **Forms** — labels linked via `htmlFor`/`id`; error messages linked via `aria-describedby`; `aria-invalid` on fields with errors
4. **Modals** — focus trapped inside; `aria-modal="true"`, `role="dialog"`, `aria-labelledby`
5. **Nav menus** — `aria-expanded` on toggle; `aria-current="page"` on active link
6. **Carousels** — `role="region"`, `aria-label`, pause button, `aria-live` for announcements
7. **Loading states** — `aria-busy="true"` on loading containers
8. **Skip link** — first focusable element in `PublicLayout`

---

## Performance Requirements

1. **No layout shift (CLS)** — Always specify `width` and `height` on images
2. **Lazy loading** — All images below the fold use `loading="lazy"`
3. **Hero image** — Use `loading="eager"` and `fetchpriority="high"` on the first visible image
4. **Code splitting** — All pages lazy-loaded; admin bundle separate from public bundle
5. **Skeleton placeholders** — Match exact dimensions of loaded content to prevent CLS
6. **DOMPurify** — Always sanitize `dangerouslySetInnerHTML` blog body content:

```tsx
import DOMPurify from 'dompurify';

<div
  className="prose prose-neutral max-w-none"
  dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(post.body) }}
/>
```

---

## Common Mistakes to Avoid

1. **Importing Axios directly in components** — Always use `src/api/` module functions
2. **Storing access token in localStorage** — Token lives in `AuthContext` state only
3. **Missing error states** — Every `useQuery` hook usage must handle `isError`
4. **Hardcoded colors** — Always use Tailwind token classes (e.g., `text-bronze`, `bg-ivory`)
5. **Missing `alt` on images** — No image without an `alt` attribute, ever
6. **`any` type** — Use proper TypeScript types; check `docs/06-api-design.md` Section 17
7. **Direct DOM manipulation** — Use React state and refs only
8. **Not invalidating queries after mutations** — Always call `queryClient.invalidateQueries()` after create/update/delete mutations
9. **Forgetting loading states on forms** — Set `isLoading={form.formState.isSubmitting}` on submit buttons
10. **CmsPage rendering raw HTML without sanitization** — Always use DOMPurify on admin-authored HTML
