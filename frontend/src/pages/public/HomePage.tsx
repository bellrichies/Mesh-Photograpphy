import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import HeroCarousel from '@/components/public/HeroCarousel';
import GalleryCard from '@/components/public/GalleryCard';
import PageMeta from '@/components/ui/PageMeta';
import { useHeroSlides } from '@/api/hero-slides';
import { useGalleries } from '@/api/galleries';
import { useTestimonials } from '@/api/testimonials';
import { useServices } from '@/api/services';
import { usePublicSettings } from '@/api/settings';

function SectionHeading({ eyebrow, title, subtitle }: { eyebrow?: string; title: string; subtitle?: string }) {
  return (
    <div className="text-center mb-12">
      {eyebrow && <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">{eyebrow}</p>}
      <h2 className="font-display text-4xl sm:text-5xl text-charcoal font-light">{title}</h2>
      {subtitle && <p className="font-body text-taupe mt-3 max-w-xl mx-auto">{subtitle}</p>}
    </div>
  );
}

export default function HomePage() {
  const { data: settings }     = usePublicSettings();
  const { data: slidesData }   = useHeroSlides();
  const { data: galleriesData } = useGalleries({ featured: true, per_page: 6 });
  const { data: testimonials } = useTestimonials();
  const { data: services }     = useServices();

  const slides      = slidesData ?? [];
  const galleries   = galleriesData?.data ?? [];
  const reviews     = testimonials ?? [];
  const serviceList = services ?? [];

  return (
    <>
      <PageMeta
        title={settings?.site.name}
        description={settings?.seo.default_description ?? undefined}
        siteName={settings?.site.name}
      />

      {/* Hero */}
      <HeroCarousel slides={slides} />

      {/* Featured Galleries */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <SectionHeading
          eyebrow="Portfolio"
          title="Recent Work"
          subtitle="A selection of stories we've had the privilege of telling."
        />
        {galleries.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {galleries.map((g) => (
              <GalleryCard key={g.id} gallery={g} />
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className="aspect-[4/5] rounded-2xl bg-cream animate-pulse" />
            ))}
          </div>
        )}
        <div className="text-center mt-12">
          <Link
            to="/portfolio"
            className="inline-flex items-center gap-2 font-body text-sm text-bronze hover:text-bronze-dark transition-colors tracking-wide"
          >
            View all galleries <ArrowRight size={16} />
          </Link>
        </div>
      </section>

      {/* Services Strip */}
      {serviceList.length > 0 && (
        <section className="bg-ivory-warm py-20">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <SectionHeading eyebrow="Services" title="What We Offer" />
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
              {serviceList.slice(0, 3).map((s) => (
                <Link
                  key={s.id}
                  to={`/services/${s.slug}`}
                  className="group bg-ivory rounded-2xl p-7 shadow-soft hover:shadow-md transition-shadow"
                >
                  {s.cover && (
                    <div className="aspect-video overflow-hidden rounded-xl mb-5">
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
                  <h3 className="font-display text-xl text-charcoal group-hover:text-bronze transition-colors">{s.title}</h3>
                  {s.short_description && (
                    <p className="font-body text-sm text-taupe mt-2 line-clamp-2">{s.short_description}</p>
                  )}
                  {s.price_display && (
                    <p className="font-body text-xs text-bronze mt-3 tracking-wide">{s.price_display}</p>
                  )}
                </Link>
              ))}
            </div>
            <div className="text-center mt-10">
              <Link
                to="/services"
                className="inline-flex items-center gap-2 font-body text-sm text-bronze hover:text-bronze-dark transition-colors"
              >
                View all services <ArrowRight size={16} />
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* Testimonials */}
      {reviews.length > 0 && (
        <section className="bg-espresso py-24">
          <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <SectionHeading eyebrow="Testimonials" title="Kind Words" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {reviews.slice(0, 4).map((t) => (
                <blockquote key={t.id} className="bg-charcoal-light rounded-2xl p-7">
                  <div className="flex gap-1 mb-4">
                    {Array.from({ length: t.rating }).map((_, i) => (
                      <span key={i} className="text-gold text-sm">★</span>
                    ))}
                  </div>
                  <p className="font-body text-ivory/80 text-sm leading-relaxed">"{t.body}"</p>
                  <footer className="mt-5 flex items-center gap-3">
                    {t.portrait && (
                      <img
                        src={t.portrait.url}
                        alt={t.client_name}
                        width={40}
                        height={40}
                        loading="lazy"
                        className="w-10 h-10 rounded-full object-cover"
                      />
                    )}
                    <div>
                      <cite className="font-body text-sm text-ivory not-italic">{t.client_name}</cite>
                      {t.client_role && (
                        <p className="font-body text-xs text-taupe">{t.client_role}</p>
                      )}
                    </div>
                  </footer>
                </blockquote>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      <section className="py-24 bg-ivory">
        <div className="max-w-2xl mx-auto px-4 text-center">
          <h2 className="font-display text-4xl text-charcoal font-light">Ready to tell your story?</h2>
          <p className="font-body text-taupe mt-4">
            Let's create images that last a lifetime. Get in touch to check availability.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center mt-8">
            <Link
              to="/booking"
              className="px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors"
            >
              Book a Session
            </Link>
            <Link
              to="/contact"
              className="px-8 py-3 border border-charcoal text-charcoal font-body text-sm tracking-widest uppercase hover:bg-charcoal hover:text-ivory transition-colors"
            >
              Get in Touch
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
