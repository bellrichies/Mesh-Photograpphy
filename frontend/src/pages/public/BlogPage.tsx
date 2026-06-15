import { useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { Search, Calendar } from 'lucide-react';
import { cn } from '@/utils/cn';
import PageMeta from '@/components/ui/PageMeta';
import { useBlogPosts, useBlogCategories, useBlogTags } from '@/api/blog';

function BlogPostCard({ post }: { post: { id: number; title: string; slug: string; excerpt: string | null; cover: { url: string; alt_text: string | null } | null; published_at: string | null; categories: { name: string; slug: string }[] } }) {
  return (
    <article className="group">
      <Link to={`/blog/${post.slug}`}>
        {post.cover ? (
          <div className="aspect-[4/5] overflow-hidden mb-4">
            <img
              src={post.cover.url}
              alt={post.cover.alt_text ?? post.title}
              width={480}
              height={600}
              loading="lazy"
              decoding="async"
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
          </div>
        ) : (
          <div className="aspect-[4/5] bg-cream mb-4" />
        )}

        {post.categories.length > 0 && (
          <p className="font-body text-xs tracking-widest uppercase text-bronze mb-2">
            {post.categories[0].name}
          </p>
        )}

        <h2 className="font-display text-2xl text-charcoal group-hover:text-bronze transition-colors leading-snug">
          {post.title}
        </h2>

        {post.excerpt && (
          <p className="font-body text-sm text-taupe mt-2 line-clamp-2">{post.excerpt}</p>
        )}

        {post.published_at && (
          <p className="font-body text-xs text-taupe mt-3 flex items-center gap-1">
            <Calendar size={12} />
            {new Date(post.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
          </p>
        )}
      </Link>
    </article>
  );
}

export default function BlogPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [searchInput, setSearchInput]   = useState(searchParams.get('q') ?? '');
  const page     = parseInt(searchParams.get('page') ?? '1', 10);
  const category = searchParams.get('category') ?? '';
  const tag      = searchParams.get('tag') ?? '';
  const q        = searchParams.get('q') ?? '';

  const { data, isLoading } = useBlogPosts({ category: category || undefined, tag: tag || undefined, q: q || undefined, page, per_page: 9 });
  const { data: categories } = useBlogCategories();
  const { data: tags }       = useBlogTags();

  const posts    = data?.data ?? [];
  const meta     = data?.meta;
  const catList  = categories ?? [];
  const tagList  = tags ?? [];

  function applySearch(e: React.FormEvent) {
    e.preventDefault();
    setSearchParams((p) => { p.set('q', searchInput); p.set('page', '1'); return p; });
  }

  function setFilter(key: string, value: string) {
    setSearchParams((p) => { if (value) p.set(key, value); else p.delete(key); p.set('page', '1'); return p; });
  }

  return (
    <>
      <PageMeta title="Blog" description="Photography tips, stories, and inspiration." />

      <div className="pt-24 pb-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Header */}
          <div className="text-center mb-14">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">Journal</p>
            <h1 className="font-display text-5xl text-charcoal font-light">Stories & Inspiration</h1>
          </div>

          <div className="flex flex-col lg:flex-row gap-12">
            {/* Posts */}
            <div className="flex-1">
              {isLoading ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i}>
                      <div className="aspect-[4/5] bg-cream animate-pulse mb-4" />
                      <div className="h-5 bg-cream rounded animate-pulse w-3/4 mb-2" />
                      <div className="h-4 bg-cream rounded animate-pulse w-full" />
                    </div>
                  ))}
                </div>
              ) : posts.length > 0 ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
                  {posts.map((p) => (
                    <BlogPostCard key={p.id} post={p} />
                  ))}
                </div>
              ) : (
                <p className="text-center font-body text-taupe py-20">No posts found.</p>
              )}

              {/* Pagination */}
              {meta && meta.last_page > 1 && (
                <div className="flex justify-center gap-2 mt-12">
                  {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
                    <button
                      key={p}
                      onClick={() => setSearchParams((sp) => { sp.set('page', String(p)); return sp; })}
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

            {/* Sidebar */}
            <aside className="w-full lg:w-72 shrink-0 space-y-8">
              {/* Search */}
              <div className="bg-ivory-warm rounded-2xl p-5">
                <h3 className="font-display text-lg text-charcoal mb-3">Search</h3>
                <form onSubmit={applySearch} className="flex gap-2">
                  <input
                    id="blog-search"
                    name="q"
                    type="search"
                    autoComplete="search"
                    value={searchInput}
                    onChange={(e) => setSearchInput(e.target.value)}
                    placeholder="Search posts…"
                    className="flex-1 border border-cream bg-ivory px-3 py-2 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
                  />
                  <button type="submit" className="text-bronze hover:text-bronze-dark transition-colors" aria-label="Search">
                    <Search size={18} />
                  </button>
                </form>
              </div>

              {/* Categories */}
              {catList.length > 0 && (
                <div className="bg-ivory-warm rounded-2xl p-5">
                  <h3 className="font-display text-lg text-charcoal mb-3">Categories</h3>
                  <ul className="space-y-2">
                    <li>
                      <button
                        onClick={() => setFilter('category', '')}
                        className={cn('font-body text-sm transition-colors', !category ? 'text-bronze' : 'text-taupe hover:text-bronze')}
                      >
                        All Categories
                      </button>
                    </li>
                    {catList.map((c) => (
                      <li key={c.slug} className="flex items-center justify-between">
                        <button
                          onClick={() => setFilter('category', c.slug)}
                          className={cn('font-body text-sm transition-colors', category === c.slug ? 'text-bronze' : 'text-taupe hover:text-bronze')}
                        >
                          {c.name}
                        </button>
                        {c.post_count !== undefined && (
                          <span className="font-body text-xs text-taupe/60">({c.post_count})</span>
                        )}
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              {/* Tags */}
              {tagList.length > 0 && (
                <div className="bg-ivory-warm rounded-2xl p-5">
                  <h3 className="font-display text-lg text-charcoal mb-3">Tags</h3>
                  <div className="flex flex-wrap gap-2">
                    {tagList.map((t) => (
                      <button
                        key={t.slug}
                        onClick={() => setFilter('tag', t.slug === tag ? '' : t.slug)}
                        className={cn(
                          'px-3 py-1 rounded-full font-body text-xs border transition-colors',
                          tag === t.slug
                            ? 'border-bronze bg-bronze text-ivory'
                            : 'border-cream text-taupe hover:border-bronze hover:text-bronze'
                        )}
                      >
                        {t.name}
                      </button>
                    ))}
                  </div>
                </div>
              )}
            </aside>
          </div>
        </div>
      </div>
    </>
  );
}
