import { useCallback, useState } from 'react';
import { Link } from 'react-router-dom';
import { ChevronLeft } from 'lucide-react';
import PageMeta from '@/components/ui/PageMeta';
import Lightbox from '@/components/ui/Lightbox';
import { useGalleryPhotos } from '@/api/galleries';
import type { GalleryPhoto } from '@/types/models';

function PhotoGrid({
  images,
  onOpen,
}: {
  images: GalleryPhoto[];
  onOpen: (index: number) => void;
}) {
  return (
    <div className="columns-2 md:columns-3 xl:columns-4 gap-px space-y-px">
      {images.map((image, index) => (
        <button
          key={image.id}
          type="button"
          onClick={() => onOpen(index)}
          className="group block w-full overflow-hidden bg-sand break-inside-avoid mb-px focus-visible:outline focus-visible:outline-2 focus-visible:outline-bronze"
          aria-label={`View photo ${index + 1}${image.alt_text ? ` - ${image.alt_text}` : ''}`}
        >
          <img
            src={image.thumb_url ?? image.url}
            alt={image.alt_text ?? ''}
            width={image.width ?? 640}
            height={image.height ?? 820}
            loading={index < 8 ? 'eager' : 'lazy'}
            decoding="async"
            className="w-full h-auto object-cover transition-transform duration-700 will-change-transform group-hover:scale-[1.035]"
          />
        </button>
      ))}
    </div>
  );
}

function PhotoGridSkeleton() {
  return (
    <div className="columns-2 md:columns-3 xl:columns-4 gap-px space-y-px">
      {Array.from({ length: 18 }).map((_, index) => (
        <div
          key={index}
          className="break-inside-avoid mb-px bg-cream animate-pulse"
          style={{ aspectRatio: index % 4 === 0 ? '3 / 4' : index % 3 === 0 ? '1 / 1' : '4 / 5' }}
        />
      ))}
    </div>
  );
}

export default function PhotoPortfolioPage() {
  const { data: photos, isLoading } = useGalleryPhotos(80);
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);
  const images = photos ?? [];

  const openLightbox = useCallback((index: number) => setLightboxIndex(index), []);
  const closeLightbox = useCallback(() => setLightboxIndex(null), []);
  const prevImage = useCallback(() => {
    setLightboxIndex((index) => index === null ? null : (index - 1 + images.length) % images.length);
  }, [images.length]);
  const nextImage = useCallback(() => {
    setLightboxIndex((index) => index === null ? null : (index + 1) % images.length);
  }, [images.length]);

  return (
    <>
      <PageMeta
        title="Photo Portfolio"
        description="A curated photography-focused view of recent Mesh Photography work."
      />

      <div className="pt-24 pb-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
          <Link
            to="/portfolio"
            className="inline-flex items-center gap-1.5 font-body text-sm text-taupe hover:text-bronze transition-colors mb-8"
          >
            <ChevronLeft size={16} /> Portfolio
          </Link>

          <div className="max-w-2xl">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-2">Photo Portfolio</p>
            <h1 className="font-display text-4xl sm:text-5xl text-charcoal font-light">Photography in Focus</h1>
            <p className="font-body text-taupe mt-3">
              A dense, image-led view of published work, arranged for visual rhythm with minimal framing.
            </p>
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {isLoading ? (
            <PhotoGridSkeleton />
          ) : images.length > 0 ? (
            <PhotoGrid images={images} onOpen={openLightbox} />
          ) : (
            <p className="text-center font-body text-taupe py-20">No photos are available yet.</p>
          )}
        </div>
      </div>

      {lightboxIndex !== null && (
        <Lightbox
          images={images}
          activeIndex={lightboxIndex}
          onClose={closeLightbox}
          onPrev={prevImage}
          onNext={nextImage}
        />
      )}
    </>
  );
}
