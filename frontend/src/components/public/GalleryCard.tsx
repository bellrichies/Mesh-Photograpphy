import { Link } from 'react-router-dom';
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
      className={cn('group block overflow-hidden rounded-2xl bg-cream', className)}
      aria-label={gallery.title}
    >
      <div className="relative overflow-hidden aspect-[4/5]">
        {gallery.cover ? (
          <img
            src={gallery.cover.url}
            alt={gallery.cover.alt_text ?? gallery.title}
            width={480}
            height={600}
            loading="lazy"
            decoding="async"
            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
          />
        ) : (
          <div className="w-full h-full bg-cream flex items-center justify-center">
            <span className="text-taupe text-sm font-body">No image</span>
          </div>
        )}

        <div className="absolute inset-0 bg-gradient-to-t from-espresso/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

        <div className="absolute bottom-0 left-0 right-0 p-5 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
          <p className="font-body text-sm text-ivory/80">
            {gallery.media_count > 0 ? `${gallery.media_count} photos` : gallery.categories[0]?.name ?? ''}
          </p>
        </div>
      </div>

      <div className="px-4 py-3">
        <h3 className="font-display text-lg text-charcoal group-hover:text-bronze transition-colors line-clamp-1">
          {gallery.title}
        </h3>
        {gallery.categories.length > 0 && (
          <p className="font-body text-xs text-taupe mt-0.5 uppercase tracking-wide">
            {gallery.categories.map((c) => c.name).join(', ')}
          </p>
        )}
      </div>
    </Link>
  );
}
