import { useState, useCallback, useEffect, useRef } from 'react';
import { ChevronLeft, ChevronRight, Quote } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import StarRating from './StarRating';
import type { Testimonial } from '@/types/models';

interface TestimonialsSectionProps {
  testimonials: Testimonial[];
  isLoading?: boolean;
}

const AUTOPLAY_MS = 6000;

function getInitials(name: string): string {
  const parts = name.match(/[A-Za-z0-9]+/g) ?? [];
  return parts.slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'MP';
}

/** Circular client portrait used in the navigation roster. */
function RosterAvatar({
  testimonial,
  isActive,
  onClick,
}: {
  testimonial: Testimonial;
  isActive: boolean;
  onClick: () => void;
}) {
  const ring = isActive
    ? 'ring-2 ring-bronze ring-offset-2 ring-offset-charcoal scale-110'
    : 'ring-1 ring-ivory/15 opacity-55 hover:opacity-100 hover:ring-bronze/50';

  return (
    <button
      type="button"
      onClick={onClick}
      aria-label={`Read ${testimonial.client_name}'s testimonial`}
      aria-current={isActive ? 'true' : undefined}
      className={cn(
        'relative h-12 w-12 shrink-0 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-bronze focus-visible:ring-offset-2 focus-visible:ring-offset-charcoal sm:h-14 sm:w-14',
        ring
      )}
    >
      {testimonial.portrait ? (
        <img
          src={testimonial.portrait.thumb_url ?? testimonial.portrait.url}
          alt={testimonial.portrait.alt_text ?? testimonial.client_name}
          className="h-full w-full rounded-full object-cover"
          loading="lazy"
          width={56}
          height={56}
          decoding="async"
        />
      ) : (
        <span className="flex h-full w-full items-center justify-center rounded-full bg-pine font-body text-xs font-semibold tracking-wide text-ivory">
          {getInitials(testimonial.client_name)}
        </span>
      )}
    </button>
  );
}

function Slide({ testimonial }: { testimonial: Testimonial }) {
  return (
    <figure className="mx-auto flex max-w-2xl flex-col items-center px-2 text-center">
      <blockquote>
        <p className="font-display text-xl font-light leading-[1.4] text-ivory sm:text-2xl lg:text-[1.875rem] lg:leading-[1.4]">
          <span aria-hidden="true" className="text-bronze">“</span>
          {testimonial.body}
          <span aria-hidden="true" className="text-bronze">”</span>
        </p>
      </blockquote>

      {testimonial.rating > 0 && (
        <StarRating rating={testimonial.rating} className="mt-5 justify-center" />
      )}

      <figcaption className="mt-4 flex flex-col items-center gap-1">
        <cite className="font-body text-sm font-semibold not-italic uppercase tracking-[0.18em] text-ivory">
          {testimonial.client_name}
        </cite>
        {testimonial.client_role && (
          <span className="font-body text-xs uppercase tracking-[0.2em] text-bronze-light">
            {testimonial.client_role}
          </span>
        )}
      </figcaption>
    </figure>
  );
}

function TestimonialsSkeleton() {
  return (
    <section className="bg-[linear-gradient(160deg,#23332d_0%,#1a1a1a_60%,#221d19_100%)] py-14 lg:py-20">
      <div className="site-container flex flex-col items-center">
        <div className="mb-8 h-3 w-28 animate-pulse rounded bg-ivory/10" />
        <div className="mb-6 h-9 w-72 max-w-full animate-pulse rounded bg-ivory/10" />
        <div className="w-full max-w-2xl space-y-3">
          <div className="mx-auto h-6 w-full animate-pulse rounded bg-ivory/10" />
          <div className="mx-auto h-6 w-11/12 animate-pulse rounded bg-ivory/10" />
          <div className="mx-auto h-6 w-2/3 animate-pulse rounded bg-ivory/10" />
        </div>
        <div className="mt-10 flex gap-3">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-14 w-14 animate-pulse rounded-full bg-ivory/10" />
          ))}
        </div>
      </div>
    </section>
  );
}

export default function TestimonialsSection({ testimonials, isLoading }: TestimonialsSectionProps) {
  const [current, setCurrent] = useState(0);
  const [paused, setPaused] = useState(false);
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  const count = testimonials.length;

  const goTo = useCallback((index: number) => setCurrent(index), []);
  const prev = useCallback(() => setCurrent((c) => (c - 1 + count) % count), [count]);
  const next = useCallback(() => setCurrent((c) => (c + 1) % count), [count]);

  // Auto-advance, paused on hover/focus or while the section is off-screen.
  const nextRef = useRef(next);
  nextRef.current = next;
  useEffect(() => {
    if (count <= 1 || paused || !isVisible) return;
    const id = window.setInterval(() => nextRef.current(), AUTOPLAY_MS);
    return () => window.clearInterval(id);
  }, [count, paused, isVisible]);

  if (isLoading) return <TestimonialsSkeleton />;
  if (!count) return null;

  const activeIndex = current % count;
  const ratedTestimonials = testimonials.filter((item) => item.rating > 0);
  const averageRating = ratedTestimonials.length
    ? ratedTestimonials.reduce((sum, item) => sum + item.rating, 0) / ratedTestimonials.length
    : 0;

  return (
    <section
      ref={ref}
      className={cn(
        'relative overflow-hidden bg-[linear-gradient(160deg,#23332d_0%,#1a1a1a_58%,#221d19_100%)] py-14 transition-all duration-700 ease-out lg:py-20',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
      )}
    >
      {/* Ambient glow + oversized quotation glyph */}
      <div className="pointer-events-none absolute left-1/2 top-0 h-56 w-56 -translate-x-1/2 rounded-full bg-bronze/10 blur-[110px]" />
      <Quote
        className="pointer-events-none absolute -left-6 top-8 h-40 w-40 text-bronze/[0.07] lg:left-10 lg:h-52 lg:w-52"
        aria-hidden="true"
        strokeWidth={1}
      />

      <div className="site-container relative">
        <div className="flex flex-col items-center text-center">
          <span className="mb-4 inline-flex items-center gap-3 font-body text-xs uppercase tracking-[0.22em] text-bronze-light">
            <span className="h-px w-8 bg-bronze/60" />
            Client Stories
            <span className="h-px w-8 bg-bronze/60" />
          </span>
          <h2 className="max-w-2xl font-display text-3xl font-light leading-tight text-ivory lg:text-4xl">
            Words from the people in front of the lens.
          </h2>

          {averageRating > 0 && (
            <div className="mt-5 flex items-center gap-3 text-ivory/80">
              <StarRating rating={Math.round(averageRating)} />
              <span className="font-body text-sm tracking-wide">
                <span className="font-semibold text-ivory">{averageRating.toFixed(1)}</span>
                <span className="text-taupe"> · {count} {count === 1 ? 'review' : 'reviews'}</span>
              </span>
            </div>
          )}
        </div>

        {/* Spotlight rotator */}
        <div
          className="relative mt-10"
          role="region"
          aria-roledescription="carousel"
          aria-label="Client testimonials"
          onMouseEnter={() => setPaused(true)}
          onMouseLeave={() => setPaused(false)}
          onFocusCapture={() => setPaused(true)}
          onBlurCapture={() => setPaused(false)}
        >
          {count > 1 && (
            <>
              <button
                type="button"
                onClick={prev}
                aria-label="Previous testimonial"
                className="absolute left-0 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-ivory/15 bg-ivory/5 text-ivory/70 backdrop-blur-sm transition-colors duration-200 hover:border-bronze hover:bg-bronze/15 hover:text-ivory focus:outline-none focus-visible:ring-2 focus-visible:ring-bronze lg:-left-2"
              >
                <ChevronLeft className="h-5 w-5" aria-hidden="true" />
              </button>
              <button
                type="button"
                onClick={next}
                aria-label="Next testimonial"
                className="absolute right-0 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-ivory/15 bg-ivory/5 text-ivory/70 backdrop-blur-sm transition-colors duration-200 hover:border-bronze hover:bg-bronze/15 hover:text-ivory focus:outline-none focus-visible:ring-2 focus-visible:ring-bronze lg:-right-2"
              >
                <ChevronRight className="h-5 w-5" aria-hidden="true" />
              </button>
            </>
          )}

          <div className="overflow-hidden px-12 sm:px-16 lg:px-20">
            <div
              className="flex transition-transform duration-700 ease-out"
              style={{ transform: `translateX(-${activeIndex * 100}%)` }}
            >
              {testimonials.map((testimonial, i) => (
                <div
                  key={testimonial.id}
                  className="w-full shrink-0"
                  aria-hidden={i !== activeIndex}
                >
                  <Slide testimonial={testimonial} />
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Avatar roster navigation */}
        {count > 1 && (
          <div className="mt-10 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
            {testimonials.map((testimonial, i) => (
              <RosterAvatar
                key={testimonial.id}
                testimonial={testimonial}
                isActive={i === activeIndex}
                onClick={() => goTo(i)}
              />
            ))}
          </div>
        )}
      </div>
    </section>
  );
}
