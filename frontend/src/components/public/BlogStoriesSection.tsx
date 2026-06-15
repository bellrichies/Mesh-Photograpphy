import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import type { BlogPost } from '@/types/models';

interface BlogStoriesSectionProps {
  posts: BlogPost[];
  isLoading?: boolean;
}

function formatDate(date: string | null): string {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function BlogPostCard({ post }: { post: BlogPost }) {
  return (
    <article className="group">
      <Link to={`/blog/${post.slug}`} tabIndex={-1} aria-hidden="true">
        <div className="aspect-video overflow-hidden mb-4 bg-cream">
          {post.cover ? (
            <img
              src={post.cover.thumb_url ?? post.cover.url}
              alt={post.cover.alt_text ?? post.title}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
              loading="lazy"
              width={480}
              height={270}
            />
          ) : (
            <div className="w-full h-full bg-cream" />
          )}
        </div>
      </Link>

      {post.categories.length > 0 && (
        <Link
          to={`/blog?category=${post.categories[0].slug}`}
          className="inline-block font-body text-xs font-medium tracking-wide uppercase text-bronze hover:text-bronze-dark mb-2 transition-colors duration-150"
        >
          {post.categories[0].name}
        </Link>
      )}

      <h3 className="font-display text-xl text-charcoal leading-snug mb-2">
        <Link
          to={`/blog/${post.slug}`}
          className="hover:text-bronze transition-colors duration-200 focus:outline-none focus:text-bronze"
        >
          {post.title}
        </Link>
      </h3>

      {post.excerpt && (
        <p className="font-body text-sm text-taupe leading-relaxed line-clamp-2 mb-3">
          {post.excerpt}
        </p>
      )}

      <time dateTime={post.published_at ?? ''} className="font-body text-xs text-taupe/70">
        {formatDate(post.published_at)}
      </time>
    </article>
  );
}

function BlogStoriesSkeleton() {
  return (
    <section className="bg-parchment py-16 lg:py-24">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-end justify-between mb-10">
          <div className="space-y-2">
            <div className="h-3 w-20 bg-cream animate-pulse rounded" />
            <div className="h-8 w-32 bg-cream animate-pulse rounded" />
          </div>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="space-y-3">
              <div className="aspect-video bg-cream animate-pulse" />
              <div className="h-3 w-16 bg-cream animate-pulse rounded" />
              <div className="h-5 w-3/4 bg-cream animate-pulse rounded" />
              <div className="h-3 w-full bg-cream animate-pulse rounded" />
              <div className="h-3 w-4/5 bg-cream animate-pulse rounded" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export default function BlogStoriesSection({ posts, isLoading }: BlogStoriesSectionProps) {
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  if (isLoading) return <BlogStoriesSkeleton />;
  if (!posts.length) return null;

  return (
    <section
      ref={ref}
      className={cn(
        'bg-parchment py-16 lg:py-24 transition-all duration-700 ease-out',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
      )}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-end justify-between mb-10">
          <div>
            <span className="font-body text-xs tracking-[0.15em] uppercase text-taupe block mb-2">
              From the Studio
            </span>
            <h2 className="font-display text-3xl lg:text-4xl text-charcoal">Stories</h2>
          </div>
          <Link
            to="/blog"
            className="text-bronze text-sm font-medium font-body hover:text-bronze-dark flex items-center gap-1.5 transition-colors duration-150"
          >
            Read all posts
            <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {posts.map((post) => (
            <BlogPostCard key={post.id} post={post} />
          ))}
        </div>
      </div>
    </section>
  );
}
