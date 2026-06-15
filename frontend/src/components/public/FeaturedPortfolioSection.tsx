import { useState, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { Plus, ArrowRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import Lightbox from '@/components/ui/Lightbox';
import type { GalleryPhoto } from '@/types/models';

interface FeaturedPortfolioSectionProps {
  photos: GalleryPhoto[];
  isLoading?: boolean;
}

// ── Skeleton ─────────────────────────────────────────────────────────────────

function GridSkeleton() {
  return (
    <div className="grid grid-cols-3 lg:grid-cols-8 gap-px bg-ivory">
      {/* Mobile shows 9, desktop shows 16 */}
      {Array.from({ length: 16 }).map((_, i) => (
        <div
          key={i}
          className={cn(
            'aspect-[3/4] bg-cream animate-pulse',
            i >= 9 ? 'hidden lg:block' : ''
          )}
        />
      ))}
    </div>
  );
}

// ── Main component ────────────────────────────────────────────────────────────

export default function FeaturedPortfolioSection({
  photos,
  isLoading,
}: FeaturedPortfolioSectionProps) {
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);

  const openAt  = useCallback((i: number) => setLightboxIndex(i), []);
  const close   = useCallback(() => setLightboxIndex(null), []);
  const prev    = useCallback(() =>
    setLightboxIndex((i) => (i === null ? null : (i - 1 + photos.length) % photos.length)),
    [photos.length]
  );
  const next    = useCallback(() =>
    setLightboxIndex((i) => (i === null ? null : (i + 1) % photos.length)),
    [photos.length]
  );

  if (isLoading) {
    return (
      <section className="bg-ivory">
        <GridSkeleton />
        <div className="py-8 border-t border-cream flex justify-center">
          <div className="h-4 w-44 bg-cream animate-pulse rounded" />
        </div>
      </section>
    );
  }

  if (!photos.length) return null;

  return (
    <>
      <section className="bg-ivory">
        {/*
          Desktop (lg+): 8 columns x 2 rows = 16 tiles
          Mobile       : 3 columns x 3 rows = 9 tiles (items 9-15 hidden)
          gap-px + bg-ivory = 1 px ivory hairline between tiles
        */}
        <div className="grid grid-cols-3 lg:grid-cols-8 gap-px bg-ivory">
          {photos.map((photo, i) => (
            <button
              key={photo.id}
              onClick={() => openAt(i)}
              aria-label={`View photo ${i + 1}${photo.alt_text ? ` - ${photo.alt_text}` : ''}`}
              className={cn(
                'group relative aspect-[3/4] overflow-hidden bg-sand block w-full text-left',
                // Hide items beyond the 9th on small screens
                i >= 9 ? 'hidden lg:block' : ''
              )}
            >
              {/* Image */}
              <img
                src={photo.thumb_url ?? photo.url}
                alt={photo.alt_text ?? ''}
                className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 will-change-transform group-hover:scale-[1.06]"
                loading={i < 6 ? 'eager' : 'lazy'}
                width={300}
                height={400}
                decoding="async"
              />

              {/* Dark scrim on hover */}
              <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300" />

              {/* Circular plus icon - centered, fades and scales in */}
              <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <span className="flex items-center justify-center w-8 h-8 rounded-full bg-white scale-75 group-hover:scale-100 transition-transform duration-300 ease-out shadow-md">
                  <Plus size={13} className="text-charcoal" strokeWidth={2} />
                </span>
              </div>
            </button>
          ))}
        </div>

        {/* Footer row */}
        <div className="flex items-center justify-center py-8 border-t border-cream">
          <Link
            to="/portfolio"
            className="inline-flex items-center gap-2 font-body text-xs tracking-[0.2em] uppercase text-charcoal hover:text-bronze transition-colors duration-150"
          >
            View All Galleries
            <ArrowRight className="h-3.5 w-3.5" />
          </Link>
        </div>
      </section>

      {/* Lightbox - rendered at root level via React portal-like stacking */}
      {lightboxIndex !== null && (
        <Lightbox
          images={photos}
          activeIndex={lightboxIndex}
          onClose={close}
          onPrev={prev}
          onNext={next}
        />
      )}
    </>
  );
}
