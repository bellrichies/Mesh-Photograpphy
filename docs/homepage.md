# Mesh Photography — Homepage Design & Architecture

> **Document Type:** UI/UX Design Specification  
> **Version:** 1.0  
> **Date:** 2026-06-14  
> **Route:** `/`  
> **Component:** `src/pages/public/HomePage.tsx`  
> **Design System:** `docs/blueprint.md` §21 · `docs/05-frontend-architecture.md` §2

---

## Table of Contents

1. [Design Philosophy](#1-design-philosophy)
2. [Information Architecture](#2-information-architecture)
3. [Full Page Layout](#3-full-page-layout)
4. [Section 1 — Navigation Bar](#4-section-1--navigation-bar)
5. [Section 2 — Hero Carousel](#5-section-2--hero-carousel)
6. [Section 3 — Brand Introduction](#6-section-3--brand-introduction)
7. [Section 4 — Featured Portfolio](#7-section-4--featured-portfolio)
8. [Section 5 — Services Teaser](#8-section-5--services-teaser)
9. [Section 6 — Testimonials](#9-section-6--testimonials)
10. [Section 7 — Blog Stories](#10-section-7--blog-stories)
11. [Section 8 — Final CTA](#11-section-8--final-cta)
12. [Section 9 — Footer](#12-section-9--footer)
13. [Responsive Design System](#13-responsive-design-system)
14. [Motion and Animation System](#14-motion-and-animation-system)
15. [Interaction States](#15-interaction-states)
16. [Accessibility Specification](#16-accessibility-specification)
17. [Component Architecture](#17-component-architecture)
18. [Data Contract](#18-data-contract)
19. [Performance Targets](#19-performance-targets)
20. [SEO Specification](#20-seo-specification)
21. [Implementation Checklist](#21-implementation-checklist)

---

## 1. Design Philosophy

The Mesh Photography homepage exists to do one thing above all else: make a prospective client feel that this photographer understands elegance. Every decision — the whitespace, the typographic scale, the color restraint — must communicate that the studio operates at the same level as the events it photographs.

### Core Principles

**Imagery is the product.** The homepage does not describe photography; it demonstrates it. Text serves as context for images, never the other way around. No section competes with the photography for visual attention.

**Restraint over richness.** The temptation is to fill every pixel. The discipline is to leave room for the work to breathe. Generous whitespace is not absence — it is confidence.

**One clear path per section.** Each section has one primary action. The hero asks the visitor to explore the portfolio. The portfolio section invites them deeper. The CTA section asks them to book. A visitor who scrolls the entire page should feel naturally guided toward making contact, not bombarded with competing calls to action.

**Performance is a design decision.** A slow-loading portfolio page is a bad first impression. Image loading strategy, skeleton states, and bundle size are as much part of the design as the color palette.

### Visual Hierarchy Principles

```
HIERARCHY LEVEL 1  — Full-bleed imagery (hero, gallery cards)
HIERARCHY LEVEL 2  — Display typography (Cormorant Garamond, large)
HIERARCHY LEVEL 3  — Section headings and primary CTAs
HIERARCHY LEVEL 4  — Body copy and secondary CTAs
HIERARCHY LEVEL 5  — Supporting labels, captions, metadata
```

Every element on the page maps to one of these five levels. Nothing competes with a higher level in the same visual field.

### Brand Tone on the Homepage

- **Confident, not boastful** — let the work speak
- **Warm, not chatty** — short copy, long whitespace
- **Premium, not exclusive** — approachable with elegance
- **Personal, not corporate** — a studio, not a brand agency

---

## 2. Information Architecture

### User Journeys

The homepage must serve three distinct users arriving with different levels of intent:

| User Type | Intent on Arrival | Primary Path |
|---|---|---|
| **Warm lead** | Ready to inquire; came from a referral | Hero CTA → Booking page |
| **Consideration phase** | Evaluating photographers; comparing options | Portfolio → Services → Testimonials → Contact |
| **Discovery** | Found via search or social; not ready to commit | Blog → Portfolio → CTA |

The page layout is designed so all three users find their natural entry point without friction. The hero captures all three; the sequential sections serve the consideration journey; the blog provides a soft re-engagement for discovery visitors.

### Content Hierarchy Map

```
┌────────────────────────────────────────────────────────────────┐
│  NAVIGATION — Identity + wayfinding (persistent)               │
├────────────────────────────────────────────────────────────────┤
│  HERO — First impression, brand statement, dual CTA            │
│  PURPOSE: Stop the scroll. Communicate quality in < 3 seconds. │
├────────────────────────────────────────────────────────────────┤
│  BRAND INTRODUCTION — Voice and positioning                    │
│  PURPOSE: Bridge from image impact to human connection.        │
├────────────────────────────────────────────────────────────────┤
│  FEATURED PORTFOLIO — Work showcase                            │
│  PURPOSE: Demonstrate range and consistent quality.            │
├────────────────────────────────────────────────────────────────┤
│  SERVICES TEASER — What is offered                             │
│  PURPOSE: Qualify the lead before they reach the contact form. │
├────────────────────────────────────────────────────────────────┤
│  TESTIMONIALS — Social proof                                   │
│  PURPOSE: Remove hesitation through peer validation.           │
├────────────────────────────────────────────────────────────────┤
│  BLOG/STORIES — Content marketing hook                         │
│  PURPOSE: Retain discovery visitors; demonstrate expertise.    │
├────────────────────────────────────────────────────────────────┤
│  FINAL CTA — Conversion point                                  │
│  PURPOSE: Convert the scrolled visitor into an inquiry.        │
├────────────────────────────────────────────────────────────────┤
│  FOOTER — Navigation, legal, social                            │
└────────────────────────────────────────────────────────────────┘
```

### Scroll Depth and Conversion Model

```
0%    → Hero: 100% of visitors see this
25%   → Portfolio: ~70% of visitors reach this
45%   → Services: ~55% of visitors reach this
60%   → Testimonials: ~40% of visitors reach this
75%   → Blog: ~30% of visitors reach this
90%   → CTA: ~25% of visitors reach this  ← Primary conversion target
100%  → Footer
```

The CTA section at 90% is intentionally positioned after the full trust-building sequence. A visitor who scrolls to the CTA has been exposed to the work, the offer, and the social proof — they are the most qualified lead on the page.

---

## 3. Full Page Layout

### Desktop Wireframe (1280px)

```
┌──────────────────────────────────────────────────────────────────────┐
│  [LOGO]          Portfolio  Services  Blog  About  Contact   [Book]  │  ← Navbar, h: 72px
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│                                                                      │
│         ┌────────────────────────────────────────────────┐           │
│         │                                                │           │
│         │              HERO IMAGE                        │           │  ← 100vh
│         │                                                │           │
│         │   ╔══════════════════════════╗                 │           │
│         │   ║  Display Heading         ║                 │           │
│         │   ║  Subtitle copy           ║                 │           │
│         │   ║  [View Portfolio] [Book] ║                 │           │
│         │   ╚══════════════════════════╝                 │           │
│         │                                                │           │
│         │  ● ● ● ● ●   [◄] [❚❚] [►]                      │            │
│         └────────────────────────────────────────────────┘           │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│              "Capturing the moments that matter most in grid."        │  ← Brand Intro, py-24
│           A line of warm brand copy, centered, no more than          │
│           two sentences. Space around it breathes.                   │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│  Portfolio                                     View all galleries →  │
│                                                                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐              │  ← Portfolio Grid
│  │  IMAGE   │  │  IMAGE   │  │  IMAGE   │  │  IMAGE   │              │
│  │          │  │          │  │          │  │          │              │
│  │          │  │          │  │          │  │          │              │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘              │
│  Title         Title         Title         Title                     │
│  Wedding       Portrait      Commercial    Elopement                 │
│                                                                      │
│  ┌──────────┐  ┌──────────┐                                          │
│  │  IMAGE   │  │  IMAGE   │                                          │
│  └──────────┘  └──────────┘                                          │
├──────────────────────────────────────────────────────────────────────┤
│  Services                                                            │
│                                                                      │
│  ┌────────────────────┐  ┌────────────────────┐  ┌──────────────┐    │  ← Services
│  │  [icon]  Wedding   │  │  [icon]  Portrait  │  │  [icon] Comm.│    │
│  │  From $2,500       │  │  From $800         │  │  On enquiry  │    │
│  │  Short desc...     │  │  Short desc...     │  │  Short desc  │    │
│  │  [Learn more]      │  │  [Learn more]      │  │  [Learn more]│    │
│  └────────────────────┘  └────────────────────┘  └──────────────┘    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐    │  ← Testimonials
│  │  "Quote text spanning the width with generous                │    │
│  │   leading and elegant serif type…"                           │    │
│  │                      — Client Name, Wedding 2026             │    │
│  └──────────────────────────────────────────────────────────────┘    │
│                      ● ○ ○   [◄] [►]                                 │
├──────────────────────────────────────────────────────────────────────┤
│  Stories                                        Read all posts →     │
│                                                                      │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐    │  ← Blog
│  │  [POST IMAGE]    │  │  [POST IMAGE]    │  │  [POST IMAGE]    │    │
│  │                  │  │                  │  │                  │    │
│  │  Category tag    │  │  Category tag    │  │  Category tag    │    │
│  │  Post Title      │  │  Post Title      │  │  Post Title      │    │
│  │  Excerpt copy    │  │  Excerpt copy    │  │  Excerpt copy    │    │
│  │  Jun 10, 2026    │  │  Jun 8, 2026     │  │  Jun 1, 2026     │    │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘    │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│            Let's Create Something Beautiful                          │  ← CTA, dark bg
│         Ready to capture your most meaningful moments?               │
│                                                                      │
│             [Book a Session]     [Get in Touch]                      │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│  [LOGO]    Portfolio  Services  Blog  About  Contact                 │  ← Footer
│  © 2026    [Instagram] [Facebook] [Pinterest]                        │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 4. Section 1 — Navigation Bar

### Purpose

Persistent identity anchor and wayfinding. The navbar is the one element present at every scroll position. Its design must communicate quality before any image loads.

### Visual Specification

| Property | Value |
|---|---|
| Height | 72px desktop / 64px mobile |
| Background | `bg-ivory/95` with `backdrop-blur-sm` |
| Position | `fixed top-0 left-0 right-0 z-50` |
| Border bottom | `border-b border-cream` (visible on scroll only) |
| Transition | Border and shadow fade in on scroll: `transition-all duration-300` |

### Layout — Desktop

```
[LOGO]                              Portfolio  Services  Blog  About  Contact    [Book a Session]
  └─ Cormorant Garamond 22px                  Inter 14px font-medium             Bronze CTA button
     text-charcoal                            text-charcoal
     tracking-wide                            hover:text-bronze
```

### Layout — Mobile

```
[LOGO]                                                              [☰]
```

Hamburger opens a full-screen overlay nav:
```
┌──────────────────────────────────────┐
│  [✕]                         [LOGO] │
│                                      │
│  Portfolio                           │
│  Services                            │
│  Blog                                │
│  About                               │
│  Contact                             │
│                                      │
│  [Book a Session]                    │
│                                      │
│  [Instagram] [Facebook] [Pinterest]  │
└──────────────────────────────────────┘
```

### Scroll Behavior

The navbar starts transparent (no border, no shadow) over the hero section where the hero image provides sufficient visual separation. As the user scrolls past 80px:

1. `border-b border-cream` fades in
2. `shadow-soft` appears
3. Background transitions from `bg-transparent` to `bg-ivory/95 backdrop-blur-sm`

This is implemented by attaching a scroll listener and toggling a CSS class:

```tsx
const [isScrolled, setIsScrolled] = useState(false);

useEffect(() => {
  const handler = () => setIsScrolled(window.scrollY > 80);
  window.addEventListener('scroll', handler, { passive: true });
  return () => window.removeEventListener('scroll', handler);
}, []);
```

### Active Link State

```tsx
// Current route gets: text-bronze font-semibold
// All others: text-charcoal hover:text-bronze
<NavLink
  to="/portfolio"
  className={({ isActive }) =>
    cn('text-sm transition-colors duration-150', isActive ? 'text-bronze font-semibold' : 'text-charcoal hover:text-bronze')
  }
>
  Portfolio
</NavLink>
```

### Mobile Overlay Animation

```css
/* Overlay slides in from the right */
.nav-overlay-enter  { transform: translateX(100%); }
.nav-overlay-active { transform: translateX(0); transition: transform 300ms ease-out; }
.nav-overlay-exit   { transform: translateX(100%); transition: transform 250ms ease-in; }
```

Use `aria-expanded` on the hamburger button and `role="dialog"` with `aria-label="Navigation menu"` on the overlay.

---

## 5. Section 2 — Hero Carousel

### Purpose

The most critical 3 seconds of the user experience. A prospective client forms their first impression before any text is read. The hero communicates: quality of light, emotional range of the photographer, and the calibre of events covered.

### Visual Specification

| Property | Value |
|---|---|
| Height | `100vh` (full viewport height, including behind navbar) |
| Min-height | `600px` (short viewports) |
| Image coverage | Full bleed, `object-cover object-center` |
| Overlay gradient | `bg-gradient-to-b from-black/20 via-black/10 to-black/50` |
| Overlay left-aligned variant | `bg-gradient-to-r from-black/60 via-black/30 to-transparent` |

### Layout Structure

```tsx
<section role="region" aria-label="Featured photography carousel" aria-roledescription="carousel">
  {/* Background image stack */}
  <div className="absolute inset-0 z-0">
    <img
      src={slides[current].background_image.url}
      alt=""                            {/* decorative — overlay text describes it */}
      className="w-full h-full object-cover object-center"
      loading="eager"
      fetchPriority="high"
      width={1920}
      height={1080}
    />
    <div className="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/55" />
  </div>

  {/* Overlay content — bottom-left on desktop, centered on mobile */}
  <div className="relative z-10 flex h-full items-end lg:items-center pb-20 lg:pb-0">
    <div className="max-w-7xl mx-auto px-6 lg:px-8 w-full">
      <div className="max-w-2xl">
        <p className="text-gold text-sm font-body tracking-[0.2em] uppercase mb-4 opacity-90">
          {slides[current].subtitle}
        </p>
        <h1 className="font-display text-5xl lg:text-7xl text-ivory font-light leading-tight mb-6">
          {slides[current].title}
        </h1>
        {slides[current].cta_label && (
          <div className="flex flex-wrap gap-4">
            <Link to={slides[current].cta_url}>
              <Button variant="primary" size="lg">{slides[current].cta_label}</Button>
            </Link>
            <Link to="/contact">
              <Button variant="ghost" size="lg" className="text-ivory border-ivory/50 hover:border-ivory">
                Get in Touch
              </Button>
            </Link>
          </div>
        )}
      </div>
    </div>
  </div>

  {/* Controls */}
  <HeroControls
    total={slides.length}
    current={current}
    isPaused={isPaused}
    onPrev={prev}
    onNext={next}
    onTogglePause={togglePause}
    onGoTo={goTo}
  />
</section>
```

### Auto-Rotation Logic

```typescript
const INTERVAL_MS = 6000;

useEffect(() => {
  if (isPaused || slides.length <= 1) return;
  const timer = setInterval(() => setCurrent((c) => (c + 1) % slides.length), INTERVAL_MS);
  return () => clearInterval(timer);
}, [isPaused, slides.length]);
```

**Pause conditions:**
- User clicks the pause button (explicit)
- User hovers over the slide (implicit — reduces motion anxiety)
- User focuses any slide control via keyboard (prevents disorientation)
- User's `prefers-reduced-motion` media query is `reduce`

```typescript
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const [isPaused, setIsPaused] = useState(prefersReducedMotion);
```

### Slide Transition

Cross-fade (opacity), not slide. A sliding transition fights with the already-strong horizontal composition of portrait photography.

```css
.slide-enter  { opacity: 0; }
.slide-active { opacity: 1; transition: opacity 800ms ease-in-out; }
.slide-exit   { opacity: 0; transition: opacity 800ms ease-in-out; }
```

### Controls Component (`HeroControls`)

Bottom-right quadrant of the hero:

```
●  ●  ●  ●  ●       [◄]  [❚❚]  [►]
Dot indicators      Prev, Pause, Next
```

All controls at minimum 44×44px touch target. Color:
- Active dot: `bg-ivory`
- Inactive dot: `bg-ivory/40 hover:bg-ivory/70`
- Arrow buttons: `text-ivory/70 hover:text-ivory`
- Pause icon: `text-ivory/70`

### Typography on Hero

| Element | Class | Size |
|---|---|---|
| Subtitle / label | `font-body tracking-[0.2em] uppercase text-gold text-sm` | 14px |
| Headline | `font-display font-light text-ivory leading-tight` | 56–72px |
| CTA Button | `font-body font-medium` via Button component | 16px |

The headline uses `font-light` (weight 300) to avoid competing visually with the photographic subject. Heavy type on a photographic background reads as clutter.

### Loading State

On initial page load, before the API data resolves:

```tsx
function HeroSkeleton() {
  return (
    <div className="h-screen w-full bg-charcoal-light animate-pulse flex items-end pb-20">
      <div className="max-w-7xl mx-auto px-6 w-full">
        <div className="h-3 w-28 bg-ivory/20 rounded mb-4" />
        <div className="h-16 w-2/3 bg-ivory/20 rounded mb-3" />
        <div className="h-16 w-1/2 bg-ivory/20 rounded mb-8" />
        <div className="flex gap-4">
          <div className="h-12 w-40 bg-ivory/20 rounded-lg" />
          <div className="h-12 w-32 bg-ivory/10 rounded-lg" />
        </div>
      </div>
    </div>
  );
}
```

The skeleton uses the expected dark background (charcoal-light) so the page doesn't flash from white to dark when the real image loads.

### Data Source

```typescript
GET /api/v1/hero-slides
// Returns: HeroSlide[] sorted by sort_order
// Loaded in: useHeroSlides() → staleTime: 5 minutes
```

---

## 6. Section 3 — Brand Introduction

### Purpose

A single, elegant breath between the visual impact of the hero and the product content of the portfolio. This section answers: *who is behind the camera, and why does it matter?*

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-ivory` |
| Padding | `py-20 lg:py-28` |
| Max width | `max-w-3xl mx-auto` |
| Alignment | Centered |

### Layout

```
                    [decorative rule — thin, gold, 40px wide, centered]

        "Capturing the quiet moments between the moments."

        We are a San Francisco-based photography studio specializing
        in weddings, portraits, and commercial work. Every session
        is approached with patience, intention, and an eye for
        the light that makes everything feel true.

                           [About the Studio →]

                    [decorative rule — thin, gold, 40px wide, centered]
```

### Typography

```tsx
<section className="py-20 lg:py-28 bg-ivory">
  <div className="max-w-3xl mx-auto px-6 text-center">

    {/* Decorative divider */}
    <div className="flex justify-center mb-10">
      <div className="h-px w-10 bg-gold" />
    </div>

    {/* Tagline — from site settings */}
    <p className="font-display text-3xl lg:text-4xl text-charcoal font-light leading-snug mb-6">
      {settings.site.tagline}
    </p>

    {/* Brand copy — from reusable block 'homepage-intro' */}
    <p className="font-body text-base lg:text-lg text-taupe leading-relaxed max-w-xl mx-auto mb-8">
      {introBlock?.content}
    </p>

    {/* Soft CTA */}
    <Link
      to="/about"
      className="inline-flex items-center gap-2 text-bronze font-body text-sm font-medium hover:text-bronze-dark transition-colors"
    >
      About the Studio
      <ArrowRight className="h-4 w-4" />
    </Link>

    {/* Decorative divider */}
    <div className="flex justify-center mt-10">
      <div className="h-px w-10 bg-gold" />
    </div>

  </div>
</section>
```

### Content Source

The tagline comes from `settings.site.tagline`. The body copy comes from a reusable content block with key `homepage-intro`, managed via the admin panel. The CTA link (`/about`) is hardcoded but the label comes from the reusable block settings field.

This allows the studio owner to change both the headline and the copy without a code deployment.

### Copy Guidelines (for Admin)

- **Tagline:** 8–12 words. One sentence. No exclamation marks. Poetic over promotional.
- **Body:** 2 sentences maximum. First sentence: what you do and where. Second sentence: how you approach it.
- **CTA Label:** 3–5 words. No "Click here."

---

## 7. Section 4 — Featured Portfolio

### Purpose

The portfolio section is where the homepage proves its headline. Six gallery cards show the breadth and consistency of the work. Each card is an invitation to explore a full gallery.

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-ivory-warm` |
| Padding | `py-16 lg:py-24` |
| Grid | `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` (desktop) |
| Card aspect ratio | `aspect-[4/5]` (portrait orientation — mirrors the work) |
| Card gap | `gap-4 lg:gap-6` |
| Max cards shown | 6 |

### Section Header

```tsx
<div className="flex items-end justify-between mb-8 lg:mb-10">
  <div>
    <span className="font-body text-xs tracking-[0.15em] uppercase text-taupe block mb-2">
      Our Work
    </span>
    <h2 className="font-display text-3xl lg:text-4xl text-charcoal">Portfolio</h2>
  </div>
  <Link
    to="/portfolio"
    className="text-bronze text-sm font-medium font-body hover:text-bronze-dark flex items-center gap-1.5 transition-colors"
  >
    View all galleries
    <ArrowRight className="h-4 w-4" />
  </Link>
</div>
```

### Gallery Card Specification

The `GalleryCard` component is the most important standalone visual unit on the homepage.

```
┌──────────────────────────────────────┐
│                                      │
│                                      │
│         GALLERY COVER IMAGE          │  ← aspect-[4/5], object-cover
│           object-center              │
│                                      │
│  ┌───────────────────────────────┐   │  ← Hover overlay: appears on hover
│  │  [CATEGORY BADGE]             │   │     bg-gradient-to-t from-charcoal/80
│  │                               │   │     opacity-0 → opacity-100
│  │  Gallery Title                │   │
│  │  [View Gallery →]             │   │
│  └───────────────────────────────┘   │
│                                      │
└──────────────────────────────────────┘
  Gallery Title (below card, always visible)
  Category · 28 images
```

```tsx
function GalleryCard({ gallery }: { gallery: Gallery }) {
  return (
    <Link
      to={`/portfolio/${gallery.slug}`}
      className="group block rounded-2xl overflow-hidden focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2"
      aria-label={`View ${gallery.title} gallery, ${gallery.media_count} images`}
    >
      {/* Image */}
      <div className="relative aspect-[4/5] overflow-hidden bg-cream">
        {gallery.cover ? (
          <img
            src={gallery.cover.thumb_url ?? gallery.cover.url}
            alt={gallery.cover.alt_text ?? gallery.title}
            className="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105"
            loading="lazy"
            width={480}
            height={600}
          />
        ) : (
          <div className="w-full h-full bg-cream flex items-center justify-center">
            <ImageIcon className="h-12 w-12 text-taupe/30" />
          </div>
        )}

        {/* Hover Overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-charcoal/75 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 ease-in-out flex items-end p-5">
          <div className="text-ivory">
            <span className="font-body text-sm font-medium flex items-center gap-1.5">
              View Gallery
              <ArrowRight className="h-4 w-4" />
            </span>
          </div>
        </div>
      </div>

      {/* Card footer — always visible */}
      <div className="pt-3 pb-1">
        <h3 className="font-display text-lg text-charcoal group-hover:text-bronze transition-colors duration-200 leading-snug">
          {gallery.title}
        </h3>
        <p className="font-body text-xs text-taupe mt-0.5">
          {gallery.categories.map((c) => c.name).join(' · ')}
          {gallery.media_count > 0 && ` · ${gallery.media_count} images`}
        </p>
      </div>
    </Link>
  );
}
```

### Grid Layout — Asymmetric Option (Enhanced)

For a more editorial feel, the first card can span two columns on desktop:

```
Mobile:          Tablet:               Desktop:
[  1  ]          [  1  ] [  2  ]       [ 1 (2-col) ] [  2  ]
[  2  ]          [  3  ] [  4  ]       [    3    ]   [  4  ]
[  3  ]          [  5  ] [  6  ]       [  5  ]  [  6  ]
[  4  ]
[  5  ]
[  6  ]
```

```tsx
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
  {galleries.map((gallery, index) => (
    <div key={gallery.id} className={index === 0 ? 'lg:col-span-2 lg:row-span-1' : ''}>
      <GalleryCard gallery={gallery} />
    </div>
  ))}
</div>
```

This option works when the first featured gallery has a landscape-orientation cover image. The admin controls `is_featured` and `sort_order`, so this arrangement is data-driven.

### Loading State

```tsx
function PortfolioSectionSkeleton() {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
      {Array.from({ length: 6 }).map((_, i) => (
        <div key={i} className={cn('rounded-2xl overflow-hidden', i === 0 && 'lg:col-span-2')}>
          <div className="aspect-[4/5] bg-cream animate-pulse" />
          <div className="pt-3 space-y-1.5">
            <div className="h-5 w-3/4 bg-cream animate-pulse rounded" />
            <div className="h-3 w-1/2 bg-cream animate-pulse rounded" />
          </div>
        </div>
      ))}
    </div>
  );
}
```

### Data Source

```typescript
GET /api/v1/galleries?featured=true&per_page=6
// Query key: ['galleries', 'public', { featured: 'true', per_page: '6' }]
// staleTime: 5 minutes
```

---

## 8. Section 5 — Services Teaser

### Purpose

Qualify the prospective client. A visitor who resonates with the work needs to know immediately: is this photographer available for my event type, and roughly what does it cost? The services section answers both without requiring a click through to the services page.

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-sand` (alternates from portfolio `bg-ivory-warm`) |
| Padding | `py-16 lg:py-24` |
| Grid | `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` |
| Card style | Minimal: no heavy borders, no shadows; defined by whitespace |

### Section Header

```tsx
<div className="text-center mb-12 lg:mb-16">
  <span className="font-body text-xs tracking-[0.15em] uppercase text-taupe block mb-2">
    What We Offer
  </span>
  <h2 className="font-display text-3xl lg:text-4xl text-charcoal">Services</h2>
</div>
```

### Service Card Specification

Each service card is deliberately minimal — no card container, no box shadow. The service stands on its content, separated from siblings by grid gutter alone.

```
   [Cover Image — 16:9, rounded-xl]

   Wedding Photography
   A horizontal rule — 24px, bronze, left-aligned

   From $2,500

   Full-day coverage of your wedding with...
   a two-sentence maximum excerpt.

   [Explore this service →]
```

```tsx
function ServiceCard({ service }: { service: Service }) {
  return (
    <article className="group">
      {/* Cover image */}
      {service.cover && (
        <div className="aspect-video rounded-xl overflow-hidden mb-5 bg-cream">
          <img
            src={service.cover.thumb_url ?? service.cover.url}
            alt={service.cover.alt_text ?? service.title}
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
            loading="lazy"
            width={480}
            height={270}
          />
        </div>
      )}

      {/* Content */}
      <h3 className="font-display text-xl text-charcoal mb-2">{service.title}</h3>

      {/* Bronze rule — visual separator with brand accent */}
      <div className="w-6 h-px bg-bronze mb-3" />

      {service.price_display && (
        <p className="font-body text-sm font-medium text-bronze mb-3">{service.price_display}</p>
      )}

      {service.short_description && (
        <p className="font-body text-sm text-taupe leading-relaxed mb-4 line-clamp-3">
          {service.short_description}
        </p>
      )}

      <Link
        to={`/services/${service.slug}`}
        className="inline-flex items-center gap-1.5 font-body text-sm font-medium text-bronze hover:text-bronze-dark transition-colors"
      >
        Explore this service
        <ArrowRight className="h-3.5 w-3.5" />
      </Link>
    </article>
  );
}
```

### Section Footer — Book CTA

Below the service cards, a centered secondary CTA bridges into the booking flow:

```tsx
<div className="text-center mt-14">
  <p className="font-body text-sm text-taupe mb-4">Not sure which package is right for you?</p>
  <Link to="/contact">
    <Button variant="secondary">Let's Talk</Button>
  </Link>
</div>
```

### Data Source

```typescript
GET /api/v1/services  // returns all published services, sorted by sort_order
// Limit display to first 3 on homepage (slice client-side or add ?per_page=3)
// staleTime: 5 minutes
```

---

## 9. Section 6 — Testimonials

### Purpose

Remove the final emotional hesitation. A prospective client who likes the work and the services still needs permission from a peer to make contact. One powerful testimonial, presented with confidence, is more effective than six mediocre ones presented apologetically.

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-ivory` (returns to base after sand services section) |
| Padding | `py-16 lg:py-24` |
| Layout | Single testimonial displayed at a time, centered |
| Quote style | Large Cormorant Garamond italic, generous line height |

### Layout

```tsx
<section className="py-16 lg:py-24 bg-ivory overflow-hidden">
  <div className="max-w-4xl mx-auto px-6">

    {/* Section label */}
    <div className="text-center mb-12">
      <span className="font-body text-xs tracking-[0.15em] uppercase text-taupe">
        Client Stories
      </span>
    </div>

    {/* Quote mark */}
    <div className="text-center mb-6">
      <span className="font-display text-8xl text-gold/40 leading-none select-none">"</span>
    </div>

    {/* Quote body */}
    <blockquote className="text-center">
      <p className="font-display text-2xl lg:text-3xl text-charcoal font-light italic leading-relaxed mb-8">
        {currentTestimonial.body}
      </p>

      {/* Attribution */}
      <footer className="flex flex-col items-center gap-3">
        {currentTestimonial.portrait && (
          <img
            src={currentTestimonial.portrait.thumb_url ?? currentTestimonial.portrait.url}
            alt={currentTestimonial.portrait.alt_text ?? currentTestimonial.client_name}
            className="w-14 h-14 rounded-full object-cover border-2 border-cream"
            loading="lazy"
            width={56}
            height={56}
          />
        )}
        <div className="text-center">
          <cite className="font-body font-semibold text-charcoal not-italic text-sm">
            {currentTestimonial.client_name}
          </cite>
          {currentTestimonial.client_role && (
            <p className="font-body text-xs text-taupe mt-0.5">
              {currentTestimonial.client_role}
            </p>
          )}
          {currentTestimonial.rating && (
            <StarRating rating={currentTestimonial.rating} className="mt-1.5" />
          )}
        </div>
      </footer>
    </blockquote>

    {/* Navigation dots */}
    {testimonials.length > 1 && (
      <div className="flex justify-center items-center gap-3 mt-10">
        <button onClick={prev} aria-label="Previous testimonial" className="p-2 text-taupe hover:text-bronze">
          <ChevronLeft className="h-5 w-5" />
        </button>
        <div className="flex gap-2">
          {testimonials.map((_, i) => (
            <button
              key={i}
              onClick={() => setCurrent(i)}
              aria-label={`Go to testimonial ${i + 1}`}
              aria-current={i === current}
              className={cn('w-1.5 h-1.5 rounded-full transition-all', i === current ? 'bg-bronze w-4' : 'bg-cream hover:bg-taupe')}
            />
          ))}
        </div>
        <button onClick={next} aria-label="Next testimonial" className="p-2 text-taupe hover:text-bronze">
          <ChevronRight className="h-5 w-5" />
        </button>
      </div>
    )}

  </div>
</section>
```

### Star Rating Component

```tsx
function StarRating({ rating, className }: { rating: number; className?: string }) {
  return (
    <div className={cn('flex gap-0.5 justify-center', className)} aria-label={`Rating: ${rating} out of 5 stars`}>
      {Array.from({ length: 5 }).map((_, i) => (
        <Star
          key={i}
          className={cn('h-3 w-3', i < rating ? 'text-gold fill-gold' : 'text-cream fill-cream')}
          aria-hidden="true"
        />
      ))}
    </div>
  );
}
```

### Testimonial Transition

The text cross-fades when navigating. Avoid slide animations for text — they are difficult to read mid-transition and add no value for testimonials.

```tsx
<AnimatePresence mode="wait">
  <motion.blockquote
    key={currentTestimonial.id}
    initial={{ opacity: 0, y: 8 }}
    animate={{ opacity: 1, y: 0 }}
    exit={{ opacity: 0, y: -8 }}
    transition={{ duration: 0.35, ease: 'easeInOut' }}
  >
    {/* testimonial content */}
  </motion.blockquote>
</AnimatePresence>
```

If Framer Motion is not in the dependency list, implement with CSS transitions and a key-swap pattern in React.

### Data Source

```typescript
GET /api/v1/testimonials?limit=3
// Display max 3 testimonials on homepage
// If only 1, hide navigation controls
// staleTime: 5 minutes
```

---

## 10. Section 7 — Blog Stories

### Purpose

Retain discovery visitors and demonstrate expertise. A visitor who arrived via search and is not yet ready to book can find value in editorial content, building a relationship with the studio over time.

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-parchment` |
| Padding | `py-16 lg:py-24` |
| Grid | `grid-cols-1 md:grid-cols-3` |
| Image aspect ratio | `aspect-video` (16:9) |
| Max posts shown | 3 |

### Section Header

```tsx
<div className="flex items-end justify-between mb-10">
  <div>
    <span className="font-body text-xs tracking-[0.15em] uppercase text-taupe block mb-2">
      From the Studio
    </span>
    <h2 className="font-display text-3xl lg:text-4xl text-charcoal">Stories</h2>
  </div>
  <Link to="/blog" className="text-bronze text-sm font-medium font-body hover:text-bronze-dark flex items-center gap-1.5">
    Read all posts <ArrowRight className="h-4 w-4" />
  </Link>
</div>
```

### Blog Post Card Specification

```tsx
function BlogPostCard({ post }: { post: BlogPost }) {
  return (
    <article className="group">
      {/* Cover image */}
      <Link to={`/blog/${post.slug}`} tabIndex={-1} aria-hidden="true">
        <div className="aspect-video rounded-xl overflow-hidden mb-4 bg-cream">
          {post.cover ? (
            <img
              src={post.cover.thumb_url ?? post.cover.url}
              alt={post.cover.alt_text ?? post.title}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
              loading="lazy"
              width={480}
              height={270}
            />
          ) : (
            <div className="w-full h-full bg-cream" />
          )}
        </div>
      </Link>

      {/* Category badge */}
      {post.categories.length > 0 && (
        <Link
          to={`/blog/category/${post.categories[0].slug}`}
          className="inline-block font-body text-xs font-medium tracking-wide uppercase text-bronze hover:text-bronze-dark mb-2 transition-colors"
        >
          {post.categories[0].name}
        </Link>
      )}

      {/* Title */}
      <h3 className="font-display text-xl text-charcoal leading-snug mb-2">
        <Link
          to={`/blog/${post.slug}`}
          className="hover:text-bronze transition-colors duration-200 focus:outline-none focus:text-bronze"
        >
          {post.title}
        </Link>
      </h3>

      {/* Excerpt */}
      {post.excerpt && (
        <p className="font-body text-sm text-taupe leading-relaxed line-clamp-2 mb-3">
          {post.excerpt}
        </p>
      )}

      {/* Date */}
      <time
        dateTime={post.published_at ?? ''}
        className="font-body text-xs text-taupe/70"
      >
        {formatDate(post.published_at)}
      </time>
    </article>
  );
}
```

### Empty State (no blog posts published)

The entire blog section is conditionally rendered. If the API returns 0 posts, the section does not render at all — an empty blog section is worse than no blog section.

```tsx
{(blogData?.items?.length ?? 0) > 0 && (
  <BlogStoriesSection posts={blogData.items} />
)}
```

### Data Source

```typescript
GET /api/v1/blog/posts?per_page=3
// Returns 3 most recently published posts
// staleTime: 5 minutes
```

---

## 11. Section 8 — Final CTA

### Purpose

This is the primary conversion section of the homepage. A visitor who has scrolled from hero to CTA has passed through the complete trust-building sequence. The ask must be clear, warm, and free of friction.

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-espresso` (deep, warm near-black) |
| Padding | `py-24 lg:py-32` |
| Text alignment | Centered |
| Texture (optional) | Subtle grain overlay via CSS `noise` pattern for depth |

### Layout

```tsx
<section className="py-24 lg:py-32 bg-espresso relative overflow-hidden">
  {/* Optional: subtle texture overlay */}
  <div className="absolute inset-0 opacity-[0.03] bg-[url('/assets/noise.png')] bg-repeat" aria-hidden="true" />

  <div className="relative z-10 max-w-3xl mx-auto px-6 text-center">

    {/* Decorative element */}
    <div className="flex justify-center mb-8">
      <div className="h-px w-10 bg-gold" />
    </div>

    <h2 className="font-display text-4xl lg:text-5xl text-ivory font-light leading-tight mb-5">
      Let's Create Something
      <span className="block text-gold italic">Beautiful Together</span>
    </h2>

    <p className="font-body text-base text-ivory/60 leading-relaxed mb-10 max-w-lg mx-auto">
      Every session begins with a conversation. Tell us about your vision
      and we'll tell you how we can make it extraordinary.
    </p>

    <div className="flex flex-col sm:flex-row gap-4 justify-center">
      <Link to="/booking">
        <Button
          variant="primary"
          size="lg"
          className="w-full sm:w-auto min-w-[160px]"
        >
          Book a Session
        </Button>
      </Link>
      <Link to="/contact">
        <Button
          variant="ghost"
          size="lg"
          className="w-full sm:w-auto min-w-[160px] text-ivory/80 hover:text-ivory"
        >
          Get in Touch
        </Button>
      </Link>
    </div>

    {/* Trust signals below buttons */}
    <div className="flex flex-wrap justify-center gap-6 mt-10">
      <span className="font-body text-xs text-ivory/30 flex items-center gap-1.5">
        <CheckCircle className="h-3.5 w-3.5" /> Free consultation
      </span>
      <span className="font-body text-xs text-ivory/30 flex items-center gap-1.5">
        <CheckCircle className="h-3.5 w-3.5" /> Response within 24 hours
      </span>
      <span className="font-body text-xs text-ivory/30 flex items-center gap-1.5">
        <CheckCircle className="h-3.5 w-3.5" /> No commitment required
      </span>
    </div>

  </div>
</section>
```

### CTA Button Hierarchy

- **Primary:** `Book a Session` — bronze background, direct to `/booking`
- **Secondary:** `Get in Touch` — text-only ghost style, to `/contact`

The primary action is booking. The secondary action exists for visitors who want to ask questions first. Never offer more than two paths from a CTA section.

### Trust Signal Copy Guidelines

The three trust signals below the buttons address the three most common hesitations:
1. "Will I be charged just for asking?" → Free consultation
2. "Will I be ignored?" → Response within 24 hours
3. "Am I committing to something?" → No commitment required

These are always visible. They are not admin-managed. They require a code change to update.

---

## 12. Section 9 — Footer

### Visual Specification

| Property | Value |
|---|---|
| Background | `bg-ink` (richest near-black, distinct from espresso CTA) |
| Padding | `py-12 lg:py-16` |
| Border top | `border-t border-charcoal-light` |

### Layout — Desktop

```
┌──────────────────────────────────────────────────────────────────────┐
│  Mesh Photography           Portfolio    Services    Contact          │
│  San Francisco Studio       Blog         About       Booking         │
│                                                                      │
│  hello@meshphoto.com        [Instagram] [Facebook] [Pinterest]       │
│  +1 555 000 1234                                                      │
│                                                                      │
│  ─────────────────────────────────────────────────────────────────   │
│  © 2026 Mesh Photography     Privacy Policy    Terms of Service      │
└──────────────────────────────────────────────────────────────────────┘
```

```tsx
<footer className="bg-ink border-t border-charcoal-light">
  <div className="max-w-7xl mx-auto px-6 lg:px-8 py-12 lg:py-16">
    <div className="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">

      {/* Brand column */}
      <div>
        <span className="font-display text-2xl text-ivory block mb-3">Mesh Photography</span>
        <p className="font-body text-xs text-taupe/60 leading-relaxed mb-4">
          {settings?.contact?.address}
        </p>
        {settings?.contact?.email && (
          <a href={`mailto:${settings.contact.email}`}
             className="font-body text-xs text-taupe/60 hover:text-ivory transition-colors block mb-1">
            {settings.contact.email}
          </a>
        )}
        {settings?.contact?.phone && (
          <a href={`tel:${settings.contact.phone}`}
             className="font-body text-xs text-taupe/60 hover:text-ivory transition-colors block">
            {settings.contact.phone}
          </a>
        )}
      </div>

      {/* Navigation columns */}
      <div className="grid grid-cols-2 gap-8">
        <div>
          <h4 className="font-body text-xs uppercase tracking-[0.12em] text-taupe mb-4">Explore</h4>
          <nav aria-label="Footer navigation - explore">
            {['Portfolio', 'Services', 'Blog', 'About'].map((item) => (
              <Link key={item} to={`/${item.toLowerCase()}`}
                    className="block font-body text-sm text-ivory/50 hover:text-ivory transition-colors mb-2">
                {item}
              </Link>
            ))}
          </nav>
        </div>
        <div>
          <h4 className="font-body text-xs uppercase tracking-[0.12em] text-taupe mb-4">Studio</h4>
          <nav aria-label="Footer navigation - studio">
            {[['Contact', '/contact'], ['Booking', '/booking'], ['Privacy', '/privacy'], ['Terms', '/terms']].map(([label, href]) => (
              <Link key={href} to={href}
                    className="block font-body text-sm text-ivory/50 hover:text-ivory transition-colors mb-2">
                {label}
              </Link>
            ))}
          </nav>
        </div>
      </div>

      {/* Social column */}
      <div>
        <h4 className="font-body text-xs uppercase tracking-[0.12em] text-taupe mb-4">Follow Along</h4>
        <div className="flex gap-4">
          {settings?.social?.instagram && (
            <a href={settings.social.instagram} target="_blank" rel="noopener noreferrer"
               aria-label="Instagram" className="text-ivory/40 hover:text-bronze transition-colors">
              <InstagramIcon className="h-5 w-5" />
            </a>
          )}
          {/* Facebook, Pinterest */}
        </div>
      </div>

    </div>

    {/* Bottom bar */}
    <div className="border-t border-charcoal-light pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
      <p className="font-body text-xs text-taupe/40">
        © {new Date().getFullYear()} Mesh Photography. All rights reserved.
      </p>
      <div className="flex gap-6">
        <Link to="/privacy" className="font-body text-xs text-taupe/40 hover:text-ivory transition-colors">
          Privacy Policy
        </Link>
        <Link to="/terms" className="font-body text-xs text-taupe/40 hover:text-ivory transition-colors">
          Terms of Service
        </Link>
      </div>
    </div>
  </div>
</footer>
```

---

## 13. Responsive Design System

### Breakpoint Reference

| Prefix | Width | Target | Key Changes |
|---|---|---|---|
| Default | 0–639px | Mobile portrait | Single column, stacked CTAs, hamburger nav |
| `sm:` | 640px+ | Mobile landscape | 2-column gallery grid begins |
| `md:` | 768px+ | Tablet | 2-column services, 3-column blog |
| `lg:` | 1024px+ | Desktop | Full 3-column portfolio, horizontal nav |
| `xl:` | 1280px+ | Wide desktop | Max-width container active |

### Mobile-Specific Decisions

**Navigation:** Hamburger → full-screen overlay. All links visible at touch-friendly size.

**Hero:** Overlay content moves from bottom-left (desktop) to bottom-center (mobile). Headline reduces from 72px to 40px. CTA buttons stack vertically with full width.

**Portfolio grid:** 1 column on mobile. The asymmetric 2-column option collapses to single-column gracefully.

**Testimonials:** No carousel navigation on mobile — swipe gesture handles next/prev via `touchstart`/`touchend` event tracking.

**Services:** 1 column on mobile. Cover image at `aspect-video`, then text below.

**CTA section:** Both buttons full-width and stacked.

### Touch Targets

All interactive elements meet the 44×44px minimum touch target. Where the visual element is smaller (e.g., a 12px icon), add invisible padding:

```tsx
<button className="p-2.5 text-taupe hover:text-bronze" aria-label="Next testimonial">
  {/* Icon is 20px, total touch target is 20 + 10 + 10 = 40px → round up with p-3 */}
  <ChevronRight className="h-5 w-5" />
</button>
```

### Spacing Scale for Responsive Sections

```tsx
// Section vertical spacing — consistent across the page
const sectionPadding = 'py-16 lg:py-24';

// Container — consistent max-width and horizontal padding
const container = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';

// Section header spacing below
const sectionHeaderSpacing = 'mb-8 lg:mb-12';
```

---

## 14. Motion and Animation System

### Design Principle

Motion on the Mesh Photography homepage should feel like the turning of pages in an editorial magazine — deliberate, smooth, and never distracting from the imagery.

### Scroll-Triggered Entry Animations

Content sections use a gentle fade-up on scroll entry. This is implemented with the `IntersectionObserver` API, not scroll event listeners (performance).

```typescript
// src/hooks/useScrollReveal.ts
export function useScrollReveal(threshold = 0.15) {
  const ref = useRef<HTMLElement>(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => { if (entry.isIntersecting) setIsVisible(true); },
      { threshold }
    );
    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, [threshold]);

  return { ref, isVisible };
}
```

```tsx
// Usage in a section
function PortfolioSection({ galleries }: { galleries: Gallery[] }) {
  const { ref, isVisible } = useScrollReveal();
  return (
    <section
      ref={ref}
      className={cn('transition-all duration-700 ease-out', isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6')}
    >
      {/* content */}
    </section>
  );
}
```

### Animation Budget

| Element | Duration | Easing | Notes |
|---|---|---|---|
| Section reveal | 700ms | `ease-out` | One-shot on scroll entry |
| Gallery card hover overlay | 400ms | `ease-in-out` | On hover |
| Gallery card image scale | 700ms | `ease-out` | Slow zoom on hover |
| Hero slide transition | 800ms | `ease-in-out` | Cross-fade between slides |
| Testimonial transition | 350ms | `ease-in-out` | Fade + tiny Y shift |
| Navbar border appear | 300ms | `ease-in-out` | On scroll past 80px |
| Mobile nav overlay | 300ms open / 250ms close | `ease-out` / `ease-in` | Slide from right |
| Button hover | 150ms | — | Color transition via Tailwind |
| Nav link hover | 150ms | — | Color transition |

### `prefers-reduced-motion` Compliance

All animations respect the `prefers-reduced-motion: reduce` media query:

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

In JavaScript, disable the hero auto-rotation and gallery card scale effects:

```typescript
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
```

---

## 15. Interaction States

Every interactive element on the homepage must have four defined states. Undetermined or missing states create inconsistent UX and fail accessibility audits.

### State Matrix

| Element | Default | Hover | Focus | Active / Current |
|---|---|---|---|---|
| Nav link | `text-charcoal` | `text-bronze` | `outline-2 outline-bronze` | `text-bronze font-semibold` |
| Primary button | `bg-bronze text-ivory` | `bg-bronze-light` | `ring-2 ring-bronze ring-offset-2` | `bg-bronze-dark` scale-[0.98] |
| Ghost button | `text-bronze border-bronze` | `bg-bronze text-ivory` | `ring-2 ring-bronze` | `bg-bronze-dark` |
| Gallery card | Natural | Image scale-105, overlay opacity-100 | `ring-2 ring-bronze ring-offset-2` | — |
| Blog post title | `text-charcoal` | `text-bronze` | `text-bronze` | — |
| Carousel dot | `bg-cream` | `bg-taupe` | outline visible | `bg-bronze w-4` (widened) |
| Footer link | `text-ivory/50` | `text-ivory` | `outline-2 outline-ivory` | — |
| Social icon | `text-ivory/40` | `text-bronze` | outline visible | — |

### Focus Styles

Never remove the default focus ring. Override it with a styled version:

```css
/* Overrides browser default for a branded focus indicator */
:focus-visible {
  outline: 2px solid #9a7b5c;  /* bronze */
  outline-offset: 2px;
  border-radius: 4px;
}
```

For dark backgrounds (CTA section, footer), override to ivory:

```tsx
<Link className="... focus:outline-none focus:ring-2 focus:ring-ivory focus:ring-offset-2 focus:ring-offset-espresso">
```

---

## 16. Accessibility Specification

### WCAG 2.1 AA Targets

| Criterion | Requirement | Implementation |
|---|---|---|
| 1.1.1 Non-text Content | All images have `alt` text | Cover images: descriptive alt; hero bg images: `alt=""` (decorative) |
| 1.3.1 Info and Relationships | Semantic HTML structure | `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`, `<h1>`–`<h3>` in correct order |
| 1.4.3 Contrast (Minimum) | 4.5:1 for normal text | Verified: charcoal on ivory (15:1), bronze on ivory (3.8:1 → use bronze-dark for body text) |
| 1.4.4 Resize Text | No information loss at 200% zoom | Tested at browser zoom 200%; no overflow clipping |
| 2.1.1 Keyboard | All functionality keyboard accessible | Carousel: arrow keys; nav: tab order; modal: Esc to close |
| 2.2.2 Pause/Stop | Auto-playing carousels can be paused | Hero: pause button; testimonials: arrow key stops auto |
| 2.4.1 Bypass Blocks | Skip navigation link | `Skip to content` — first element, visible on focus |
| 2.4.2 Page Titled | Descriptive `<title>` | `Mesh Photography | Professional Photography Studio` |
| 2.4.6 Headings and Labels | Clear, descriptive headings | h1: page title; h2: section titles; h3: card titles |
| 3.2.1 On Focus | No context change on focus | Verified: focus does not trigger navigation or scroll |
| 4.1.2 Name/Role/Value | ARIA roles and labels | Carousel: `aria-label`, `aria-roledescription`, `aria-current` |

### Heading Hierarchy on the Homepage

The homepage is a single document. The heading hierarchy must be correct:

```
<h1> (implicit — site identity, rendered by PageMeta)
  <h2> Portfolio         ← Section heading
    <h3> Gallery Title   ← Card heading
  <h2> Services
    <h3> Service Title
  <h2> Client Stories
  <h2> Stories
    <h3> Blog Post Title
```

There is one `<h1>` on the page: the page title rendered in `<title>` via React Helmet. Section headings are `<h2>`. Card titles within sections are `<h3>`.

**Do not use heading tags for visual styling.** If you need large display text that is not a structural heading, use `<p>` with the `font-display` class.

### Carousel Accessibility

```tsx
<section
  role="region"
  aria-label="Featured photography"
  aria-roledescription="carousel"
>
  {slides.map((slide, i) => (
    <div
      key={slide.id}
      role="group"
      aria-roledescription="slide"
      aria-label={`Slide ${i + 1} of ${slides.length}: ${slide.title}`}
      aria-hidden={i !== current}
      className={i === current ? 'block' : 'hidden'}
    >
      {/* slide content */}
    </div>
  ))}

  <div aria-live="polite" className="sr-only">
    {slides[current]?.title}
  </div>
</section>
```

The `aria-live="polite"` region announces the current slide title to screen readers without interrupting any existing announcements.

---

## 17. Component Architecture

### Component Tree

```
HomePage
├── PageMeta                          (React Helmet: title, description, OG tags)
├── HeroCarousel
│   ├── HeroSlide (×N, lazy rendered)
│   │   ├── img (bg, loading="eager" on first)
│   │   ├── div (gradient overlay)
│   │   └── HeroSlideContent
│   │       ├── p (subtitle label)
│   │       ├── h1 (headline)
│   │       └── div (CTA buttons — Link + Button)
│   └── HeroControls
│       ├── button (prev)
│       ├── button[] (dot indicators)
│       ├── button (pause/play)
│       └── button (next)
├── BrandIntroSection
│   └── Link (About CTA)
├── FeaturedPortfolioSection
│   ├── div (section header — h2 + View all link)
│   ├── GalleryGrid
│   │   └── GalleryCard (×6)
│   │       ├── Link (wraps card)
│   │       ├── div (image container + hover overlay)
│   │       └── div (card footer — h3 + meta)
│   └── GalleryGridSkeleton (loading state)
├── ServicesTeaserSection
│   ├── div (section header — h2)
│   ├── ServiceCard (×3)
│   │   ├── div (image container)
│   │   ├── h3 (service title)
│   │   ├── div (bronze rule)
│   │   ├── p (price display)
│   │   ├── p (short description)
│   │   └── Link (Explore CTA)
│   └── div (secondary CTA — "Let's Talk")
├── TestimonialsSection
│   ├── blockquote (current testimonial)
│   │   ├── p (quote body)
│   │   └── footer
│   │       ├── img (portrait)
│   │       ├── cite (client name)
│   │       ├── p (client role)
│   │       └── StarRating
│   └── div (navigation — prev/dots/next)
├── BlogStoriesSection               (conditionally rendered — only if posts.length > 0)
│   ├── div (section header — h2 + Read all link)
│   └── BlogPostCard (×3)
│       ├── Link > div (image container)
│       ├── Link (category badge)
│       ├── h3 > Link (title)
│       ├── p (excerpt, line-clamp-2)
│       └── time (publish date)
├── FinalCtaSection
│   ├── h2 (headline)
│   ├── p (supporting copy)
│   ├── div (CTA buttons)
│   └── div (trust signals — CheckCircle icons)
└── Footer (from PublicLayout, not in HomePage.tsx)
```

### Component File Locations

```
src/
├── pages/public/
│   └── HomePage.tsx                  ← Page component (data orchestration only)
├── components/public/
│   ├── HeroCarousel.tsx
│   ├── HeroSlide.tsx
│   ├── HeroControls.tsx
│   ├── BrandIntroSection.tsx
│   ├── FeaturedPortfolioSection.tsx
│   ├── GalleryGrid.tsx
│   ├── GalleryCard.tsx
│   ├── GalleryGridSkeleton.tsx
│   ├── ServicesTeaserSection.tsx
│   ├── ServiceCard.tsx
│   ├── TestimonialsSection.tsx
│   ├── StarRating.tsx
│   ├── BlogStoriesSection.tsx
│   ├── BlogPostCard.tsx
│   ├── FinalCtaSection.tsx
│   └── PageMeta.tsx
├── components/layout/
│   ├── Navbar.tsx
│   └── Footer.tsx
└── hooks/
    ├── useScrollReveal.ts
    ├── useHeroCarousel.ts            ← Carousel state: current, isPaused, prev, next, goTo
    └── useTestimonialCycle.ts        ← Same pattern for testimonial carousel
```

### Separation of Concerns

`HomePage.tsx` is a **data-fetching and orchestration component** only. It calls all query hooks, handles loading/error states, and passes resolved data to presentational section components. It contains no layout code.

```tsx
// HomePage.tsx — should look exactly like this, no more
export default function HomePage() {
  const { data: slides,       isLoading: slidesLoading }  = useHeroSlides();
  const { data: galleries,    isLoading: galleriesLoading } = useGalleries({ featured: 'true', per_page: '6' });
  const { data: blogData,     isLoading: blogLoading }     = useBlogPosts({ per_page: '3' });
  const { data: testimonials, isLoading: testimonialsLoading } = useTestimonials({ limit: '3' });
  const { data: settings }                                 = usePublicSettings();
  const { data: introBlock }                               = useReusableBlock('homepage-intro');

  return (
    <>
      <PageMeta
        title={settings?.seo?.default_title ?? undefined}
        description={settings?.seo?.default_description ?? undefined}
      />
      <HeroCarousel slides={slides ?? []} isLoading={slidesLoading} />
      <BrandIntroSection settings={settings} introBlock={introBlock} />
      <FeaturedPortfolioSection galleries={galleries?.items ?? []} isLoading={galleriesLoading} />
      <ServicesTeaserSection />          {/* fetches its own data internally */}
      <TestimonialsSection testimonials={testimonials ?? []} isLoading={testimonialsLoading} />
      <BlogStoriesSection posts={blogData?.items ?? []} isLoading={blogLoading} />
      <FinalCtaSection />
    </>
  );
}
```

---

## 18. Data Contract

### API Calls Required

| Section | Endpoint | Hook | staleTime |
|---|---|---|---|
| Navbar + Brand Intro + Footer | `GET /api/v1/settings/public` | `usePublicSettings()` | 5 min |
| Brand Intro (body copy) | Reusable block `homepage-intro` | `useReusableBlock('homepage-intro')` | 5 min |
| Hero Carousel | `GET /api/v1/hero-slides` | `useHeroSlides()` | 5 min |
| Featured Portfolio | `GET /api/v1/galleries?featured=true&per_page=6` | `useGalleries({featured:'true',per_page:'6'})` | 5 min |
| Services Teaser | `GET /api/v1/services?per_page=3` | `useServices({per_page:'3'})` | 5 min |
| Testimonials | `GET /api/v1/testimonials?limit=3` | `useTestimonials({limit:'3'})` | 5 min |
| Blog Stories | `GET /api/v1/blog/posts?per_page=3` | `useBlogPosts({per_page:'3'})` | 5 min |

All 7 requests fire in parallel on page load (no waterfall). TanStack Query deduplicates any shared cache keys.

### Request Parallelism

```typescript
// All queries start simultaneously — React Query fires them in parallel
// No useEffect waterfalls, no sequential fetching
const [slides, galleries, posts, testimonials, settings, intro, services] = [
  useHeroSlides(),
  useGalleries({ featured: 'true', per_page: '6' }),
  useBlogPosts({ per_page: '3' }),
  useTestimonials({ limit: '3' }),
  usePublicSettings(),
  useReusableBlock('homepage-intro'),
  useServices({ per_page: '3' }),
];
```

### Graceful Degradation

Each section handles its own empty and error states independently. A failed request for testimonials does not affect the gallery section. The page remains functional with partial data.

| Section | When data unavailable |
|---|---|
| Hero | Shows `HeroSkeleton` if loading; hides section if 0 slides |
| Brand Intro | Shows tagline only (from settings) if introBlock is null |
| Portfolio | Shows `GalleryGridSkeleton` if loading; shows "No galleries yet" if 0 |
| Services | Shows `ServicesTeaserSkeleton` if loading; hides if 0 |
| Testimonials | Shows skeleton if loading; hides entire section if 0 |
| Blog | Shows skeleton if loading; hides entire section if 0 |
| CTA | Always renders (no data dependency) |

---

## 19. Performance Targets

### Core Web Vitals Goals

| Metric | Target | Strategy |
|---|---|---|
| LCP (Largest Contentful Paint) | < 2.5s | Hero image: `loading="eager"`, `fetchpriority="high"`, appropriate size |
| FID / INP (Interaction to Next Paint) | < 100ms | No heavy JS on main thread; carousel logic is lightweight |
| CLS (Cumulative Layout Shift) | < 0.1 | All images have explicit `width` + `height`; skeleton placeholders match real dimensions |
| TTFB (Time to First Byte) | < 300ms | PHP API response time target; settings cached; OPcache enabled |

### Image Loading Strategy

```
Hero image (first slide):   loading="eager"  fetchpriority="high"  ← LCP element
Hero images (2nd+ slides):  loading="lazy"                         ← Deferred
Gallery covers:             loading="lazy"    (below hero)
Service covers:             loading="lazy"
Blog post covers:           loading="lazy"
Testimonial portraits:      loading="lazy"
Footer logo:                loading="eager"   (small, render-blocking avoided via CSS)
```

### Preloading the Hero Image

The hero image is the LCP element. Preload it via `<link rel="preload">` in `index.html` is impractical because the image URL is dynamic (from API). Instead:

1. Set `fetchpriority="high"` and `loading="eager"` on the first slide image
2. Start the hero API request as early as possible (no layout effect waterfall)
3. Use `sizes` attribute for responsive selection: `sizes="100vw"`

### Bundle Size

| Chunk | Target Size (gzipped) |
|---|---|
| Initial vendor (React, Router, Query) | < 80KB |
| App code (public pages) | < 60KB |
| Lightbox (gallery detail only) | Lazy-loaded, not on homepage |
| Tiptap editor (admin only) | Lazy-loaded, not on homepage |

The homepage imports no admin-specific code. Code splitting is enforced by the lazy route loading in `App.tsx`.

---

## 20. SEO Specification

### Document `<head>` for Homepage

```tsx
<PageMeta
  title="Professional Photography Studio | San Francisco"  // from settings.seo.default_title
  description="Mesh Photography captures weddings, portraits, and commercial work with intentional craft. Based in San Francisco, serving the Bay Area."
  ogImage={settings?.site?.logo_url ?? undefined}
  ogType="website"
  canonical="https://meshphoto.com/"
/>
```

### JSON-LD Structured Data

The homepage renders two JSON-LD blocks: `LocalBusiness` and `WebSite`.

```tsx
<PageMeta
  jsonLd={{
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'LocalBusiness',
        '@id': 'https://meshphoto.com/#business',
        name: settings?.site?.name ?? 'Mesh Photography',
        description: settings?.seo?.default_description ?? '',
        url: 'https://meshphoto.com',
        telephone: settings?.contact?.phone ?? '',
        email: settings?.contact?.email ?? '',
        address: {
          '@type': 'PostalAddress',
          addressLocality: 'San Francisco',
          addressRegion: 'CA',
          addressCountry: 'US',
        },
        priceRange: '$$–$$$',
        image: settings?.site?.logo_url ?? '',
        sameAs: [
          settings?.social?.instagram,
          settings?.social?.facebook,
        ].filter(Boolean),
      },
      {
        '@type': 'WebSite',
        '@id': 'https://meshphoto.com/#website',
        url: 'https://meshphoto.com',
        name: settings?.site?.name ?? 'Mesh Photography',
        potentialAction: {
          '@type': 'SearchAction',
          target: 'https://meshphoto.com/blog/search?q={search_term_string}',
          'query-input': 'required name=search_term_string',
        },
      },
    ],
  }}
/>
```

### SEO Anti-Patterns to Avoid

- Do not render visible `<h1>` containing just the site name — that belongs in the `<title>` tag; the visible `<h1>` should be the hero headline
- Do not use `<h1>` inside the gallery cards — those are `<h3>`
- Do not `noindex` the homepage
- Do not lazy-load the hero background image (LCP impact)
- Do not block JavaScript — the page has no content without it (React SPA)

---

## 21. Implementation Checklist

### Foundation

- [ ] `tailwind.config.ts` includes all design system color tokens, font families, custom spacing, shadow, and border-radius
- [ ] Google Fonts loaded in `index.html` with `preconnect` hints
- [ ] `PageMeta` component renders `<title>`, `<meta description>`, OG tags, and optional JSON-LD
- [ ] Skip-to-content link is first focusable element in `PublicLayout`
- [ ] `useScrollReveal` hook implemented and applied to all sections below the fold

### Hero Carousel

- [ ] Renders all slides from `useHeroSlides()` API data
- [ ] Auto-rotates at 6-second interval; pauses on hover and explicit button press
- [ ] `prefers-reduced-motion` disables auto-rotation from mount
- [ ] Cross-fade transition (no slide animation)
- [ ] Dot indicators sync with current slide
- [ ] Prev/next buttons functional with aria-labels
- [ ] Pause/play button with correct aria-label (`Pause carousel` / `Resume carousel`)
- [ ] ARIA: `role="region"`, `aria-roledescription="carousel"`, `aria-label`, `aria-live`
- [ ] First slide image: `loading="eager"`, `fetchpriority="high"`
- [ ] Subsequent slide images: `loading="lazy"`
- [ ] `HeroSkeleton` renders before data resolves (dark bg, white shimmer placeholders)

### Brand Introduction

- [ ] Tagline from `settings.site.tagline`
- [ ] Body copy from `useReusableBlock('homepage-intro')` — null-safe rendering
- [ ] Gold decorative rules (top and bottom)
- [ ] "About the Studio" link navigates to `/about`

### Featured Portfolio

- [ ] Requests `featured=true&per_page=6` from API
- [ ] Renders `GalleryGridSkeleton` while loading (6 cards matching exact dimensions)
- [ ] Gallery card: image, hover overlay with text, card footer with title and meta
- [ ] Image hover: scale-105 at 700ms ease-out
- [ ] Hover overlay: opacity-0 → opacity-100 at 400ms
- [ ] Title hover: `text-bronze` transition at 200ms
- [ ] Card focus: `ring-2 ring-bronze ring-offset-2`
- [ ] `aria-label` on card link includes title and image count
- [ ] All images have `alt` text from `media.alt_text`
- [ ] "View all galleries" link to `/portfolio`
- [ ] Empty state hidden (section does not render if 0 galleries)

### Services Teaser

- [ ] Renders first 3 published services from API
- [ ] Cover image at `aspect-video` with hover scale
- [ ] Bronze horizontal rule below title
- [ ] Price display from `service.price_display`
- [ ] Short description truncated at 3 lines (`line-clamp-3`)
- [ ] "Explore this service" link to `/services/{slug}`
- [ ] "Let's Talk" secondary CTA to `/contact`

### Testimonials

- [ ] Renders max 3 testimonials from API
- [ ] Single testimonial visible at a time with fade transition
- [ ] Navigation dots: active dot wider (`w-4`) with bronze color
- [ ] Prev/next with aria-labels
- [ ] Star rating renders correctly (gold filled / cream unfilled)
- [ ] `aria-live="polite"` announces current testimonial to screen readers
- [ ] Entire section hidden if 0 testimonials
- [ ] Swipe gesture support on mobile (touchstart/touchend)

### Blog Stories

- [ ] Renders 3 most recent published posts
- [ ] Entire section hidden if 0 posts
- [ ] Blog post card: image, category badge, title, excerpt, date
- [ ] Category badge links to `/blog/category/{slug}`
- [ ] Post title links to `/blog/{slug}`
- [ ] Excerpt: `line-clamp-2`
- [ ] Date rendered via `<time>` with `dateTime` attribute

### Final CTA

- [ ] Dark espresso background
- [ ] Primary button: "Book a Session" → `/booking`
- [ ] Secondary ghost button: "Get in Touch" → `/contact`
- [ ] Three trust signals visible below buttons
- [ ] Both buttons full-width and stacked on mobile

### Footer

- [ ] Contact info from `settings.contact`
- [ ] Social links from `settings.social` — only rendered if URL is non-null
- [ ] Social links: `target="_blank"`, `rel="noopener noreferrer"`, `aria-label`
- [ ] Copyright year is dynamic (`new Date().getFullYear()`)
- [ ] Privacy and Terms links work (CMS pages exist for these slugs)

### Accessibility Final Check

- [ ] Run `axe` browser extension on desktop at 1280px → 0 critical violations
- [ ] Run `axe` on mobile viewport (375px) → 0 critical violations
- [ ] Tab through entire page — all interactive elements reachable in logical order
- [ ] Activate carousel controls with Space and Enter keys
- [ ] Navigate gallery grid using keyboard only
- [ ] Test with VoiceOver (macOS) or NVDA (Windows) — hero and carousel announce correctly
- [ ] Color contrast checked: charcoal on ivory, bronze on ivory, ivory on espresso

### Performance Final Check

- [ ] Lighthouse mobile score ≥ 85
- [ ] LCP ≤ 2.5 seconds (4G throttled in DevTools)
- [ ] No CLS — confirm with Lighthouse CLS metric
- [ ] No layout shift when skeleton transitions to real content
- [ ] React DevTools Profiler: no unnecessary re-renders on scroll
- [ ] Network tab: hero image is the largest single network request; all others deferred
