import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Search, Tag, Folder } from 'lucide-react';
import { useBlogPosts, useBlogCategories, useBlogTags } from '@/api/blog';

export default function BlogSidebar() {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');

  const { data: recentData } = useBlogPosts({ per_page: 5, page: 1 });
  const { data: categories } = useBlogCategories();
  const { data: tags }       = useBlogTags();

  const recentPosts = recentData?.data ?? [];

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/blog?q=${encodeURIComponent(searchQuery.trim())}`);
    }
  };

  return (
    <aside className="space-y-8">
      {/* Search */}
      <div>
        <h3 className="font-display text-lg text-charcoal mb-3 flex items-center gap-2">
          <Search size={16} className="text-bronze" />
          Search
        </h3>
        <form onSubmit={handleSearch} className="flex gap-2">
          <input
            type="search"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search posts…"
            className="flex-1 border border-cream bg-ivory px-4 py-2.5 font-body text-sm text-charcoal placeholder:text-taupe focus:outline-none focus:border-bronze rounded-lg"
          />
          <button
            type="submit"
            className="px-4 py-2.5 bg-bronze text-ivory font-body text-sm rounded-lg hover:bg-bronze-light transition-colors"
          >
            Go
          </button>
        </form>
      </div>

      {/* Recent Posts */}
      {recentPosts.length > 0 && (
        <div>
          <h3 className="font-display text-lg text-charcoal mb-3">Recent Posts</h3>
          <ul className="space-y-3">
            {recentPosts.map((post) => (
              <li key={post.id}>
                <Link
                  to={`/blog/${post.slug}`}
                  className="flex items-start gap-3 group"
                >
                  {post.cover ? (
                    <img
                      src={post.cover.thumb_url ?? post.cover.url}
                      alt={post.cover.alt_text ?? post.title}
                      width={56}
                      height={56}
                      loading="lazy"
                      className="w-14 h-14 object-cover rounded shrink-0"
                    />
                  ) : (
                    <div className="w-14 h-14 bg-cream rounded shrink-0" />
                  )}
                  <div className="min-w-0">
                    <p className="font-body text-sm text-charcoal group-hover:text-bronze transition-colors line-clamp-2 leading-snug">
                      {post.title}
                    </p>
                    {post.published_at && (
                      <time className="font-body text-xs text-taupe mt-0.5 block">
                        {new Date(post.published_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </time>
                    )}
                  </div>
                </Link>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Categories */}
      {categories && categories.length > 0 && (
        <div>
          <h3 className="font-display text-lg text-charcoal mb-3 flex items-center gap-2">
            <Folder size={16} className="text-bronze" />
            Categories
          </h3>
          <ul className="space-y-1.5">
            {categories.map((c) => (
              <li key={c.id}>
                <Link
                  to={`/blog?category=${c.slug}`}
                  className="flex items-center justify-between font-body text-sm text-taupe hover:text-bronze transition-colors py-1 border-b border-cream/50 last:border-0"
                >
                  <span>{c.name}</span>
                  {c.post_count !== undefined && (
                    <span className="text-xs bg-cream text-charcoal px-2 py-0.5 rounded-full">
                      {c.post_count}
                    </span>
                  )}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Tags */}
      {tags && tags.length > 0 && (
        <div>
          <h3 className="font-display text-lg text-charcoal mb-3 flex items-center gap-2">
            <Tag size={16} className="text-bronze" />
            Tags
          </h3>
          <div className="flex flex-wrap gap-2">
            {tags.map((t) => (
              <Link
                key={t.id}
                to={`/blog?tag=${t.slug}`}
                className="px-3 py-1 rounded-full border border-cream font-body text-xs text-taupe hover:border-bronze hover:text-bronze transition-colors"
              >
                {t.name}
                {t.post_count !== undefined && (
                  <span className="ml-1 opacity-60">({t.post_count})</span>
                )}
              </Link>
            ))}
          </div>
        </div>
      )}
    </aside>
  );
}
