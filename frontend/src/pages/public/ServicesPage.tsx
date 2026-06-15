import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import PageMeta from '@/components/ui/PageMeta';
import { useServices } from '@/api/services';

export default function ServicesPage() {
  const { data: services, isLoading } = useServices();
  const list = services ?? [];

  return (
    <>
      <PageMeta title="Services" description="Professional photography services for weddings, portraits, and more." />

      <div className="pt-24 pb-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Header */}
          <div className="text-center mb-16">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Services</p>
            <h1 className="font-display text-5xl text-charcoal font-light">What We Offer</h1>
            <p className="font-body text-taupe mt-4 max-w-xl mx-auto">
              Tailored photography experiences for every moment.
            </p>
          </div>

          {isLoading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} className="bg-cream animate-pulse h-80" />
              ))}
            </div>
          ) : list.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {list.map((s) => (
                <Link
                  key={s.id}
                  to={`/services/${s.slug}`}
                  className="group bg-ivory border border-cream overflow-hidden hover:shadow-soft transition-shadow"
                >
                  {s.cover && (
                    <div className="aspect-video overflow-hidden">
                      <img
                        src={s.cover.url}
                        alt={s.cover.alt_text ?? s.title}
                        width={480}
                        height={270}
                        loading="lazy"
                        decoding="async"
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>
                  )}
                  <div className="p-6">
                    <h2 className="font-display text-2xl text-charcoal group-hover:text-bronze transition-colors">{s.title}</h2>
                    {s.short_description && (
                      <p className="font-body text-sm text-taupe mt-2 line-clamp-3">{s.short_description}</p>
                    )}
                    <div className="flex items-center justify-between mt-4">
                      {s.price_display && (
                        <span className="font-body text-xs text-bronze tracking-wide">{s.price_display}</span>
                      )}
                      <span className="inline-flex items-center gap-1 font-body text-xs text-taupe group-hover:text-bronze transition-colors ml-auto">
                        Learn more <ArrowRight size={13} />
                      </span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <p className="text-center font-body text-taupe py-20">No services available.</p>
          )}

          {/* CTA */}
          <div className="mt-20 bg-ivory-warm p-10 text-center">
            <h2 className="font-display text-3xl text-charcoal">Ready to work together?</h2>
            <p className="font-body text-taupe mt-3">Let's discuss your vision and find the perfect package.</p>
            <div className="flex gap-4 justify-center mt-6">
              <Link to="/booking" className="px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors">
                Book Now
              </Link>
              <Link to="/contact" className="px-8 py-3 border border-charcoal text-charcoal font-body text-sm tracking-widest uppercase hover:bg-charcoal hover:text-ivory transition-colors">
                Ask a Question
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
