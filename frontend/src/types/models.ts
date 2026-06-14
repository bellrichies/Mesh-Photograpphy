// ─── Media ────────────────────────────────────────────────────────────────────

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

// ─── SEO ──────────────────────────────────────────────────────────────────────

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

// ─── Users ────────────────────────────────────────────────────────────────────

export interface AdminUser {
  id: number;
  email: string;
  first_name: string;
  last_name: string;
  roles: string[];
  permissions: string[];
  status?: 'active' | 'inactive';
  last_login_at?: string | null;
}

// ─── Gallery ──────────────────────────────────────────────────────────────────

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

// Minimal photo shape returned by GET /galleries/photos and used by the
// homepage grid + lightbox. Avoids depending on the heavier MediaRecord type
// which includes admin-only fields the public API does not return.
export interface GalleryPhoto {
  id: number;
  url: string;
  thumb_url: string | null;
  alt_text: string | null;
  caption: string | null;
  width: number | null;
  height: number | null;
  sort_order: number;
}

export interface GalleryDetail extends Gallery {
  media: GalleryMedia[];
  seo: SeoMeta;
  prev_gallery: { slug: string; title: string } | null;
  next_gallery: { slug: string; title: string } | null;
}

// ─── Blog ─────────────────────────────────────────────────────────────────────

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

// ─── Service ──────────────────────────────────────────────────────────────────

export interface Service {
  id: number;
  title: string;
  slug: string;
  short_description: string | null;
  price_display: string | null;
  cover: MediaRecord | null;
  sort_order: number;
  status: 'draft' | 'published';
}

export interface ServiceDetail extends Service {
  description: string | null;
  seo: SeoMeta;
}

// ─── Testimonial ──────────────────────────────────────────────────────────────

export interface Testimonial {
  id: number;
  client_name: string;
  client_role: string | null;
  body: string;
  rating: number;
  portrait: MediaRecord | null;
  status: 'draft' | 'published';
  sort_order: number;
}

// ─── Hero Slide ───────────────────────────────────────────────────────────────

export interface HeroSlide {
  id: number;
  title: string;
  subtitle: string | null;
  background_image: MediaRecord | null;
  cta_label: string | null;
  cta_url: string | null;
  sort_order: number;
  status: 'draft' | 'published';
}

// ─── CMS Page ─────────────────────────────────────────────────────────────────

export interface PageSection {
  id: number;
  section_type: string;
  title: string | null;
  content: string | null;
  settings: Record<string, unknown>;
  sort_order: number;
}

export interface CmsPage {
  id: number;
  title: string;
  slug: string;
  template: string | null;
  sections: PageSection[];
  seo: SeoMeta;
}

// ─── Inquiry ──────────────────────────────────────────────────────────────────

export interface InquiryNote {
  id: number;
  note: string;
  created_by: { id: number; name: string };
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

// ─── Booking ──────────────────────────────────────────────────────────────────

export interface BookingRequest {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  event_type: string;
  event_date: string | null;
  event_location: string | null;
  message: string;
  status: 'new' | 'contacted' | 'quoted' | 'booked' | 'cancelled';
  ip_address: string | null;
  created_at: string;
}

// ─── Settings ─────────────────────────────────────────────────────────────────

export interface ThemeSettings {
  primary_color: string;
  secondary_color: string;
  accent_color: string;
  text_color: string;
  bg_color: string;
  display_font: string;
  body_font: string;
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
    map_embed_url?: string | null;
  };
  social: Record<string, string | null>;
  seo: {
    default_title: string | null;
    default_description: string | null;
  };
  theme?: ThemeSettings;
}

// ─── Dashboard ────────────────────────────────────────────────────────────────

export interface DashboardMetrics {
  pages:       { total: number; published: number };
  galleries:   { total: number; published: number };
  blog_posts:  { total: number; published: number };
  media:       { total: number };
  services:    { total: number; published: number };
  testimonials:{ total: number; published: number };
  hero_slides: { total: number; published: number };
  inquiries:   { total: number; new: number; in_progress: number };
  bookings:    { total: number; new: number; quoted: number };
}
