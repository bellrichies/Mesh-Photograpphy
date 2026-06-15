import { Link } from 'react-router-dom';
import { Camera, Heart, Eye, Award } from 'lucide-react';
import PageMeta from '@/components/ui/PageMeta';
import { useCmsPage } from '@/api/pages';
import { useTestimonials } from '@/api/testimonials';
import { usePublicSettings } from '@/api/settings';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import DOMPurify from 'dompurify';
import StarRating from '@/components/public/StarRating';
import type { PageSection } from '@/types/models';

// ── Static fallbacks ──────────────────────────────────────────────────────────

const VALUES = [
  { icon: Camera, title: 'Authenticity',  body: 'We capture genuine moments, not manufactured ones. Every frame reflects who you truly are.' },
  { icon: Eye,    title: 'Editorial Eye', body: 'Trained in magazine and commercial work, we bring a refined visual language to every session.' },
  { icon: Heart,  title: 'Intentional Care', body: 'We invest time in understanding your story before the first shutter click. That care shows.' },
  { icon: Award,  title: 'Timeless Craft', body: 'Trends fade. We focus on images you will still love in thirty years: classic, understated, enduring.' },
];

const DEFAULT_STATS = [
  { value: '8+',  label: 'Years of Experience' },
  { value: '400+',label: 'Sessions Delivered' },
  { value: '12',  label: 'Cities Worked In' },
  { value: '98%', label: 'Client Satisfaction' },
];

// ── Helper to look up a CMS section by key ────────────────────────────────────

function getSection(sections: PageSection[], key: string): PageSection | null {
  return sections.find((s) => s.section_key === key) ?? null;
}

function useSection(sections: PageSection[], key: string): string | null {
  return getSection(sections, key)?.content ?? null;
}

function safe(html: string | null): string {
  return html ? DOMPurify.sanitize(html) : '';
}

// ── Sub-components ────────────────────────────────────────────────────────────

function SectionReveal({ children, className = '' }: { children: React.ReactNode; className?: string }) {
  const { ref, isVisible } = useScrollReveal<HTMLDivElement>();
  return (
    <div
      ref={ref}
      className={cn(
        'transition-all duration-700 ease-out',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8',
        className
      )}
    >
      {children}
    </div>
  );
}

// ── Page ──────────────────────────────────────────────────────────────────────

export default function AboutPage() {
  const { data: page, isLoading } = useCmsPage('about');
  const { data: testimonials }    = useTestimonials();
  const { data: settings }        = usePublicSettings();

  const sections = page?.sections ?? [];
  const siteName = settings?.site.name ?? 'Mesh Photography';

  // CMS fields with fallbacks
  const heroTitle       = useSection(sections, 'hero_title')    || page?.title || 'About Us';
  const heroSubtitle    = useSection(sections, 'hero_subtitle') || 'Editorial imagery for couples, founders, and families - crafted with intention, delivered with warmth.';
  const storyHeading    = useSection(sections, 'story_heading') || 'Born from a love of storytelling through light';
  const storyBody       = useSection(sections, 'story_body');
  const missionHeading  = useSection(sections, 'mission_heading') || 'To freeze fleeting moments in their most honest form';
  const missionBody     = useSection(sections, 'mission_body');
  const visionHeading   = useSection(sections, 'vision_heading') || 'Images that outlast the moment they were taken';
  const visionBody      = useSection(sections, 'vision_body');
  const approachBody    = useSection(sections, 'approach_body');
  const teamHeading     = useSection(sections, 'team_heading') || 'The people behind the lens';
  const teamBody        = useSection(sections, 'team_body');
  const clientsHeading  = useSection(sections, 'clients_heading') || 'Trusted by couples, families, and founders';
  const clientsBody     = useSection(sections, 'clients_body');
  const ctaHeading      = useSection(sections, 'cta_heading')  || "Let's create something lasting together";
  const ctaBody         = useSection(sections, 'cta_body')     || "Whether you have a clear vision or you're starting from scratch, we're here to guide you every step of the way.";
  const heroImage       = getSection(sections, 'hero_image')?.media?.url ?? null;

  // Stats: try JSON section, then fall back to defaults
  let stats = DEFAULT_STATS;
  const rawStats = useSection(sections, 'stats');
  if (rawStats) {
    try { stats = JSON.parse(rawStats); } catch { /* keep defaults */ }
  }

  // Legacy fallback: single rich_text body section (old-style pages without sections)
  const legacyBody = sections.find((s) => s.section_key === 'body')?.content ?? null;
  const effectiveStoryBody = storyBody ?? legacyBody;

  const pageSeo = page?.seo;

  if (isLoading) {
    return (
      <div className="pt-24">
        <div className="h-[40vh] bg-cream animate-pulse" />
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="h-4 bg-cream rounded animate-pulse" style={{ width: `${50 + (i % 4) * 12}%` }} />
          ))}
        </div>
      </div>
    );
  }

  return (
    <>
      <PageMeta
        title={heroTitle}
        description={pageSeo?.meta_description ?? `Learn the story behind ${siteName}: our approach, values, and the people behind the lens.`}
        seo={pageSeo}
      />

      {/* ── Hero ──────────────────────────────────────────────────────────── */}
      <section className="relative min-h-[250px] sm:min-h-[280px] bg-espresso overflow-hidden flex items-end pt-[72px]">
        {heroImage ? (
          <img
            src={heroImage}
            alt=""
            className="absolute inset-0 w-full h-full object-cover"
            loading="eager"
            fetchpriority="high"
          />
        ) : null}
        <div className="absolute inset-0 bg-gradient-to-br from-charcoal via-espresso to-charcoal opacity-90" />
        <div className="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
          <p className="font-body text-xs tracking-[0.2em] uppercase text-bronze mb-3">About Us</p>
          <h1 className="font-display text-4xl sm:text-5xl lg:text-6xl text-ivory font-light leading-[1.05] max-w-2xl">
            {heroTitle}
          </h1>
          <p className="font-body text-sm text-ivory/60 mt-4 max-w-lg leading-relaxed">{heroSubtitle}</p>
        </div>
      </section>

      {/* ── Stats bar ────────────────────────────────────────────────────── */}
      <div className="bg-charcoal">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 lg:grid-cols-4 divide-x divide-charcoal-light">
            {stats.map(({ value, label }) => (
              <div key={label} className="py-5 sm:py-6 px-4 text-center">
                <p className="font-display text-3xl sm:text-4xl text-bronze font-light">{value}</p>
                <p className="font-body text-xs tracking-widest uppercase text-ivory/50 mt-1">{label}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* ── Brand Story ──────────────────────────────────────────────────── */}
      <section className="bg-ivory py-20 lg:py-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionReveal>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
              <div>
                <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-4">
                  Our Story
                </span>
                <h2 className="font-display text-4xl lg:text-5xl text-charcoal font-light leading-tight mb-8">
                  {storyHeading}
                </h2>

                {effectiveStoryBody ? (
                  <div
                    className="prose prose-lg max-w-none font-body text-charcoal
                      prose-headings:font-display prose-headings:font-light
                      prose-a:text-bronze prose-a:no-underline hover:prose-a:underline"
                    dangerouslySetInnerHTML={{ __html: safe(effectiveStoryBody) }}
                  />
                ) : (
                  <div className="space-y-5 font-body text-taupe text-base leading-relaxed">
                    <p>
                      {siteName} was born from a deep love of storytelling through images. We believe
                      that every moment, whether grand or quiet, carries meaning, and our job is to
                      preserve it with honesty and care.
                    </p>
                    <p>
                      Based in Washington, DC, we travel wherever love, life, and light take us. From
                      intimate elopements in the mountains to editorial campaigns downtown, we bring the
                      same craft and attention to every shoot.
                    </p>
                    <p>
                      We're not interested in stiff poses or forced smiles. We create space for genuine
                      moments to emerge, then we're there to catch them.
                    </p>
                  </div>
                )}

                <div className="flex flex-wrap gap-4 mt-10">
                  <Link
                    to="/portfolio"
                    className="px-8 py-3 bg-bronze text-ivory font-body text-xs tracking-[0.15em] uppercase hover:bg-bronze-dark transition-colors duration-150"
                  >
                    See Our Work
                  </Link>
                  <Link
                    to="/contact"
                    className="px-8 py-3 border border-charcoal text-charcoal font-body text-xs tracking-[0.15em] uppercase hover:bg-charcoal hover:text-ivory transition-colors duration-150"
                  >
                    Get in Touch
                  </Link>
                </div>
              </div>

              {/* Visual accent */}
              <div className="relative hidden lg:block">
                <div className="aspect-[4/5] bg-charcoal/5 relative overflow-hidden">
                  <div className="absolute inset-0 flex items-center justify-center">
                    <div className="text-center opacity-10">
                      <Camera size={80} className="text-charcoal mx-auto mb-4" />
                      <p className="font-display text-2xl text-charcoal">Since 2018</p>
                    </div>
                  </div>
                </div>
                <div className="absolute -bottom-6 -left-6 w-32 h-32 bg-bronze/10 border border-bronze/20" />
                <div className="absolute -top-6 -right-6 w-20 h-20 bg-charcoal/5 border border-charcoal/10" />
              </div>
            </div>
          </SectionReveal>
        </div>
      </section>

      {(teamBody || clientsBody) && (
        <section className="bg-ivory py-16 lg:py-20 border-t border-cream">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <SectionReveal>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
                {teamBody && (
                  <div>
                    <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-4">Team</span>
                    <h2 className="font-display text-3xl lg:text-4xl text-charcoal font-light mb-5">{teamHeading}</h2>
                    <div
                      className="prose max-w-none font-body text-taupe text-sm leading-relaxed"
                      dangerouslySetInnerHTML={{ __html: safe(teamBody) }}
                    />
                  </div>
                )}

                {clientsBody && (
                  <div>
                    <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-4">Clients</span>
                    <h2 className="font-display text-3xl lg:text-4xl text-charcoal font-light mb-5">{clientsHeading}</h2>
                    <div
                      className="prose max-w-none font-body text-taupe text-sm leading-relaxed"
                      dangerouslySetInnerHTML={{ __html: safe(clientsBody) }}
                    />
                  </div>
                )}
              </div>
            </SectionReveal>
          </div>
        </section>
      )}

      {/* ── Mission & Vision ─────────────────────────────────────────────── */}
      <section className="bg-sand py-20 lg:py-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionReveal>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
              <div className="bg-ivory p-10 border border-cream">
                <span className="font-body text-xs tracking-[0.18em] uppercase text-bronze block mb-4">Mission</span>
                <h3 className="font-display text-3xl text-charcoal font-light mb-5">
                  {missionHeading}
                </h3>
                {missionBody ? (
                  <div
                    className="font-body text-sm text-taupe leading-relaxed prose max-w-none
                      prose-headings:font-display prose-a:text-bronze"
                    dangerouslySetInnerHTML={{ __html: safe(missionBody) }}
                  />
                ) : (
                  <p className="font-body text-sm text-taupe leading-relaxed">
                    Every image we deliver is a commitment to authenticity. We push past performance
                    and manufactured perfection to find the real story: the glance, the laugh, the
                    quiet in-between. That's where life actually lives.
                  </p>
                )}
              </div>

              <div className="bg-charcoal p-10">
                <span className="font-body text-xs tracking-[0.18em] uppercase text-bronze block mb-4">Vision</span>
                <h3 className="font-display text-3xl text-ivory font-light mb-5">
                  {visionHeading}
                </h3>
                {visionBody ? (
                  <div
                    className="font-body text-sm text-ivory/60 leading-relaxed prose prose-invert max-w-none"
                    dangerouslySetInnerHTML={{ __html: safe(visionBody) }}
                  />
                ) : (
                  <p className="font-body text-sm text-ivory/60 leading-relaxed">
                    We envision a body of work that families will pass down across generations: images
                    free of fleeting trends, full of enduring emotion. Photography as heirloom, not content.
                  </p>
                )}
              </div>
            </div>
          </SectionReveal>
        </div>
      </section>

      {/* ── Core Values ──────────────────────────────────────────────────── */}
      <section className="bg-ivory py-20 lg:py-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionReveal>
            <div className="text-center mb-14">
              <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-3">What Guides Us</span>
              <h2 className="font-display text-4xl lg:text-5xl text-charcoal font-light">Core Values</h2>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
              {VALUES.map(({ icon: Icon, title, body }) => (
                <div key={title} className="group p-6 border border-cream hover:border-bronze/40 transition-colors duration-200">
                  <div className="w-10 h-10 bg-sand flex items-center justify-center mb-5">
                    <Icon size={18} className="text-bronze" />
                  </div>
                  <h3 className="font-display text-xl text-charcoal mb-3">{title}</h3>
                  <p className="font-body text-sm text-taupe leading-relaxed">{body}</p>
                </div>
              ))}
            </div>
          </SectionReveal>
        </div>
      </section>

      {/* ── Approach ─────────────────────────────────────────────────────── */}
      <section className="bg-charcoal py-20 lg:py-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <SectionReveal>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
              <div>
                <span className="font-body text-xs tracking-[0.18em] uppercase text-bronze block mb-4">
                  Our Approach
                </span>
                <h2 className="font-display text-4xl lg:text-5xl text-ivory font-light leading-tight mb-8">
                  Calm direction.<br />Thoughtful pacing.
                </h2>
                {approachBody ? (
                  <div
                    className="prose prose-invert max-w-none font-body text-ivory/60 text-sm leading-relaxed"
                    dangerouslySetInnerHTML={{ __html: safe(approachBody) }}
                  />
                ) : (
                  <div className="space-y-5 font-body text-ivory/60 text-sm leading-relaxed">
                    <p>
                      Before every session, we invest in understanding you: your dynamic, your
                      surroundings, your story. That investment pays off in every frame.
                    </p>
                    <p>
                      On the day, we guide with a light touch. Prompts, not poses. Movement, not
                      stiffness. You'll forget the camera is there. That's when the best images happen.
                    </p>
                    <p>
                      After the session, our editing process is meticulous and consistent: colour,
                      tone, and mood calibrated to the visual identity that defines {siteName}.
                    </p>
                  </div>
                )}
              </div>

              <div className="grid grid-cols-2 gap-4">
                {['Before', 'During', 'Editing', 'Delivery'].map((step, i) => (
                  <div key={step} className="p-6 border border-charcoal-light">
                    <p className="font-body text-xs text-bronze tracking-widest mb-2">0{i + 1}</p>
                    <p className="font-display text-xl text-ivory">{step}</p>
                  </div>
                ))}
              </div>
            </div>
          </SectionReveal>
        </div>
      </section>

      {/* ── Testimonials excerpt ──────────────────────────────────────────── */}
      {testimonials && testimonials.length > 0 && (
        <section className="bg-sand py-20 lg:py-28">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <SectionReveal>
              <div className="text-center mb-12">
                <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-3">Client Words</span>
                <h2 className="font-display text-4xl text-charcoal font-light">What Our Clients Say</h2>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {testimonials.slice(0, 3).map((t) => (
                  <div key={t.id} className="bg-ivory p-8 border border-cream">
                    <StarRating rating={t.rating} className="justify-start" />
                    <blockquote className="font-display text-lg text-charcoal font-light leading-snug mt-4 mb-5">
                      &ldquo;{t.body}&rdquo;
                    </blockquote>
                    <div>
                      <p className="font-body text-sm font-medium text-charcoal">{t.client_name}</p>
                      {t.client_role && (
                        <p className="font-body text-xs text-taupe">{t.client_role}</p>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </SectionReveal>
          </div>
        </section>
      )}

      {/* ── CTA ──────────────────────────────────────────────────────────── */}
      <section className="bg-ivory py-20 lg:py-28">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <SectionReveal>
            <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-4">
              Start the Conversation
            </span>
            <h2 className="font-display text-4xl lg:text-5xl text-charcoal font-light mb-6">
              {ctaHeading}
            </h2>
            <p className="font-body text-sm text-taupe max-w-md mx-auto mb-10 leading-relaxed">
              {ctaBody}
            </p>
            <div className="flex flex-wrap gap-4 justify-center">
              <Link
                to="/booking"
                className="px-10 py-4 bg-bronze text-ivory font-body text-xs tracking-[0.15em] uppercase hover:bg-bronze-dark transition-colors duration-150"
              >
                Book a Session
              </Link>
              <Link
                to="/contact"
                className="px-10 py-4 border border-charcoal text-charcoal font-body text-xs tracking-[0.15em] uppercase hover:bg-charcoal hover:text-ivory transition-colors duration-150"
              >
                Ask a Question
              </Link>
            </div>
          </SectionReveal>
        </div>
      </section>
    </>
  );
}
