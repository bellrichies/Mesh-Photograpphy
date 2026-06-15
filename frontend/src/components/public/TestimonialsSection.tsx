import { useState, useCallback } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import StarRating from './StarRating';
import type { Testimonial } from '@/types/models';

interface TestimonialsSectionProps {
  testimonials: Testimonial[];
  isLoading?: boolean;
}

function TestimonialsSkeleton() {
  return (
    <section className="py-16 lg:py-24 bg-ivory">
      <div className="max-w-4xl mx-auto px-6">
        <div className="text-center mb-12">
          <div className="h-3 w-24 bg-cream animate-pulse rounded mx-auto" />
        </div>
        <div className="space-y-4 max-w-2xl mx-auto text-center">
          <div className="h-8 bg-cream animate-pulse rounded" />
          <div className="h-8 bg-cream animate-pulse rounded w-4/5 mx-auto" />
          <div className="h-8 bg-cream animate-pulse rounded w-3/5 mx-auto" />
          <div className="flex justify-center mt-6 gap-2">
            <div className="h-10 w-10 rounded-full bg-cream animate-pulse" />
            <div className="space-y-1.5 text-left">
              <div className="h-3 w-24 bg-cream animate-pulse rounded" />
              <div className="h-2.5 w-16 bg-cream animate-pulse rounded" />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

export default function TestimonialsSection({ testimonials, isLoading }: TestimonialsSectionProps) {
  const [current, setCurrent] = useState(0);
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  const prev = useCallback(
    () => setCurrent((c) => (c - 1 + testimonials.length) % testimonials.length),
    [testimonials.length]
  );
  const next = useCallback(
    () => setCurrent((c) => (c + 1) % testimonials.length),
    [testimonials.length]
  );

  if (isLoading) return <TestimonialsSkeleton />;
  if (!testimonials.length) return null;

  const t = testimonials[current];

  return (
    <section
      ref={ref}
      className={cn(
        'py-16 lg:py-24 bg-ivory overflow-hidden transition-all duration-700 ease-out',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
      )}
    >
      <div className="max-w-4xl mx-auto px-6">
        <div className="text-center mb-12">
          <span className="font-body text-xs tracking-[0.15em] uppercase text-taupe">
            Client Stories
          </span>
        </div>

        <div className="text-center mb-2">
          <span className="font-display text-8xl text-gold/30 leading-none select-none">"</span>
        </div>

        <blockquote key={t.id} className="text-center">
          <p className="font-display text-2xl lg:text-3xl text-charcoal font-light italic leading-relaxed mb-8">
            {t.body}
          </p>

          <footer className="flex flex-col items-center gap-3">
            {t.portrait && (
              <img
                src={t.portrait.thumb_url ?? t.portrait.url}
                alt={t.portrait.alt_text ?? t.client_name}
                className="w-14 h-14 rounded-full object-cover border-2 border-cream"
                loading="lazy"
                width={56}
                height={56}
              />
            )}
            <div className="text-center">
              <cite className="font-body font-semibold text-charcoal not-italic text-sm">
                {t.client_name}
              </cite>
              {t.client_role && (
                <p className="font-body text-xs text-taupe mt-0.5">{t.client_role}</p>
              )}
              {t.rating > 0 && <StarRating rating={t.rating} className="mt-1.5" />}
            </div>
          </footer>
        </blockquote>

        {testimonials.length > 1 && (
          <div className="flex justify-center items-center gap-3 mt-10">
            <button
              onClick={prev}
              aria-label="Previous testimonial"
              className="p-2.5 text-taupe hover:text-bronze transition-colors"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>

            <div className="flex gap-2">
              {testimonials.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setCurrent(i)}
                  aria-label={`Go to testimonial ${i + 1}`}
                  aria-current={i === current ? 'true' : undefined}
                  className={cn(
                    'h-1.5 rounded-full transition-all duration-300',
                    i === current ? 'w-4 bg-bronze' : 'w-1.5 bg-cream hover:bg-taupe'
                  )}
                />
              ))}
            </div>

            <button
              onClick={next}
              aria-label="Next testimonial"
              className="p-2.5 text-taupe hover:text-bronze transition-colors"
            >
              <ChevronRight className="h-5 w-5" />
            </button>
          </div>
        )}
      </div>
    </section>
  );
}
