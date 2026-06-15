import { useParams, Link, Navigate } from 'react-router-dom';
import { ChevronLeft } from 'lucide-react';
import PageMeta from '@/components/ui/PageMeta';
import { useService } from '@/api/services';

export default function ServiceDetailPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: service, isLoading, isError } = useService(slug ?? '');

  if (isLoading) {
    return (
      <div className="pt-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="h-96 bg-cream rounded-2xl animate-pulse mb-8" />
        <div className="space-y-4">
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="h-4 bg-cream rounded animate-pulse" />
          ))}
        </div>
      </div>
    );
  }

  if (isError || !service) return <Navigate to="/services" replace />;

  return (
    <>
      <PageMeta title={service.title} seo={service.seo} />

      <div className="pt-24 pb-20">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <Link
            to="/services"
            className="inline-flex items-center gap-1.5 font-body text-sm text-taupe hover:text-bronze transition-colors mb-8"
          >
            <ChevronLeft size={16} /> Services
          </Link>

          {service.cover && (
            <div className="aspect-video overflow-hidden rounded-2xl mb-10">
              <img
                src={service.cover.url}
                alt={service.cover.alt_text ?? service.title}
                width={900}
                height={506}
                loading="eager"
                fetchpriority="high"
                decoding="async"
                className="w-full h-full object-cover"
              />
            </div>
          )}

          <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Service</p>
          <h1 className="font-display text-4xl sm:text-5xl text-charcoal font-light mb-2">{service.title}</h1>

          {service.price_display && (
            <p className="font-body text-lg text-bronze mb-6">{service.price_display}</p>
          )}

          {service.short_description && (
            <p className="font-body text-lg text-taupe leading-relaxed mb-8">{service.short_description}</p>
          )}

          {service.description && (
            <div className="font-body text-charcoal leading-relaxed whitespace-pre-wrap border-t border-cream pt-8">
              {service.description}
            </div>
          )}

          <div className="mt-12 flex gap-4">
            <Link
              to="/booking"
              className="px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors"
            >
              Book This Service
            </Link>
            <Link
              to="/contact"
              className="px-8 py-3 border border-charcoal text-charcoal font-body text-sm tracking-widest uppercase hover:bg-charcoal hover:text-ivory transition-colors"
            >
              Ask a Question
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}
