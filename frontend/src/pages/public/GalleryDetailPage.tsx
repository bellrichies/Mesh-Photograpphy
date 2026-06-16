import { useState } from 'react';
import { useParams, Link, Navigate } from 'react-router-dom';
import { ArrowLeft, ArrowRight, ChevronLeft } from 'lucide-react';
import { cn } from '@/utils/cn';
import PageMeta from '@/components/ui/PageMeta';
import Lightbox from '@/components/ui/Lightbox';
import { useGallery } from '@/api/galleries';
import type { GalleryMedia } from '@/types/models';

/*
 * Editorial masonry-style gallery layout inspired by ijeworks.com/portraits/
 *
 * The grid uses a 3-column base with images at varying aspect ratios to create
 * visual rhythm. Every 7th image spans 2 columns for an anchor composition.
 * On mobile the layout collapses to a 2-column tight grid.
 */

function EditorialGrid({
  images,
  onOpen,
}: {
  images: GalleryMedia[];
  onOpen: (i: number) => void;
}) {
  return (
    <div className="columns-2 md:columns-3 gap-px space-y-px">
      {images.map((img, idx) => (
        <button
          key={img.id}
          onClick={() => onOpen(idx)}
          className="group block w-full overflow-hidden focus-visible:outline focus-visible:outline-2 focus-visible:outline-bronze break-inside-avoid mb-px"
          aria-label={`View photo ${idx + 1}${img.alt_text ? ` - ${img.alt_text}` : ''}`}
        >
          <div className="relative overflow-hidden">
            <img
              src={img.thumb_url ?? img.url}
              alt={img.alt_text ?? ''}
              width={600}
              height={800}
              loading={idx < 6 ? 'eager' : 'lazy'}
              decoding="async"
              className="w-full h-auto object-cover transition-transform duration-700 will-change-transform group-hover:scale-[1.04]"
            />
            {/* Subtle dark scrim on hover */}
            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 pointer-events-none" />
          </div>
        </button>
      ))}
    </div>
  );
}

function StandardGrid({
  images,
  onOpen,
}: {
  images: GalleryMedia[];
  onOpen: (i: number) => void;
}) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      {images.map((img, idx) => (
        <button
          key={img.id}
          onClick={() => onOpen(idx)}
          className="group block w-full overflow-hidden bg-sand focus-visible:outline focus-visible:outline-2 focus-visible:outline-bronze"
          aria-label={`View photo ${idx + 1}${img.alt_text ? ` - ${img.alt_text}` : ''}`}
        >
          <img
            src={img.thumb_url ?? img.url}
            alt={img.alt_text ?? ''}
            width={640}
            height={800}
            loading={idx < 3 ? 'eager' : 'lazy'}
            decoding="async"
            className="aspect-[4/5] w-full object-cover transition-transform duration-700 will-change-transform group-hover:scale-[1.04]"
          />
        </button>
      ))}
    </div>
  );
}

export default function GalleryDetailPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: gallery, isLoading, isError } = useGallery(slug ?? '');
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);

  if (isLoading) {
    return (
      <div className="pt-24 site-container">
        <div className="h-8 w-48 bg-cream animate-pulse mb-10" />
        <div className="columns-2 md:columns-3 gap-px space-y-px">
          {Array.from({ length: 12 }).map((_, i) => (
            <div
              key={i}
              className={cn(
                'break-inside-avoid mb-px bg-cream animate-pulse',
                i % 5 === 0 ? 'aspect-[3/4]' : i % 3 === 0 ? 'aspect-square' : 'aspect-[4/5]'
              )}
            />
          ))}
        </div>
      </div>
    );
  }

  if (isError || !gallery) {
    return <Navigate to="/portfolio" replace />;
  }

  const images: GalleryMedia[] = gallery.media ?? [];
  const isPhotoPortfolio = slug === 'photo';

  const openLightbox  = (index: number) => setLightboxIndex(index);
  const closeLightbox = () => setLightboxIndex(null);
  const prevImage     = () => setLightboxIndex((i) => i === null ? null : (i - 1 + images.length) % images.length);
  const nextImage     = () => setLightboxIndex((i) => i === null ? null : (i + 1) % images.length);

  return (
    <>
      <PageMeta title={gallery.title} seo={gallery.seo} />

      <div className="pt-24 pb-20">
        {/* Header */}
        <div className="site-container mb-10">
          <Link
            to="/portfolio"
            className="inline-flex items-center gap-1.5 font-body text-sm text-taupe hover:text-bronze transition-colors mb-8"
          >
            <ChevronLeft size={16} /> Portfolio
          </Link>

          <div>
            {gallery.categories.length > 0 && (
              <p className="font-body text-xs tracking-widest uppercase text-bronze mb-2">
                {gallery.categories.map((c) => c.name).join(' / ')}
              </p>
            )}
            <h1 className="font-display text-4xl sm:text-5xl text-charcoal font-light">{gallery.title}</h1>
            {gallery.description && (
              <p className="font-body text-taupe mt-3 max-w-2xl">{gallery.description}</p>
            )}
          </div>
        </div>

        {/* Editorial image grid */}
        {images.length > 0 ? (
          <div className="site-container">
            {isPhotoPortfolio ? (
              <EditorialGrid images={images} onOpen={openLightbox} />
            ) : (
              <StandardGrid images={images} onOpen={openLightbox} />
            )}
          </div>
        ) : (
          <div className="site-container">
            <p className="text-center font-body text-taupe py-20">No photos in this gallery yet.</p>
          </div>
        )}

        {/* Prev / Next gallery */}
        {(gallery.prev_gallery || gallery.next_gallery) && (
          <div className="site-container flex items-center justify-between mt-16 pt-8 border-t border-cream">
            {gallery.prev_gallery ? (
              <Link
                to={`/portfolio/${gallery.prev_gallery.slug}`}
                className="flex items-center gap-2 font-body text-sm text-taupe hover:text-bronze transition-colors"
              >
                <ArrowLeft size={16} />
                <span className="font-display">{gallery.prev_gallery.title}</span>
              </Link>
            ) : <div />}

            {gallery.next_gallery ? (
              <Link
                to={`/portfolio/${gallery.next_gallery.slug}`}
                className="flex items-center gap-2 font-body text-sm text-taupe hover:text-bronze transition-colors"
              >
                <span className="font-display">{gallery.next_gallery.title}</span>
                <ArrowRight size={16} />
              </Link>
            ) : <div />}
          </div>
        )}
      </div>

      {/* Lightbox */}
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
