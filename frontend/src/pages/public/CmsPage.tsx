import { useParams, Navigate } from 'react-router-dom';
import DOMPurify from 'dompurify';
import PageMeta from '@/components/ui/PageMeta';
import { useCmsPage } from '@/api/pages';

export default function CmsPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: page, isLoading, isError } = useCmsPage(slug ?? '');

  if (isLoading) {
    return (
      <div className="pt-24 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="space-y-4">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="h-4 bg-cream rounded animate-pulse" style={{ width: `${55 + (i % 4) * 12}%` }} />
          ))}
        </div>
      </div>
    );
  }

  if (isError || !page) return <Navigate to="/" replace />;

  return (
    <>
      <PageMeta title={page.title} seo={page.seo} />

      <div className="pt-24 pb-20">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="font-display text-4xl sm:text-5xl text-charcoal font-light mb-10">{page.title}</h1>

          {page.sections.map((section) => {
            if (section.section_type === 'rich_text' && section.content) {
              return (
                <div
                  key={section.id}
                  className="prose prose-lg max-w-none font-body text-charcoal
                    prose-headings:font-display prose-headings:font-light
                    prose-a:text-bronze prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-xl"
                  dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(section.content) }}
                />
              );
            }
            return null;
          })}
        </div>
      </div>
    </>
  );
}
