import { useParams, Link, Navigate } from 'react-router-dom';
import { Calendar, User, ArrowLeft, ArrowRight, ChevronLeft, Twitter, Linkedin, Link2 } from 'lucide-react';
import DOMPurify from 'dompurify';
import toast from 'react-hot-toast';
import PageMeta from '@/components/ui/PageMeta';
import BlogSidebar from '@/components/public/BlogSidebar';
import { useBlogPost } from '@/api/blog';

export default function BlogPostPage() {
  const { slug }  = useParams<{ slug: string }>();
  const { data: post, isLoading, isError } = useBlogPost(slug ?? '');

  if (isLoading) {
    return (
      <div className="pt-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col lg:flex-row gap-12">
          <div className="flex-1 min-w-0">
            <div className="aspect-video bg-cream rounded-2xl animate-pulse mb-8" />
            <div className="space-y-4">
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="h-4 bg-cream rounded animate-pulse" style={{ width: `${60 + (i % 3) * 15}%` }} />
              ))}
            </div>
          </div>
          <div className="hidden lg:block w-72 shrink-0 space-y-6">
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className="h-32 bg-cream rounded-xl animate-pulse" />
            ))}
          </div>
        </div>
      </div>
    );
  }

  if (isError || !post) return <Navigate to="/blog" replace />;

  const safeBody  = DOMPurify.sanitize(post.body ?? '');
  const pageUrl   = typeof window !== 'undefined' ? window.location.href : '';
  const shareText = encodeURIComponent(post.title);
  const shareUrl  = encodeURIComponent(pageUrl);

  const copyLink = async () => {
    try {
      await navigator.clipboard.writeText(pageUrl);
      toast.success('Link copied!');
    } catch {
      toast.error('Could not copy link');
    }
  };

  return (
    <>
      <PageMeta title={post.title} seo={post.seo} />

      <div className="pt-24 pb-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          {/* Two-column layout: article + sidebar */}
          <div className="flex flex-col lg:flex-row gap-12 xl:gap-16 items-start">

            {/* Main content */}
            <article className="flex-1 min-w-0">
              <Link
                to="/blog"
                className="inline-flex items-center gap-1.5 font-body text-sm text-taupe hover:text-bronze transition-colors mb-8"
              >
                <ChevronLeft size={16} /> Journal
              </Link>

              {/* Categories */}
              {post.categories.length > 0 && (
                <div className="flex gap-2 flex-wrap mb-3">
                  {post.categories.map((c) => (
                    <Link
                      key={c.slug}
                      to={`/blog?category=${c.slug}`}
                      className="font-body text-xs tracking-widest uppercase text-bronze hover:text-bronze-dark transition-colors"
                    >
                      {c.name}
                    </Link>
                  ))}
                </div>
              )}

              <h1 className="font-display text-4xl sm:text-5xl text-charcoal font-light leading-snug mb-5">
                {post.title}
              </h1>

              {/* Meta */}
              <div className="flex flex-wrap gap-5 font-body text-xs text-taupe mb-8">
                {post.published_at && (
                  <span className="flex items-center gap-1.5">
                    <Calendar size={13} />
                    {new Date(post.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                  </span>
                )}
                {post.author && (
                  <span className="flex items-center gap-1.5">
                    <User size={13} />
                    {post.author.name}
                  </span>
                )}
              </div>

              {/* Cover */}
              {post.cover && (
                <div className="aspect-video overflow-hidden rounded-2xl mb-10">
                  <img
                    src={post.cover.url}
                    alt={post.cover.alt_text ?? post.title}
                    width={800}
                    height={450}
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    className="w-full h-full object-cover"
                  />
                </div>
              )}

              {/* Body */}
              <div
                className="prose prose-lg max-w-none font-body text-charcoal
                  prose-headings:font-display prose-headings:font-light
                  prose-a:text-bronze prose-a:no-underline hover:prose-a:underline
                  prose-img:rounded-xl"
                dangerouslySetInnerHTML={{ __html: safeBody }}
              />

              {/* Tags */}
              {post.tags.length > 0 && (
                <div className="flex flex-wrap gap-2 mt-10 pt-8 border-t border-cream">
                  {post.tags.map((t) => (
                    <Link
                      key={t.slug}
                      to={`/blog?tag=${t.slug}`}
                      className="px-3 py-1 rounded-full border border-cream font-body text-xs text-taupe hover:border-bronze hover:text-bronze transition-colors"
                    >
                      {t.name}
                    </Link>
                  ))}
                </div>
              )}

              {/* Social sharing */}
              <div className="flex items-center gap-3 mt-10 pt-8 border-t border-cream">
                <span className="font-body text-xs text-taupe uppercase tracking-widest">Share</span>
                <a
                  href={`https://twitter.com/intent/tweet?text=${shareText}&url=${shareUrl}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="p-2 rounded-full border border-cream text-taupe hover:text-bronze hover:border-bronze transition-colors"
                  aria-label="Share on X / Twitter"
                >
                  <Twitter size={15} />
                </a>
                <a
                  href={`https://www.linkedin.com/sharing/share-offsite/?url=${shareUrl}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="p-2 rounded-full border border-cream text-taupe hover:text-bronze hover:border-bronze transition-colors"
                  aria-label="Share on LinkedIn"
                >
                  <Linkedin size={15} />
                </a>
                <button
                  type="button"
                  onClick={copyLink}
                  className="p-2 rounded-full border border-cream text-taupe hover:text-bronze hover:border-bronze transition-colors"
                  aria-label="Copy link"
                >
                  <Link2 size={15} />
                </button>
              </div>

              {/* Prev / Next */}
              {(post.prev_post || post.next_post) && (
                <div className="flex items-center justify-between mt-12 pt-8 border-t border-cream">
                  {post.prev_post ? (
                    <Link
                      to={`/blog/${post.prev_post.slug}`}
                      className="flex items-center gap-2 font-body text-sm text-taupe hover:text-bronze transition-colors max-w-[45%]"
                    >
                      <ArrowLeft size={16} className="shrink-0" />
                      <span className="font-display line-clamp-1">{post.prev_post.title}</span>
                    </Link>
                  ) : <div />}

                  {post.next_post ? (
                    <Link
                      to={`/blog/${post.next_post.slug}`}
                      className="flex items-center gap-2 font-body text-sm text-taupe hover:text-bronze transition-colors max-w-[45%]"
                    >
                      <span className="font-display line-clamp-1">{post.next_post.title}</span>
                      <ArrowRight size={16} className="shrink-0" />
                    </Link>
                  ) : <div />}
                </div>
              )}
            </article>

            {/* Sidebar */}
            <div className="w-full lg:w-72 xl:w-80 shrink-0 lg:sticky lg:top-24">
              <BlogSidebar />
            </div>
          </div>

          {/* Related posts — full-width below the two-column layout */}
          {post.related_posts.length > 0 && (
            <div className="mt-20 pt-16 border-t border-cream">
              <h2 className="font-display text-3xl text-charcoal font-light mb-8 text-center">Related Posts</h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {post.related_posts.map((rp) => (
                  <article key={rp.id} className="group">
                    <Link to={`/blog/${rp.slug}`}>
                      {rp.cover ? (
                        <div className="aspect-video overflow-hidden rounded-2xl mb-3">
                          <img
                            src={rp.cover.url}
                            alt={rp.cover.alt_text ?? rp.title}
                            width={480}
                            height={270}
                            loading="lazy"
                            decoding="async"
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                          />
                        </div>
                      ) : (
                        <div className="aspect-video rounded-2xl bg-cream mb-3" />
                      )}
                      <h3 className="font-display text-xl text-charcoal group-hover:text-bronze transition-colors">{rp.title}</h3>
                      {rp.published_at && (
                        <time className="font-body text-xs text-taupe mt-1 block">
                          {new Date(rp.published_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </time>
                      )}
                    </Link>
                  </article>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
