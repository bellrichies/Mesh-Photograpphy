import { useState, useEffect, useCallback, useRef } from 'react';
import { Link } from 'react-router-dom';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import type { HeroSlide } from '@/types/models';

interface HeroCarouselProps {
  slides: HeroSlide[];
}

const INTERVAL = 6000;

export default function HeroCarousel({ slides }: HeroCarouselProps) {
  const [current, setCurrent]   = useState(0);
  const [paused, setPaused]     = useState(false);
  const [fading, setFading]     = useState(false);
  const timerRef                = useRef<ReturnType<typeof setTimeout>>();

  const go = useCallback((index: number) => {
    setFading(true);
    setTimeout(() => {
      setCurrent(index);
      setFading(false);
    }, 300);
  }, []);

  const prev = useCallback(() => go((current - 1 + slides.length) % slides.length), [current, go, slides.length]);
  const next = useCallback(() => go((current + 1) % slides.length), [current, go, slides.length]);

  useEffect(() => {
    if (slides.length <= 1 || paused) return;
    timerRef.current = setTimeout(next, INTERVAL);
    return () => clearTimeout(timerRef.current);
  }, [current, paused, next, slides.length]);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'ArrowLeft') prev();
      if (e.key === 'ArrowRight') next();
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [prev, next]);

  if (slides.length === 0) return null;

  const slide = slides[current];

  return (
    <section
      className="relative h-[90vh] min-h-[600px] overflow-hidden bg-espresso"
      aria-roledescription="carousel"
      aria-label="Hero slideshow"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      {/* Background */}
      <div className={cn('absolute inset-0 transition-opacity duration-300', fading ? 'opacity-0' : 'opacity-100')}>
        {slide.background_image ? (
          <img
            src={slide.background_image.url}
            alt=""
            width={1920}
            height={1080}
            loading="eager"
            fetchPriority="high"
            decoding="async"
            className="w-full h-full object-cover"
          />
        ) : (
          <div className="w-full h-full bg-espresso" />
        )}
        <div className="absolute inset-0 bg-gradient-to-b from-espresso/30 via-espresso/20 to-espresso/60" />
      </div>

      {/* Content */}
      <div
        className={cn(
          'relative z-10 h-full flex flex-col items-center justify-center text-center px-6 transition-opacity duration-300',
          fading ? 'opacity-0' : 'opacity-100'
        )}
        aria-live="polite"
        aria-atomic="true"
      >
        <h1 className="font-display text-5xl sm:text-6xl lg:text-7xl text-ivory font-light tracking-wide max-w-3xl leading-tight">
          {slide.title}
        </h1>
        {slide.subtitle && (
          <p className="font-body text-lg text-ivory/80 mt-4 max-w-xl">{slide.subtitle}</p>
        )}
        {slide.cta_label && slide.cta_url && (
          <Link
            to={slide.cta_url}
            className="mt-8 inline-block px-8 py-3 border border-ivory text-ivory font-body text-sm tracking-widest uppercase hover:bg-ivory hover:text-charcoal transition-colors duration-200"
          >
            {slide.cta_label}
          </Link>
        )}
      </div>

      {/* Prev / Next */}
      {slides.length > 1 && (
        <>
          <button
            onClick={prev}
            className="absolute left-4 top-1/2 -translate-y-1/2 z-20 text-ivory/60 hover:text-ivory transition-colors bg-espresso/30 rounded-full p-2"
            aria-label="Previous slide"
          >
            <ChevronLeft size={28} />
          </button>
          <button
            onClick={next}
            className="absolute right-4 top-1/2 -translate-y-1/2 z-20 text-ivory/60 hover:text-ivory transition-colors bg-espresso/30 rounded-full p-2"
            aria-label="Next slide"
          >
            <ChevronRight size={28} />
          </button>

          {/* Dots */}
          <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-20" role="tablist">
            {slides.map((_, i) => (
              <button
                key={i}
                role="tab"
                aria-selected={i === current}
                aria-label={`Slide ${i + 1}`}
                onClick={() => go(i)}
                className={cn(
                  'h-1.5 rounded-full transition-all duration-300',
                  i === current ? 'w-8 bg-ivory' : 'w-2 bg-ivory/40 hover:bg-ivory/70'
                )}
              />
            ))}
          </div>
        </>
      )}
    </section>
  );
}
