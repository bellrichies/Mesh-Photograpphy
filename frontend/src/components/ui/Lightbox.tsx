import { useEffect, useCallback } from 'react';
import { X, ChevronLeft, ChevronRight } from 'lucide-react';
import type { GalleryPhoto } from '@/types/models';

interface LightboxProps {
  images: GalleryPhoto[];
  activeIndex: number;
  onClose: () => void;
  onPrev: () => void;
  onNext: () => void;
}

export default function Lightbox({ images, activeIndex, onClose, onPrev, onNext }: LightboxProps) {
  const active = images[activeIndex];

  const handleKey = useCallback((e: KeyboardEvent) => {
    if (e.key === 'Escape') onClose();
    if (e.key === 'ArrowLeft') onPrev();
    if (e.key === 'ArrowRight') onNext();
  }, [onClose, onPrev, onNext]);

  useEffect(() => {
    document.addEventListener('keydown', handleKey);
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', handleKey);
      document.body.style.overflow = '';
    };
  }, [handleKey]);

  if (!active) return null;

  return (
    <div
      className="fixed inset-0 z-50 bg-ink/95 flex items-center justify-center"
      role="dialog"
      aria-modal="true"
      aria-label="Gallery lightbox"
      onClick={onClose}
    >
      {/* Close */}
      <button
        onClick={onClose}
        className="absolute top-4 right-4 text-ivory/70 hover:text-ivory transition-colors z-10"
        aria-label="Close lightbox"
      >
        <X size={28} />
      </button>

      {/* Counter */}
      <p className="absolute top-4 left-1/2 -translate-x-1/2 text-ivory/60 text-sm font-body">
        {activeIndex + 1} / {images.length}
      </p>

      {/* Prev */}
      {images.length > 1 && (
        <button
          onClick={(e) => { e.stopPropagation(); onPrev(); }}
          className="absolute left-4 top-1/2 -translate-y-1/2 text-ivory/70 hover:text-ivory transition-colors z-10 bg-espresso/40 rounded-full p-2"
          aria-label="Previous image"
        >
          <ChevronLeft size={28} />
        </button>
      )}

      {/* Image */}
      <div className="max-w-[90vw] max-h-[85vh] flex flex-col items-center gap-3" onClick={(e) => e.stopPropagation()}>
        <img
          src={active.url}
          alt={active.alt_text ?? ''}
          className="max-w-full max-h-[80vh] object-contain"
          width={active.width ?? undefined}
          height={active.height ?? undefined}
          loading="eager"
        />
        {active.caption && (
          <p className="text-ivory/70 text-sm font-body text-center max-w-xl">{active.caption}</p>
        )}
      </div>

      {/* Next */}
      {images.length > 1 && (
        <button
          onClick={(e) => { e.stopPropagation(); onNext(); }}
          className="absolute right-4 top-1/2 -translate-y-1/2 text-ivory/70 hover:text-ivory transition-colors z-10 bg-espresso/40 rounded-full p-2"
          aria-label="Next image"
        >
          <ChevronRight size={28} />
        </button>
      )}
    </div>
  );
}
