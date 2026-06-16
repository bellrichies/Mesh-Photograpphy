import { Link } from 'react-router-dom';
import PageMeta from '@/components/ui/PageMeta';
import { useServices } from '@/api/services';
import ServiceCard from '@/components/public/ServiceCard';

export default function ServicesPage() {
  const { data: services, isLoading } = useServices();
  const list = services ?? [];

  return (
    <>
      <PageMeta title="Services" description="Professional photography services for weddings, portraits, and more." />

      <div className="pt-24 pb-20">
        <div className="site-container">
          {/* Header */}
          <div className="text-center mb-16">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Services</p>
            <h1 className="font-display text-5xl text-charcoal font-light">What We Offer</h1>
            <p className="font-body text-taupe mt-4 max-w-xl mx-auto">
              Tailored photography experiences for every moment.
            </p>
          </div>

          {isLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
              {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} className="space-y-4">
                  <div className="bg-cream animate-pulse" style={{ aspectRatio: '3/4' }} />
                  <div className="h-6 w-3/5 bg-cream animate-pulse rounded" />
                  <div className="h-3 w-full bg-cream animate-pulse rounded" />
                </div>
              ))}
            </div>
          ) : list.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
              {list.map((s, i) => (
                <ServiceCard key={s.id} service={s} index={i} />
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
