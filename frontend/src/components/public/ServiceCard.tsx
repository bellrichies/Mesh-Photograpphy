import { Link } from 'react-router-dom';
import { ArrowUpRight } from 'lucide-react';
import type { Service } from '@/types/models';

interface ServiceCardProps {
  service: Service;
  /** Zero-based index used for the numbered badge. */
  index: number;
}

/**
 * Editorial service card used on both the homepage teaser and the full
 * services page so the two stay visually consistent.
 */
export default function ServiceCard({ service, index }: ServiceCardProps) {
  const num = String(index + 1).padStart(2, '0');

  return (
    <Link to={`/services/${service.slug}`} className="group block">
      {/* Image — tall portrait, full bleed */}
      <div className="relative overflow-hidden bg-charcoal" style={{ aspectRatio: '3/4' }}>
        {service.cover ? (
          <img
            src={service.cover.url}
            alt={service.cover.alt_text ?? service.title}
            className="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.04]"
            loading="lazy"
            width={600}
            height={800}
          />
        ) : (
          <div className="w-full h-full bg-charcoal" />
        )}

        {/* Dark overlay on hover */}
        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-500" />

        {/* Number badge — top-left */}
        <span className="absolute top-5 left-5 font-body text-xs tracking-[0.2em] text-ivory/70 select-none">
          {num}
        </span>

        {/* Arrow icon — bottom-right, slides in on hover */}
        <div className="absolute bottom-5 right-5 w-10 h-10 bg-bronze flex items-center justify-center opacity-0 translate-y-3 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
          <ArrowUpRight size={18} className="text-ivory" />
        </div>
      </div>

      {/* Text block */}
      <div className="pt-5 pb-2">
        <div className="flex items-start justify-between gap-4">
          <div>
            <h3 className="font-display text-2xl text-charcoal font-light leading-snug group-hover:text-bronze transition-colors duration-200">
              {service.title}
            </h3>
            {service.price_display && (
              <p className="font-body text-xs tracking-[0.12em] uppercase text-taupe mt-1.5">
                {service.price_display}
              </p>
            )}
          </div>
        </div>

        {service.short_description && (
          <p className="font-body text-sm text-taupe leading-relaxed mt-3 line-clamp-2">
            {service.short_description}
          </p>
        )}
      </div>
    </Link>
  );
}
