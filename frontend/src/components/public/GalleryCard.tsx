import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import type { Gallery } from '@/types/models';

interface GalleryCardProps {
  gallery: Gallery;
  className?: string;
}

export default function GalleryCard({ gallery, className }: GalleryCardProps) {
  return (
    <Link
      to={`/portfolio/${gallery.slug}`}
      className={cn(
        'group block overflow-hidden focus:outline-none focus:ring-2 focus:ring-bronze focus:ring-offset-2',
        className
      )}
      aria-label={`View ${gallery.title} gallery${gallery.media_count > 0 ? `, ${gallery.media_count} images` : ''}`}
    >
      {/* Image — sharp edges per design spec */}
      <div className="relative aspect-[4/5] overflow-hidden bg-cream">
        {gallery.cover ? (
          <img
            src={gallery.cover.thumb_url ?? gallery.cover.url}
            alt={gallery.cover.alt_text ?? gallery.title}
            width={480}
            height={600}
            loading="lazy"
            decoding="async"
            className="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105"
          />
        ) : (
          <div className="w-full h-full bg-cream flex items-center justify-center">
            <span className="text-taupe/30 text-sm font-body">No image</span>
          </div>
        )}

        {/* Hover overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-charcoal/75 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 ease-in-out flex items-end p-5">
          <span className="font-body text-sm font-medium text-ivory flex items-center gap-1.5">
            View Gallery
            <ArrowRight className="h-4 w-4" />
          </span>
        </div>
      </div>

      {/* Card footer */}
      <div className="pt-3 pb-1 px-1">
        <h3 className="font-display text-lg text-charcoal group-hover:text-bronze transition-colors duration-200 leading-snug line-clamp-1">
          {gallery.title}
        </h3>
        <p className="font-body text-xs text-taupe mt-0.5">
          {gallery.categories.map((c) => c.name).join(' · ')}
          {gallery.media_count > 0 && ` · ${gallery.media_count} images`}
        </p>
      </div>
    </Link>
  );
}
