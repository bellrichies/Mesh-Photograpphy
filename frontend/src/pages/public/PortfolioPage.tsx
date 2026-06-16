import { useState } from 'react';
import { cn } from '@/utils/cn';
import GalleryCard from '@/components/public/GalleryCard';
import PageMeta from '@/components/ui/PageMeta';
import { useGalleries, useGalleryCategories } from '@/api/galleries';

export default function PortfolioPage() {
  const [activeCategory, setActiveCategory] = useState('');
  const [page, setPage] = useState(1);

  const { data: categories }    = useGalleryCategories();
  const { data, isLoading }     = useGalleries({ category: activeCategory || undefined, page, per_page: 12 });

  const galleries  = data?.data ?? [];
  const meta       = data?.meta;
  const cats       = categories ?? [];

  function selectCategory(slug: string) {
    setActiveCategory(slug);
    setPage(1);
  }

  return (
    <>
      <PageMeta title="Portfolio" description="Browse our photography galleries." />

      <div className="pt-24 pb-16">
        {/* Header */}
        <div className="site-container mb-12 text-center">
          <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Portfolio</p>
          <h1 className="font-display text-5xl text-charcoal font-light">Our Work</h1>
        </div>

        {/* Category tabs */}
        {cats.length > 0 && (
          <div className="site-container mb-10">
            <div className="flex gap-2 flex-wrap">
              <button
                onClick={() => selectCategory('')}
                className={cn(
                  'px-5 py-2 font-body text-sm border transition-colors',
                  activeCategory === ''
                    ? 'border-bronze bg-bronze text-ivory'
                    : 'border-cream text-taupe hover:border-bronze hover:text-bronze'
                )}
              >
                All
              </button>
              {cats.map((c) => (
                <button
                  key={c.slug}
                  onClick={() => selectCategory(c.slug)}
                  className={cn(
                    'px-5 py-2 font-body text-sm border transition-colors',
                    activeCategory === c.slug
                      ? 'border-bronze bg-bronze text-ivory'
                      : 'border-cream text-taupe hover:border-bronze hover:text-bronze'
                  )}
                >
                  {c.name}
                  {c.gallery_count !== undefined && (
                    <span className="ml-1.5 text-xs opacity-60">({c.gallery_count})</span>
                  )}
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Grid */}
        <div className="site-container">
          {isLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="aspect-[4/5] rounded-2xl bg-cream animate-pulse" />
              ))}
            </div>
          ) : galleries.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {galleries.map((g) => (
                <GalleryCard key={g.id} gallery={g} />
              ))}
            </div>
          ) : (
            <p className="text-center font-body text-taupe py-20">No galleries found.</p>
          )}

          {/* Pagination */}
          {meta && meta.last_page > 1 && (
            <div className="flex justify-center gap-2 mt-12">
              {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
                <button
                  key={p}
                  onClick={() => setPage(p)}
                  className={cn(
                    'w-9 h-9 font-body text-sm border transition-colors',
                    p === meta.current_page
                      ? 'border-bronze bg-bronze text-ivory'
                      : 'border-cream text-taupe hover:border-bronze hover:text-bronze'
                  )}
                >
                  {p}
                </button>
              ))}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
