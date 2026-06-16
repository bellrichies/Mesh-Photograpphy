import { type CSSProperties } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import { useServices } from '@/api/services';
import ServiceCard from './ServiceCard';

function ServicesTeaserSkeleton() {
  return (
    <section className="bg-ivory py-20 lg:py-28">
      <div className="site-container">
        <div className="flex items-end justify-between mb-14">
          <div>
            <div className="h-2.5 w-20 bg-cream animate-pulse rounded mb-3" />
            <div className="h-10 w-52 bg-cream animate-pulse rounded" />
          </div>
          <div className="h-4 w-28 bg-cream animate-pulse rounded hidden md:block" />
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="space-y-4">
              <div className="bg-cream animate-pulse" style={{ aspectRatio: '3/4' }} />
              <div className="h-6 w-3/5 bg-cream animate-pulse rounded" />
              <div className="h-3 w-full bg-cream animate-pulse rounded" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export default function ServicesTeaserSection() {
  const { data: services, isLoading } = useServices();
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  const displayServices = (services ?? []).slice(0, 3);

  if (isLoading) return <ServicesTeaserSkeleton />;
  if (!displayServices.length) return null;

  return (
    <section
      ref={ref}
      className={cn('bg-ivory py-20 lg:py-28', isVisible && 'is-revealed')}
    >
      <div className="site-container">
        {/* Section header */}
        <div className="reveal-up flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-14 lg:mb-16">
          <div>
            <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-3">
              What We Offer
            </span>
            <h2 className="font-display text-4xl lg:text-5xl text-charcoal font-light leading-tight">
              Our Services
            </h2>
          </div>
          <Link
            to="/services"
            className="inline-flex items-center gap-2 font-body text-sm text-bronze border-b border-bronze pb-0.5 hover:text-bronze-dark hover:border-bronze-dark transition-colors duration-150 self-start sm:self-auto shrink-0"
          >
            View all services
            <ArrowUpRight size={14} />
          </Link>
        </div>

        {/* Service cards grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          {displayServices.map((s, i) => (
            <div
              key={s.id}
              className="reveal-up"
              style={{ '--reveal-delay': `${100 + i * 110}ms` } as CSSProperties}
            >
              <ServiceCard service={s} index={i} />
            </div>
          ))}
        </div>

        {/* Bottom CTA */}
        <div
          className="reveal-up mt-16 pt-10 border-t border-cream flex flex-col sm:flex-row items-center justify-between gap-4"
          style={{ '--reveal-delay': '240ms' } as CSSProperties}
        >
          <p className="font-body text-sm text-taupe">
            Not sure which package fits your vision?
          </p>
          <Link
            to="/contact"
            className="inline-block px-8 py-3 bg-charcoal text-ivory font-body text-xs tracking-[0.15em] uppercase transition-all duration-200 hover:bg-bronze hover:-translate-y-0.5 hover:shadow-soft"
          >
            Let&apos;s Talk
          </Link>
        </div>
      </div>
    </section>
  );
}
