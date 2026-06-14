import { useState } from 'react';
import { useParams, Link, Navigate } from 'react-router-dom';
import { ArrowLeft, ArrowRight, ChevronLeft } from 'lucide-react';
import PageMeta from '@/components/ui/PageMeta';
import Lightbox from '@/components/ui/Lightbox';
import { useGallery } from '@/api/galleries';
import type { GalleryMedia } from '@/types/models';

export default function GalleryDetailPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: gallery, isLoading, isError } = useGallery(slug ?? '');
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);

  if (isLoading) {
    return (
      <div className="pt-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="h-8 w-48 bg-cream rounded animate-pulse mb-10" />
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
          {Array.from({ length: 12 }).map((_, i) => (
            <div key={i} className="aspect-square bg-cream rounded-xl animate-pulse" />
          ))}
        </div>
      </div>
    );
  }

  if (isError || !gallery) {
    return <Navigate to="/portfolio" replace />;
  }

  const images: GalleryMedia[] = gallery.media ?? [];

  const openLightbox = (index: number) => setLightboxIndex(index);
  const closeLightbox = () => setLightboxIndex(null);
  const prevImage = () => setLightboxIndex((i) => i === null ? null : (i - 1 + images.length) % images.length);
  const nextImage = () => setLightboxIndex((i) => i === null ? null : (i + 1) % images.length);

  return (
    <>
      <PageMeta title={gallery.title} seo={gallery.seo} />

      <div className="pt-24 pb-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Back */}
          <Link
            to="/portfolio"
            className="inline-flex items-center gap-1.5 font-body text-sm text-taupe hover:text-bronze transition-colors mb-8"
          >
            <ChevronLeft size={16} /> Portfolio
          </Link>

          {/* Header */}
          <div className="mb-10">
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

          {/* Grid */}
          {images.length > 0 ? (
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
              {images.map((img, idx) => (
                <button
                  key={img.id}
                  onClick={() => openLightbox(idx)}
                  className="group overflow-hidden rounded-xl aspect-square focus-visible:outline focus-visible:outline-2 focus-visible:outline-bronze"
                  aria-label={`View photo ${idx + 1}`}
                >
                  <img
                    src={img.thumb_url ?? img.url}
                    alt={img.alt_text ?? ''}
                    width={400}
                    height={400}
                    loading={idx < 8 ? 'eager' : 'lazy'}
                    decoding="async"
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  />
                </button>
              ))}
            </div>
          ) : (
            <p className="text-center font-body text-taupe py-20">No photos in this gallery yet.</p>
          )}

          {/* Prev / Next gallery */}
          {(gallery.prev_gallery || gallery.next_gallery) && (
            <div className="flex items-center justify-between mt-16 pt-8 border-t border-cream">
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
