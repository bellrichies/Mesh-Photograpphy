# Mesh Photography — Frontend Architecture

> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Stack:** React 18 · TypeScript 5 · Tailwind CSS 3.4 · TanStack Query 5 · Vite 5

---

## Table of Contents

1. [Application Overview](#1-application-overview)
2. [Design System](#2-design-system)
3. [Application Structure](#3-application-structure)
4. [Routing Architecture](#4-routing-architecture)
5. [Authentication Flow](#5-authentication-flow)
6. [Data Fetching Strategy](#6-data-fetching-strategy)
7. [Component Architecture](#7-component-architecture)
8. [Public Pages](#8-public-pages)
9. [Admin Dashboard Pages](#9-admin-dashboard-pages)
10. [Form Handling](#10-form-handling)
11. [Image Handling](#11-image-handling)
12. [SEO Strategy](#12-seo-strategy)
13. [State Management](#13-state-management)
14. [Error Handling](#14-error-handling)
15. [Performance Optimization](#15-performance-optimization)
16. [Accessibility](#16-accessibility)
17. [Testing Strategy](#17-testing-strategy)

---

## 1. Application Overview

The frontend is a **React 18 SPA** that consumes all data from the PHP REST API. It serves two distinct contexts within the same bundle:

- **Public Site** — portfolio, services, blog, contact, booking, CMS pages (routes: `/`, `/portfolio`, `/services`, `/blog`, `/contact`, `/booking`, `/about`, etc.)
- **Admin Dashboard** — content management, media library, inquiry/booking management, settings (routes: `/admin/*`)
- **Auth** — login page (route: `/admin/login`)

All API communication flows through `src/api/` modules using Axios with JWT Bearer token authentication. React Query handles server-state caching, background refetching, and optimistic updates.

---

## 2. Design System

The design system is preserved verbatim from `app_docs/blueprint.md` §21.

### Color Tokens (`tailwind.config.ts`)

```typescript
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

### Typography Scale

| Role | Class | Font | Weight | Size |
|---|---|---|---|---|
| Display hero | `font-display text-6xl` | Cormorant Garamond | 300 | 60px |
| Display H1 | `font-display text-4xl` | Cormorant Garamond | 400 | 36px |
| Display H2 | `font-display text-3xl` | Cormorant Garamond | 400 | 30px |
| Section heading | `font-display text-2xl` | Cormorant Garamond | 500 | 24px |
| Body large | `font-body text-lg` | Inter | 400 | 18px |
| Body default | `font-body text-base` | Inter | 400 | 16px |
| Body small | `font-body text-sm` | Inter | 400 | 14px |
| Caption | `font-body text-xs` | Inter | 400 | 12px |
| UI label | `font-body text-sm font-medium` | Inter | 500 | 14px |

### Spacing and Layout

- Max content width: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`
- Section vertical padding: `py-16 lg:py-24`
- Card gap: `gap-6 lg:gap-8`
- Mobile breakpoint: 640px; Tablet: 768px; Desktop: 1024px; Wide: 1280px

### Component Variants

```typescript
// Button component with variant system
type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger';
type ButtonSize = 'sm' | 'md' | 'lg';

// primary: bg-bronze text-ivory hover:bg-bronze-light
// secondary: border border-bronze text-bronze hover:bg-bronze hover:text-ivory
// ghost: text-bronze hover:underline
// danger: bg-red-600 text-white hover:bg-red-700
```

---

## 3. Application Structure

### Entry Point (`src/main.tsx`)

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
                duration: 4000,
                style: { fontFamily: 'Inter, sans-serif' },
              }}
            />
          </AuthProvider>
        </QueryClientProvider>
      </HelmetProvider>
    </BrowserRouter>
  </React.StrictMode>
);
```

### Root App (`src/App.tsx`)

```tsx
import { Routes, Route } from 'react-router-dom';
import { Suspense, lazy } from 'react';

// Layouts
import PublicLayout from './layouts/PublicLayout';
import AdminLayout from './layouts/AdminLayout';
import AuthLayout from './layouts/AuthLayout';

// Auth guard
import PrivateRoute from './components/auth/PrivateRoute';

// Pages (lazy-loaded by route group)
const HomePage         = lazy(() => import('./pages/public/HomePage'));
const PortfolioPage    = lazy(() => import('./pages/public/PortfolioPage'));
// ... more public pages

const DashboardPage    = lazy(() => import('./pages/admin/DashboardPage'));
// ... more admin pages

const LoginPage        = lazy(() => import('./pages/auth/LoginPage'));

export default function App() {
  return (
    <Suspense fallback={<PageSpinner />}>
      <Routes>
        {/* Auth */}
        <Route element={<AuthLayout />}>
          <Route path="/admin/login" element={<LoginPage />} />
        </Route>

        {/* Admin */}
        <Route element={<PrivateRoute><AdminLayout /></PrivateRoute>}>
          <Route path="/admin" element={<DashboardPage />} />
          <Route path="/admin/galleries" element={<GalleryListPage />} />
          {/* ... */}
        </Route>

        {/* Public */}
        <Route element={<PublicLayout />}>
          <Route path="/" element={<HomePage />} />
          <Route path="/portfolio" element={<PortfolioPage />} />
          {/* ... */}
          <Route path="/:slug" element={<CmsPage />} />
        </Route>

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  );
}
```

---

## 4. Routing Architecture

### Public Routes

| Path | Component | Data Fetched |
|---|---|---|
| `/` | `HomePage` | hero-slides, galleries (featured), blog/recent, testimonials, settings |
| `/portfolio` | `PortfolioPage` | galleries, gallery-categories |
| `/portfolio/category/:slug` | `PortfolioPage` | galleries filtered by category |
| `/portfolio/:slug` | `GalleryDetailPage` | gallery + media |
| `/services` | `ServicesPage` | services list |
| `/services/:slug` | `ServiceDetailPage` | single service |
| `/blog` | `BlogPage` | posts (paginated), categories, tags |
| `/blog/search` | `BlogSearchPage` | search results |
| `/blog/category/:slug` | `BlogCategoryPage` | posts by category |
| `/blog/tag/:slug` | `BlogTagPage` | posts by tag |
| `/blog/:slug` | `BlogPostPage` | post, related posts |
| `/testimonials` | `TestimonialsPage` | all testimonials |
| `/contact` | `ContactPage` | static |
| `/booking` | `BookingPage` | static |
| `/:slug` | `CmsPage` | CMS page + sections |
| `/admin/login` | `LoginPage` | static |

### Admin Routes (all protected)

| Path | Component | Permission |
|---|---|---|
| `/admin` | `DashboardPage` | authenticated |
| `/admin/galleries` | `GalleryListPage` | `manage-galleries` |
| `/admin/galleries/new` | `GalleryFormPage` | `manage-galleries` |
| `/admin/galleries/:id/edit` | `GalleryFormPage` | `manage-galleries` |
| `/admin/blog` | `BlogPostListPage` | `manage-blog` |
| `/admin/blog/new` | `BlogPostFormPage` | `manage-blog` |
| `/admin/blog/:id/edit` | `BlogPostFormPage` | `manage-blog` |
| `/admin/blog/categories` | `BlogCategoriesPage` | `manage-blog` |
| `/admin/media` | `MediaLibraryPage` | `manage-media` |
| `/admin/pages` | `PagesListPage` | `manage-pages` |
| `/admin/pages/new` | `PageFormPage` | `manage-pages` |
| `/admin/pages/:id/edit` | `PageFormPage` | `manage-pages` |
| `/admin/services` | `ServiceListPage` | `manage-services` |
| `/admin/testimonials` | `TestimonialListPage` | `manage-testimonials` |
| `/admin/hero-slides` | `HeroSlideListPage` | `manage-pages` |
| `/admin/inquiries` | `InquiryListPage` | `manage-inquiries` |
| `/admin/inquiries/:id` | `InquiryDetailPage` | `manage-inquiries` |
| `/admin/bookings` | `BookingListPage` | `manage-inquiries` |
| `/admin/bookings/:id` | `BookingDetailPage` | `manage-inquiries` |
| `/admin/settings` | `SettingsPage` | `manage-settings` |
| `/admin/users` | `UsersPage` | `manage-users` |

### `PrivateRoute` Component

```tsx
export default function PrivateRoute({ children }: { children: React.ReactNode }) {
  const { user, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) return <PageSpinner />;
  if (!user) return <Navigate to="/admin/login" state={{ from: location }} replace />;

  return <>{children}</>;
}
```

---

## 5. Authentication Flow

### AuthContext (`src/store/AuthContext.tsx`)

```tsx
interface AuthState {
  user: AdminUser | null;
  accessToken: string | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshToken: () => Promise<string | null>;
}

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
  const [user, setUser] = useState<AdminUser | null>(null);
  const [accessToken, setAccessToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // On mount: try to refresh token from httpOnly cookie
  useEffect(() => {
    silentRefresh().finally(() => setIsLoading(false));
  }, []);

  const login = async (email: string, password: string) => {
    const res = await authApi.login({ email, password });
    setAccessToken(res.data.access_token);
    setUser(res.data.user);
  };

  const logout = async () => {
    await authApi.logout();
    setAccessToken(null);
    setUser(null);
  };

  const silentRefresh = async () => {
    try {
      const res = await authApi.refresh();
      setAccessToken(res.data.access_token);
      setUser(res.data.user);
    } catch {
      setAccessToken(null);
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider value={{ user, accessToken, isLoading, login, logout, refreshToken: silentRefresh }}>
      {children}
    </AuthContext.Provider>
  );
};
```

### Axios Interceptors (`src/api/client.ts`)

```typescript
import axios from 'axios';

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  withCredentials: true, // sends httpOnly refresh cookie
});

// Attach access token
apiClient.interceptors.request.use((config) => {
  const token = getAccessToken(); // read from AuthContext or memory
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Handle 401 — try silent refresh
let isRefreshing = false;
let refreshQueue: Array<(token: string) => void> = [];

apiClient.interceptors.response.use(
  (res) => res,
  async (error) => {
    const original = error.config;
    if (error.response?.status === 401 && !original._retry) {
      original._retry = true;
      if (!isRefreshing) {
        isRefreshing = true;
        try {
          const newToken = await refreshAccessToken();
          refreshQueue.forEach((resolve) => resolve(newToken));
          refreshQueue = [];
        } catch {
          // Refresh failed — redirect to login
          redirectToLogin();
          return Promise.reject(error);
        } finally {
          isRefreshing = false;
        }
      }
      return new Promise((resolve) => {
        refreshQueue.push((token) => {
          original.headers.Authorization = `Bearer ${token}`;
          resolve(apiClient(original));
        });
      });
    }
    return Promise.reject(error);
  }
);
```

---

## 6. Data Fetching Strategy

All server state is managed by **TanStack Query**. Components never make direct Axios calls — they use query hooks.

### Query Client Configuration (`src/api/client.ts`)

```typescript
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,  // 5 minutes for public content
      gcTime:    10 * 60 * 1000, // 10 minutes garbage collection
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});
```

### Example Query Hook

```typescript
// src/api/galleries.ts
export const galleryKeys = {
  all:     ['galleries'] as const,
  list:    (filters: GalleryFilters) => [...galleryKeys.all, 'list', filters] as const,
  detail:  (slug: string) => [...galleryKeys.all, 'detail', slug] as const,
};

export function useGalleries(filters: GalleryFilters = {}) {
  return useQuery({
    queryKey: galleryKeys.list(filters),
    queryFn: () => apiClient.get<ApiResponse<Gallery[]>>('/galleries', { params: filters })
      .then((res) => res.data.data),
  });
}

export function useGallery(slug: string) {
  return useQuery({
    queryKey: galleryKeys.detail(slug),
    queryFn: () => apiClient.get<ApiResponse<GalleryDetail>>(`/galleries/${slug}`)
      .then((res) => res.data.data),
    enabled: Boolean(slug),
  });
}
```

### Mutation Hook with Optimistic Invalidation

```typescript
export function useCreateGallery() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (data: CreateGalleryInput) =>
      apiClient.post<ApiResponse<Gallery>>('/admin/galleries', data).then((r) => r.data.data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: galleryKeys.all });
      toast.success('Gallery created successfully');
    },
    onError: (err: AxiosError<ApiErrorResponse>) => {
      toast.error(err.response?.data?.message ?? 'Failed to create gallery');
    },
  });
}
```

### Stale Time Configuration

| Content Type | `staleTime` | Notes |
|---|---|---|
| Public galleries, services, blog | 5 minutes | Low change frequency |
| Homepage (hero, testimonials) | 5 minutes | |
| Admin listings | 60 seconds | Refreshes more often |
| Dashboard metrics | 30 seconds | Near-real-time |
| Settings | 5 minutes | Changes invalidate immediately |
| Media library | 60 seconds | Uploads trigger invalidation |

---

## 7. Component Architecture

### Component Categories

#### UI Primitives (`src/components/ui/`)

Reusable, unstyled-to-lightly-styled building blocks:

```tsx
// Button.tsx
interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
}

const variants = {
  primary:   'bg-bronze text-ivory hover:bg-bronze-light',
  secondary: 'border border-bronze text-bronze hover:bg-bronze hover:text-ivory',
  ghost:     'text-bronze hover:underline',
  danger:    'bg-red-600 text-white hover:bg-red-700',
};

export function Button({ variant = 'primary', size = 'md', isLoading, children, ...props }) {
  return (
    <button
      className={cn(baseClasses, variants[variant], sizes[size])}
      disabled={isLoading || props.disabled}
      {...props}
    >
      {isLoading ? <Spinner size="sm" /> : children}
    </button>
  );
}
```

#### Layout Components (`src/components/layout/`)

**`PublicLayout.tsx`** — Renders `<Navbar>`, `<Outlet>`, `<Footer>`

```tsx
export default function PublicLayout() {
  const { data: settings } = usePublicSettings();
  return (
    <>
      <Navbar logo={settings?.site?.logo} />
      <main id="main-content">
        <Outlet />
      </main>
      <Footer settings={settings} />
    </>
  );
}
```

**`AdminLayout.tsx`** — Renders `<AdminTopbar>`, `<AdminSidebar>`, `<Outlet>`

```tsx
export default function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { user } = useAuth();
  return (
    <div className="flex h-screen bg-ivory-warm">
      <AdminSidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      <div className="flex-1 flex flex-col overflow-hidden">
        <AdminTopbar user={user} onMenuClick={() => setSidebarOpen(true)} />
        <main className="flex-1 overflow-auto p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
```

#### Public Components (`src/components/public/`)

**`HeroCarousel.tsx`** — Auto-rotating slides, keyboard-accessible, pause on focus/hover

**`GalleryGrid.tsx`** — Responsive masonry grid, category filter tabs

**`GalleryCard.tsx`** — Cover image with hover overlay, title, category badge

**`Lightbox.tsx`** — Wraps `yet-another-react-lightbox` with custom toolbar

**`ContactForm.tsx`** — React Hook Form + Zod validation, loading states, server error display

**`BookingForm.tsx`** — Extended contact form with event-specific fields

**`CategoryFilter.tsx`** — Pill-style filter tabs for portfolio categories

#### Admin Components (`src/components/admin/`)

**`MediaPicker.tsx`** — Modal that opens the media library, allows selection, returns selected media

```tsx
interface MediaPickerProps {
  value?: MediaRecord | null;
  onChange: (media: MediaRecord | null) => void;
  accept?: 'image' | 'document' | 'video';
  label?: string;
}
```

**`MediaUploader.tsx`** — Drag-and-drop zone + file input, upload progress, calls `POST /api/v1/admin/media/upload`

**`RichTextEditor.tsx`** — Tiptap-based editor with Bold/Italic/Heading/Link/Image toolbar

**`SlugInput.tsx`** — Debounced availability check against API, shows green/red status indicator

**`SeoPanel.tsx`** — Collapsible SEO fields: meta title (character counter), meta description, OG title/description, OG image picker, robots

**`DataTable.tsx`** — Reusable sortable table with pagination controls

**`ConfirmDialog.tsx`** — Modal confirmation for destructive actions (delete, archive)

**`SortableList.tsx`** — Drag-to-reorder wrapper (wraps `@dnd-kit/sortable`)

---

## 8. Public Pages

### `HomePage.tsx`

```tsx
export default function HomePage() {
  const { data: heroSlides }    = useHeroSlides();
  const { data: galleries }     = useGalleries({ featured: true, limit: 6 });
  const { data: recentPosts }   = useBlogPosts({ limit: 3 });
  const { data: testimonials }  = useTestimonials({ limit: 3 });
  const { data: settings }      = usePublicSettings();

  return (
    <>
      <PageMeta title={settings?.seo?.title} description={settings?.seo?.description} />
      <HeroCarousel slides={heroSlides ?? []} />
      <IntroSection content={settings?.site?.tagline} />
      <FeaturedGalleries galleries={galleries ?? []} />
      <ServicesTeaser />
      <TestimonialsCarousel testimonials={testimonials ?? []} />
      <RecentBlogPosts posts={recentPosts ?? []} />
      <CtaSection />
    </>
  );
}
```

### `GalleryDetailPage.tsx`

```tsx
export default function GalleryDetailPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: gallery, isLoading, isError } = useGallery(slug!);
  const [lightboxIndex, setLightboxIndex] = useState(-1);

  if (isLoading) return <GalleryDetailSkeleton />;
  if (isError || !gallery) return <NotFound />;

  return (
    <>
      <PageMeta title={gallery.seo?.meta_title ?? gallery.title} description={gallery.seo?.meta_description} />
      <section className="py-16">
        <h1 className="font-display text-4xl">{gallery.title}</h1>
        <p className="text-taupe">{gallery.description}</p>
      </section>
      <GalleryGrid
        images={gallery.media}
        onImageClick={(index) => setLightboxIndex(index)}
      />
      <Lightbox
        open={lightboxIndex >= 0}
        index={lightboxIndex}
        slides={gallery.media.map((m) => ({ src: m.url, alt: m.alt_text }))}
        close={() => setLightboxIndex(-1)}
      />
    </>
  );
}
```

### `BlogPostPage.tsx`

```tsx
export default function BlogPostPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: post } = useBlogPost(slug!);

  return (
    <>
      <PageMeta title={post?.seo?.meta_title ?? post?.title} />
      <article className="max-w-3xl mx-auto py-16 px-4">
        <header>
          <h1 className="font-display text-4xl">{post?.title}</h1>
          <PostMeta post={post} />
        </header>
        {post?.cover && <img src={post.cover.thumb_url} alt={post.cover.alt_text} loading="eager" />}
        {/* dangerouslySetInnerHTML sanitized with DOMPurify */}
        <div
          className="prose prose-neutral max-w-none"
          dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(post?.body ?? '') }}
        />
        <RelatedPosts posts={post?.related ?? []} />
      </article>
    </>
  );
}
```

### `ContactPage.tsx`

```tsx
export default function ContactPage() {
  const { mutateAsync: sendInquiry, isPending } = useSubmitInquiry();

  const form = useForm<ContactFormValues>({
    resolver: zodResolver(contactSchema),
  });

  const handleSubmit = form.handleSubmit(async (data) => {
    try {
      await sendInquiry(data);
      form.reset();
      toast.success('Your message has been sent! We\'ll be in touch soon.');
    } catch (err) {
      const errors = extractApiErrors(err);
      Object.entries(errors).forEach(([field, msg]) => {
        form.setError(field as keyof ContactFormValues, { message: msg });
      });
    }
  });

  return (
    <>
      <PageMeta title="Contact | Mesh Photography" />
      <section className="max-w-2xl mx-auto py-16 px-4">
        <h1 className="font-display text-4xl">Get in Touch</h1>
        <form onSubmit={handleSubmit} noValidate>
          <FormField name="name" label="Your Name" form={form} required />
          <FormField name="email" label="Email Address" type="email" form={form} required />
          <FormField name="phone" label="Phone (optional)" type="tel" form={form} />
          <FormField name="subject" label="Subject" form={form} />
          <FormTextarea name="message" label="Your Message" form={form} required rows={6} />
          <Button type="submit" isLoading={isPending}>Send Message</Button>
        </form>
      </section>
    </>
  );
}
```

---

## 9. Admin Dashboard Pages

### `DashboardPage.tsx`

```tsx
export default function DashboardPage() {
  const { data: metrics } = useDashboardMetrics(); // 30-second staleTime

  return (
    <>
      <h1 className="font-display text-2xl mb-6">Dashboard</h1>
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <MetricCard label="Galleries" value={metrics?.galleries.total} sub={`${metrics?.galleries.published} published`} />
        <MetricCard label="Blog Posts" value={metrics?.blog_posts.total} />
        <MetricCard label="New Inquiries" value={metrics?.inquiries.new} accent="bronze" />
        <MetricCard label="New Bookings" value={metrics?.bookings.new} accent="bronze" />
        {/* ... */}
      </div>
    </>
  );
}
```

### `GalleryFormPage.tsx`

```tsx
export default function GalleryFormPage() {
  const { id } = useParams<{ id: string }>();
  const isEdit = Boolean(id);
  const { data: existing } = useAdminGallery(id!);
  const createGallery = useCreateGallery();
  const updateGallery = useUpdateGallery();

  const form = useForm<GalleryFormValues>({
    resolver: zodResolver(gallerySchema),
    values: existing ? mapGalleryToForm(existing) : undefined,
  });

  return (
    <div className="max-w-3xl">
      <h1 className="font-display text-2xl mb-6">{isEdit ? 'Edit Gallery' : 'New Gallery'}</h1>
      <form onSubmit={form.handleSubmit(onSubmit)}>
        <FormField name="title" label="Gallery Title" form={form} required />
        <SlugInput name="slug" title={form.watch('title')} entityType="gallery" form={form} />
        <FormTextarea name="description" label="Description" form={form} rows={4} />
        <MediaPicker name="cover_media_id" label="Cover Image" form={form} />
        <FormSelect name="status" label="Status" form={form}
          options={[{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }]} />
        <CategoryCheckboxes name="category_ids" form={form} />
        <SeoPanel form={form} />
        <Button type="submit" isLoading={form.formState.isSubmitting}>
          {isEdit ? 'Save Changes' : 'Create Gallery'}
        </Button>
      </form>

      {isEdit && <GalleryMediaManager galleryId={id!} />}
    </div>
  );
}
```

### `BlogPostFormPage.tsx`

Features:
- Tiptap rich text editor with autosave (30-second interval)
- Media attachment manager (cover image, inline images)
- Category and tag multi-select with inline create
- SEO panel with character counters
- Status selector (draft/published/scheduled/archived)
- `published_at` datetime picker (shown when scheduled)
- Revision history sidebar

### `MediaLibraryPage.tsx`

Features:
- Grid view with masonry layout
- Type filter tabs (All / Images / Videos / Documents)
- Search input with debounced API query
- `MediaUploader` drop zone at top
- Click to open detail panel (edit alt text, title, caption)
- Multi-select for bulk archive
- Infinite scroll or paginated grid (React Query `useInfiniteQuery`)

### `InquiryDetailPage.tsx`

Features:
- Full inquiry details (name, email, phone, subject, message, IP, date)
- Status selector with `PATCH /api/v1/admin/inquiries/:id/status`
- Internal notes list (append-only, attributed to admin)
- Add note form with `POST /api/v1/admin/inquiries/:id/notes`
- Notes update optimistically via React Query

---

## 10. Form Handling

### Zod Schema Examples

```typescript
// src/api/schemas/contact.ts
import { z } from 'zod';

export const contactSchema = z.object({
  name:    z.string().min(2, 'Name must be at least 2 characters').max(255),
  email:   z.string().email('Please enter a valid email address'),
  phone:   z.string().max(50).optional().or(z.literal('')),
  subject: z.string().max(255).optional().or(z.literal('')),
  message: z.string().min(10, 'Message must be at least 10 characters').max(5000),
});

export type ContactFormValues = z.infer<typeof contactSchema>;
```

### Server Error Handling Pattern

```typescript
// src/utils/api-errors.ts
export function extractApiErrors(error: unknown): Record<string, string> {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as ApiErrorResponse;
    if (data?.errors && typeof data.errors === 'object') {
      return data.errors;
    }
    if (data?.message) {
      return { _root: data.message };
    }
  }
  return { _root: 'An unexpected error occurred' };
}
```

### `FormField` Wrapper Component

```tsx
interface FormFieldProps {
  name: string;
  label: string;
  type?: string;
  required?: boolean;
  form: UseFormReturn<any>;
}

export function FormField({ name, label, type = 'text', required, form }: FormFieldProps) {
  const { register, formState: { errors } } = form;
  const error = errors[name]?.message as string | undefined;

  return (
    <div className="mb-4">
      <label htmlFor={name} className="block text-sm font-medium text-charcoal mb-1">
        {label} {required && <span className="text-red-500">*</span>}
      </label>
      <input
        id={name}
        type={type}
        className={cn(inputBase, error ? inputError : inputNormal)}
        aria-describedby={error ? `${name}-error` : undefined}
        aria-invalid={Boolean(error)}
        {...register(name)}
      />
      {error && (
        <p id={`${name}-error`} className="mt-1 text-sm text-red-600" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
```

---

## 11. Image Handling

### Image URL Construction

```typescript
// src/utils/media.ts
const BASE = import.meta.env.VITE_UPLOADS_BASE_URL;

export function mediaUrl(media: { directory: string; stored_name: string }): string {
  return `${BASE}/${media.directory}/${media.stored_name}`;
}

export function thumbUrl(media: MediaRecord): string {
  if (media.thumb) return mediaUrl(media.thumb);
  return mediaUrl(media); // fallback to original
}
```

### Image Component

```tsx
interface OptimizedImageProps {
  media: MediaRecord;
  className?: string;
  alt?: string;
  priority?: boolean; // for hero images: loading="eager"
}

export function OptimizedImage({ media, className, alt, priority = false }: OptimizedImageProps) {
  return (
    <img
      src={thumbUrl(media)}
      alt={alt ?? media.alt_text ?? ''}
      loading={priority ? 'eager' : 'lazy'}
      decoding="async"
      className={className}
      width={media.thumb?.width ?? media.width}
      height={media.thumb?.height ?? media.height}
      onError={(e) => {
        // Fallback to original if thumb fails
        (e.target as HTMLImageElement).src = mediaUrl(media);
      }}
    />
  );
}
```

### Gallery Lightbox

```tsx
import Lightbox from 'yet-another-react-lightbox';
import Captions from 'yet-another-react-lightbox/plugins/captions';
import Fullscreen from 'yet-another-react-lightbox/plugins/fullscreen';
import Keyboard from 'yet-another-react-lightbox/plugins/keyboard';

<Lightbox
  plugins={[Captions, Fullscreen, Keyboard]}
  open={lightboxIndex >= 0}
  index={lightboxIndex}
  close={() => setLightboxIndex(-1)}
  slides={gallery.media.map((m) => ({
    src: mediaUrl(m),
    alt: m.alt_text ?? '',
    title: m.caption ?? '',
    width: m.width,
    height: m.height,
  }))}
/>
```

---

## 12. SEO Strategy

### `PageMeta` Component (React Helmet Async)

```tsx
import { Helmet } from 'react-helmet-async';

interface PageMetaProps {
  title?: string;
  description?: string;
  ogImage?: string;
  ogType?: string;
  canonical?: string;
  noIndex?: boolean;
}

export function PageMeta({ title, description, ogImage, ogType = 'website', canonical, noIndex }: PageMetaProps) {
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
    </Helmet>
  );
}
```

### JSON-LD Structured Data

```tsx
export function JsonLd({ data }: { data: object }) {
  return (
    <Helmet>
      <script type="application/ld+json">
        {JSON.stringify(data)}
      </script>
    </Helmet>
  );
}

// Usage on blog post page
<JsonLd data={{
  '@context': 'https://schema.org',
  '@type': 'BlogPosting',
  headline: post.title,
  datePublished: post.published_at,
  author: { '@type': 'Person', name: post.author_name },
  image: post.cover?.url,
}} />
```

### Sitemap

Served by the PHP API at `GET /api/v1/sitemap` (returns XML). The React frontend does NOT serve the sitemap — the Apache config proxies `/sitemap.xml` to the PHP backend.

---

## 13. State Management

### Server State: TanStack Query

All API data is managed by TanStack Query. The query cache serves as the server-state store.

### Client State: React Context

Two contexts handle application-wide client state:

1. **`AuthContext`** — JWT access token, current user, login/logout functions
2. **Toast notifications** — via `react-hot-toast` (no custom context needed)

### Local State: `useState` / `useReducer`

- UI state (modal open/close, sidebar expanded, filter selections, lightbox index)
- Form state is managed by React Hook Form (not React state)

### No Redux

The application does not use Redux. TanStack Query + Context handles all state needs cleanly at this scale. If the admin grows significantly, Zustand is the preferred addition (lightweight, no boilerplate).

---

## 14. Error Handling

### API Error Handling Pattern

```typescript
// Types
interface ApiResponse<T> {
  ok: boolean;
  data: T;
  message: string;
  errors: Record<string, string>;
  meta: Record<string, unknown>;
}

interface ApiErrorResponse extends ApiResponse<null> {
  ok: false;
}
```

### Error Boundary

```tsx
// src/components/ErrorBoundary.tsx
export class ErrorBoundary extends React.Component<Props, State> {
  state = { hasError: false, error: null };

  static getDerivedStateFromError(error: Error) {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, info: React.ErrorInfo) {
    console.error('React ErrorBoundary caught:', error, info);
  }

  render() {
    if (this.state.hasError) {
      return <ErrorPage message="Something went wrong. Please try refreshing the page." />;
    }
    return this.props.children;
  }
}
```

### Query Error States

```tsx
// Every data-dependent page handles loading, error, and empty states
const { data, isLoading, isError, error } = useGalleries();

if (isLoading) return <GalleryGridSkeleton />;
if (isError) return <ErrorMessage message={getErrorMessage(error)} />;
if (!data?.length) return <EmptyState message="No galleries found." />;
```

### Loading Skeleton Pattern

```tsx
// Skeleton components match the layout of the loaded content
export function GalleryGridSkeleton() {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      {Array.from({ length: 6 }).map((_, i) => (
        <div key={i} className="aspect-square bg-cream animate-pulse rounded-lg" />
      ))}
    </div>
  );
}
```

---

## 15. Performance Optimization

### Code Splitting

All page-level components are lazy-loaded via `React.lazy()`:

```typescript
const GalleryDetailPage = lazy(() => import('./pages/public/GalleryDetailPage'));
const BlogPostFormPage  = lazy(() => import('./pages/admin/blog/BlogPostFormPage'));
```

Route-group lazy loading: admin chunk is only loaded when a user navigates to `/admin/*`.

### Image Optimization

- Thumbnails (640px JPEG) used in all list/grid views
- Full-size originals loaded only in lightbox
- `loading="lazy"` on all non-hero images
- `loading="eager"` + `fetchpriority="high"` on LCP hero image
- `width` and `height` attributes to prevent CLS

### Tailwind Purging

Vite + Tailwind JIT scans `src/**/*.{ts,tsx}` and produces minimal CSS (< 10KB gzipped for typical pages).

### TanStack Query Prefetching

On hover over navigation links, prefetch the target page's data:

```typescript
const prefetchGallery = (slug: string) => {
  queryClient.prefetchQuery({
    queryKey: galleryKeys.detail(slug),
    queryFn: () => fetchGallery(slug),
    staleTime: 5 * 60 * 1000,
  });
};
```

### Bundle Analysis

```bash
npm run build -- --mode analyze
# Or: npx vite-bundle-visualizer
```

Keep initial bundle < 200KB gzipped. Move large libraries (Tiptap, Lightbox) to admin-only chunks.

---

## 16. Accessibility

### Skip to Content

```html
<!-- public/index.html -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-bronze focus:text-ivory focus:rounded">
  Skip to content
</a>
```

### Focus Management

- Modal dialogs use `focus-trap-react` to keep focus inside while open
- After navigation, focus moves to `<h1>` or `<main>` for screen reader announcement
- Custom hook `useFocusOnMount(ref)` handles this

### ARIA Patterns

```tsx
// HeroCarousel
<div role="region" aria-label="Featured photography carousel" aria-roledescription="carousel">
  <div role="group" aria-roledescription="slide" aria-label={`Slide ${current + 1} of ${total}`}>
    ...
  </div>
  <button aria-label="Previous slide" onClick={prev}>...</button>
  <button aria-label="Next slide" onClick={next}>...</button>
  <button aria-label="Pause carousel" onClick={togglePause}>...</button>
</div>

// Navigation
<nav aria-label="Main navigation">
  <a aria-current={isActive ? 'page' : undefined}>Portfolio</a>
</nav>
```

### Color Contrast

Tailwind classes configured to meet WCAG AA:
- Body text on ivory: `text-charcoal` on `bg-ivory` — 15:1 contrast ratio
- Bronze accent on white: `text-bronze-dark` — meets 4.5:1 threshold

---

## 17. Testing Strategy

### Unit Tests

```bash
npm install -D vitest @testing-library/react @testing-library/user-event jsdom
```

Priority tests:
- `Button.test.tsx` — variant rendering, disabled state, loading state
- `SlugInput.test.tsx` — debounce behavior, API call, validation state
- `contactSchema.test.ts` — Zod validation rules
- `useAuth.test.ts` — login, logout, token refresh flow
- `extractApiErrors.test.ts` — error parsing utility

### Integration Tests

Priority flows:
- `ContactForm.test.tsx` — fill, validate, submit, success/error state
- `MediaUploader.test.tsx` — file selection, upload progress, grid update
- `LoginPage.test.tsx` — login success/failure, redirect

### E2E Tests (Playwright — Tier 2)

Priority paths:
- Homepage loads and displays hero carousel
- Portfolio browse → gallery detail → lightbox opens
- Contact form submission
- Admin login → create gallery → upload image → attach to gallery
- Blog post create → publish

```bash
npm install -D @playwright/test
npx playwright install
```
