import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { Pause, Play } from 'lucide-react';
import { cn } from '@/utils/cn';
import type { HeroSlide } from '@/types/models';

interface HeroCarouselProps {
  slides: HeroSlide[];
  isLoading?: boolean;
  siteName?: string;
}

const INTERVAL_MS = 6000;

function HeroSkeleton() {
  return (
    <div className="h-[90vh] min-h-[620px] w-full bg-charcoal-light animate-pulse flex items-end pb-24">
      <div className="site-container">
        <div className="h-3 w-28 bg-ivory/20 rounded mb-4" />
        <div className="h-14 w-2/3 bg-ivory/20 rounded mb-3" />
        <div className="h-14 w-1/2 bg-ivory/20 rounded mb-8" />
        <div className="flex gap-4">
          <div className="h-11 w-40 bg-ivory/20 rounded" />
          <div className="h-11 w-32 bg-ivory/10 rounded" />
        </div>
      </div>
    </div>
  );
}

export default function HeroCarousel({ slides, isLoading, siteName }: HeroCarouselProps) {
  const prefersReducedMotion =
    typeof window !== 'undefined'
      ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
      : false;

  const [current, setCurrent] = useState(0);
  const [manualPause, setManualPause] = useState(prefersReducedMotion);
  const [hoverPause, setHoverPause] = useState(false);
  const [fading, setFading] = useState(false);

  const isPaused = manualPause || hoverPause;

  const go = useCallback((index: number) => {
    setFading(true);
    setTimeout(() => {
      setCurrent(index);
      setFading(false);
    }, 400);
  }, []);

  const prev = useCallback(
    () => go((current - 1 + slides.length) % slides.length),
    [current, go, slides.length]
  );
  const next = useCallback(
    () => go((current + 1) % slides.length),
    [current, go, slides.length]
  );

  useEffect(() => {
    if (slides.length <= 1 || isPaused) return;
    const timer = setInterval(() => {
      setCurrent((c) => (c + 1) % slides.length);
    }, INTERVAL_MS);
    return () => clearInterval(timer);
  }, [isPaused, slides.length]);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'ArrowLeft') prev();
      if (e.key === 'ArrowRight') next();
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [prev, next]);

  if (isLoading) return <HeroSkeleton />;

  // No slides configured or API unreachable — render a fallback dark hero so the
  // navbar (which is transparent/ivory-text when not scrolled) stays visible.
  if (!slides.length) {
    return (
      <section className="relative h-[90vh] min-h-[620px] overflow-hidden bg-espresso flex items-end pb-24 pt-[72px]">
        <div className="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/55" />
        <div className="relative z-10 site-container">
          <div className="max-w-2xl">
            <h1 className="font-display text-5xl lg:text-7xl text-ivory font-light leading-tight mb-6">
              {siteName ?? 'Mesh Photography'}
            </h1>
            <div className="flex flex-wrap gap-4">
              <Link
                to="/portfolio"
                className="px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-wide hover:bg-bronze-light transition-colors duration-150"
              >
                View Portfolio
              </Link>
              <Link
                to="/contact"
                className="px-8 py-3 border border-ivory/60 text-ivory font-body text-sm tracking-wide hover:border-ivory hover:bg-ivory/10 transition-colors duration-150"
              >
                Get in Touch
              </Link>
            </div>
          </div>
        </div>
      </section>
    );
  }

  const slide = slides[current];

  return (
    <section
      className="relative h-[90vh] min-h-[620px] overflow-hidden bg-espresso"
      role="region"
      aria-label="Featured photography"
      aria-roledescription="carousel"
      onMouseEnter={() => setHoverPause(true)}
      onMouseLeave={() => setHoverPause(false)}
    >
      {/* Background */}
      <div
        className={cn(
          'absolute inset-0 transition-opacity duration-700 ease-in-out',
          fading ? 'opacity-0' : 'opacity-100'
        )}
      >
        {slide.background_image ? (
          <img
            src={slide.background_image.url}
            alt=""
            width={1920}
            height={1080}
            loading="eager"
            fetchpriority="high"
            decoding="async"
            className="w-full h-full object-cover object-center"
          />
        ) : (
          <div className="w-full h-full bg-espresso" />
        )}
        <div className="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/55" />
      </div>

      {/* Content — bottom-left on desktop, bottom-center on mobile */}
      <div
        className={cn(
          'relative z-10 flex h-full items-end pb-24 lg:pb-20 pt-[72px] transition-opacity duration-500 ease-in-out',
          fading ? 'opacity-0' : 'opacity-100'
        )}
      >
          <div className="site-container">
          <div className="max-w-2xl">
            {slide.subtitle && (
              <p className="font-body text-sm tracking-[0.2em] uppercase text-gold mb-4 opacity-90">
                {slide.subtitle}
              </p>
            )}
            <h1 className="font-display text-5xl lg:text-7xl text-ivory font-light leading-tight mb-6">
              {slide.title}
            </h1>
            <div className="flex flex-wrap gap-4">
              {slide.cta_label && slide.cta_url && (
                <Link
                  to={slide.cta_url}
                  className="px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-wide hover:bg-bronze-light transition-colors duration-150"
                >
                  {slide.cta_label}
                </Link>
              )}
              <Link
                to="/contact"
                className="px-8 py-3 border border-ivory/60 text-ivory font-body text-sm tracking-wide hover:border-ivory hover:bg-ivory/10 transition-colors duration-150"
              >
                Get in Touch
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Carousel controls — dot indicators + pause/play only (no arrows) */}
      {slides.length > 1 && (
        <>
          <div className="absolute bottom-7 left-1/2 -translate-x-1/2 z-20 flex items-center gap-4">
            <div className="flex gap-2">
              {slides.map((_, i) => (
                <button
                  key={i}
                  onClick={() => go(i)}
                  aria-label={`Go to slide ${i + 1}`}
                  aria-current={i === current ? 'true' : undefined}
                  className={cn(
                    'h-1.5 rounded-full transition-all duration-300',
                    i === current ? 'w-8 bg-ivory' : 'w-2 bg-ivory/40 hover:bg-ivory/70'
                  )}
                />
              ))}
            </div>
            {!prefersReducedMotion && (
              <button
                onClick={() => setManualPause((v) => !v)}
                aria-label={manualPause ? 'Resume carousel' : 'Pause carousel'}
                className="p-1.5 text-ivory/60 hover:text-ivory transition-colors"
              >
                {manualPause ? <Play size={13} /> : <Pause size={13} />}
              </button>
            )}
          </div>
        </>
      )}

      <div aria-live="polite" className="sr-only">
        {slide.title}
      </div>
    </section>
  );
}
