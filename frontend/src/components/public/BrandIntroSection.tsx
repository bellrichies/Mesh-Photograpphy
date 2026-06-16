import { type CSSProperties } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, Calendar, Camera, Heart } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import type { GalleryPhoto, PublicSettings } from '@/types/models';

interface BrandIntroSectionProps {
  settings?: PublicSettings | null;
  photos?: GalleryPhoto[];
}

const proofPoints = [
  {
    icon: Camera,
    label: 'Editorial Eye',
    text: 'Composed imagery with natural light, considered framing, and quiet confidence.',
  },
  {
    icon: Heart,
    label: 'Human Pace',
    text: 'A calm, guided process that helps people feel present instead of posed.',
  },
  {
    icon: Calendar,
    label: 'Planned Flow',
    text: 'Session rhythm, timing, and details handled with care from the first note.',
  },
];

function ImageStack({ photos, fallbackText }: { photos: GalleryPhoto[]; fallbackText: string }) {
  const [primary, secondary, tertiary] = photos;

  if (!primary) {
    return (
      <div className="relative min-h-[520px] overflow-hidden rounded-2xl bg-charcoal p-8 text-ivory">
        <div className="absolute inset-0 bg-[linear-gradient(135deg,rgba(47,76,69,0.72),rgba(27,23,20,0.96))]" />
        <div className="relative z-10 flex h-full min-h-[460px] flex-col justify-end">
          <p className="font-body text-xs uppercase tracking-[0.18em] text-bronze-light mb-4">Mesh Photography</p>
          <p className="font-display text-4xl font-light leading-tight max-w-sm">{fallbackText}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="relative mx-auto w-full max-w-[580px] lg:max-w-none">
      {/* Decorative corner bracket */}
      <div className="absolute left-0 top-6 hidden h-28 w-28 border border-bronze/35 lg:block" />

      <div className="relative pt-8 pb-16 lg:min-h-[620px] lg:pb-0 lg:pt-10 xl:min-h-[660px]">
        {/* Main large portrait */}
        <div
          className="relative ml-auto w-[90%] overflow-hidden rounded-2xl bg-charcoal shadow-[0_20px_60px_rgba(0,0,0,0.18)] lg:w-[86%]"
          style={{ aspectRatio: '4 / 5' }}
        >
          <img
            src={primary.url}
            alt={primary.alt_text ?? 'Mesh Photography portrait session'}
            className="h-full w-full object-cover transition-transform duration-700 hover:scale-[1.03]"
            loading="lazy"
            width={720}
            height={900}
            decoding="async"
          />
          <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,0.01),rgba(0,0,0,0.50))]" />
          <div className="absolute bottom-0 left-0 right-0 p-6 text-right sm:p-8">
            <p className="font-body text-[10px] uppercase tracking-[0.2em] text-ivory/65">Signature Approach</p>
            <p className="font-display text-2xl text-ivory font-light leading-snug mt-2 ml-auto max-w-[240px] sm:text-3xl">
              Direction that feels effortless on the day.
            </p>
          </div>
        </div>

        {/* Bottom-left floating photo */}
        {secondary && (
          <div className="absolute bottom-10 left-0 w-[44%] max-w-[220px] overflow-hidden rounded-xl border-[3px] border-ivory bg-sand shadow-[0_12px_40px_rgba(0,0,0,0.16)] lg:bottom-16">
            <img
              src={secondary.thumb_url ?? secondary.url}
              alt={secondary.alt_text ?? 'Photography detail'}
              className="h-full w-full object-cover transition-transform duration-700 hover:scale-[1.05]"
              style={{ aspectRatio: '1 / 1' }}
              loading="lazy"
              width={320}
              height={320}
              decoding="async"
            />
          </div>
        )}

        {/* Top-right floating photo */}
        {tertiary && (
          <div className="absolute right-0 top-0 w-[36%] max-w-[195px] overflow-hidden rounded-xl border-[3px] border-ivory bg-sand shadow-[0_12px_40px_rgba(0,0,0,0.16)] sm:right-4 lg:right-2">
            <img
              src={tertiary.thumb_url ?? tertiary.url}
              alt={tertiary.alt_text ?? 'Photography story'}
              className="h-full w-full object-cover transition-transform duration-700 hover:scale-[1.05]"
              style={{ aspectRatio: '3 / 4' }}
              loading="lazy"
              width={260}
              height={346}
              decoding="async"
            />
          </div>
        )}
      </div>
    </div>
  );
}

export default function BrandIntroSection({ settings, photos = [] }: BrandIntroSectionProps) {
  const { ref, isVisible } = useScrollReveal<HTMLElement>();
  const tagline = settings?.site.tagline ?? 'Capturing the quiet moments between the moments.';

  return (
    <section
      ref={ref}
      className={cn(
        'relative overflow-hidden bg-[linear-gradient(180deg,#faf9f7_0%,#f4efe8_52%,#faf9f7_100%)] py-20 lg:py-28',
        isVisible && 'is-revealed'
      )}
    >
      <div className="absolute inset-x-0 top-0 h-px bg-bronze/20" />
      <div className="absolute inset-x-0 bottom-0 h-px bg-bronze/20" />

      <div className="site-container">
        <div className="grid grid-cols-1 items-center gap-14 lg:grid-cols-2 lg:gap-12 xl:gap-20">

          {/* Left: text content */}
          <div className="reveal-up">
            {/* Eyebrow */}
            <div className="flex items-center gap-3 mb-8">
              <span className="h-px w-10 bg-bronze" />
              <span className="font-body text-[11px] uppercase tracking-[0.22em] text-bronze">The Studio</span>
            </div>

            {/* Heading */}
            <h2 className="font-display text-5xl sm:text-6xl lg:text-6xl xl:text-7xl text-charcoal font-light leading-[1.02] tracking-tight">
              {tagline}
            </h2>

            {/* Body */}
            <p className="font-body text-base lg:text-[17px] text-taupe leading-relaxed max-w-lg mt-7">
              We shape photography sessions around presence, ease, and emotional clarity, so every
              frame feels refined without losing the feeling that made it matter.
            </p>

            {/* CTAs */}
            <div className="mt-10 flex flex-wrap gap-4">
              <Link
                to="/portfolio"
                className="group inline-flex items-center gap-2.5 bg-charcoal px-7 py-3.5 font-body text-[11px] uppercase tracking-[0.18em] text-ivory transition-all duration-200 hover:bg-bronze hover:-translate-y-0.5 hover:shadow-soft"
              >
                Explore the Work
                <ArrowRight size={14} strokeWidth={2} className="transition-transform duration-200 group-hover:translate-x-1" />
              </Link>
              <Link
                to="/booking"
                className="inline-flex items-center gap-2.5 border border-charcoal/30 px-7 py-3.5 font-body text-[11px] uppercase tracking-[0.18em] text-charcoal transition-all duration-200 hover:border-bronze hover:text-bronze hover:-translate-y-0.5"
              >
                Plan a Session
              </Link>
            </div>

            {/* Proof-point cards */}
            <div className="mt-12 grid grid-cols-1 gap-3 sm:grid-cols-3">
              {proofPoints.map(({ icon: Icon, label, text }) => (
                <div
                  key={label}
                  className="group border border-cream bg-white/70 p-5 rounded-xl shadow-soft transition-all duration-200 hover:-translate-y-1 hover:border-bronze/40 hover:bg-white"
                >
                  <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-pine text-ivory transition-colors duration-200 group-hover:bg-bronze">
                    <Icon size={18} strokeWidth={1.7} />
                  </div>
                  <h3 className="font-display text-xl text-charcoal font-light leading-tight">{label}</h3>
                  <p className="font-body text-xs text-taupe leading-relaxed mt-2">{text}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Right: photo collage */}
          <div className="reveal-up" style={{ '--reveal-delay': '160ms' } as CSSProperties}>
            <ImageStack photos={photos} fallbackText={tagline} />
          </div>
        </div>
      </div>
    </section>
  );
}
