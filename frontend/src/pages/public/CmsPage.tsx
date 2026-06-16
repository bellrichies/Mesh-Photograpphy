import { useParams } from 'react-router-dom';
import DOMPurify from 'dompurify';
import PageMeta from '@/components/ui/PageMeta';
import { useCmsPage } from '@/api/pages';
import NotFoundPage from '@/pages/public/NotFoundPage';
import type { PageSection } from '@/types/models';

function sectionContent(sections: PageSection[], key: string): string | null {
  return sections.find((section) => section.section_key === key)?.content ?? null;
}

export default function CmsPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data: page, isLoading, isError } = useCmsPage(slug ?? '');

  if (isLoading) {
    return (
      <div className="pt-24 site-container-readable">
        <div className="space-y-4">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="h-4 bg-cream rounded animate-pulse" style={{ width: `${55 + (i % 4) * 12}%` }} />
          ))}
        </div>
      </div>
    );
  }

  if (isError || !page) return <NotFoundPage />;

  const heroTitle = sectionContent(page.sections, 'hero_title') || page.title;
  const heroSubtitle = sectionContent(page.sections, 'hero_subtitle');
  const bodySections = page.sections.filter(
    (section) => !['hero_title', 'hero_subtitle'].includes(section.section_key)
  );

  return (
    <>
      <PageMeta title={heroTitle} seo={page.seo} />

      <div className="pt-24 pb-20">
        <div className="site-container-readable">
          <header className="mb-10">
            <h1 className="font-display text-4xl sm:text-5xl text-charcoal font-light">{heroTitle}</h1>
            {heroSubtitle && (
              <p className="font-body text-sm text-taupe leading-relaxed mt-4 max-w-2xl">{heroSubtitle}</p>
            )}
          </header>

          {bodySections.map((section) => {
            if (section.section_type === 'rich_text' && section.content) {
              return (
                <div
                  key={section.id}
                  className="prose prose-lg max-w-none font-body text-charcoal
                    prose-headings:font-display prose-headings:font-light
                    prose-a:text-bronze prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-none"
                  dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(section.content) }}
                />
              );
            }
            if (section.section_type === 'image' && section.media) {
              return (
                <figure key={section.id} className="my-8">
                  <img
                    src={section.media.url}
                    alt={section.media.alt_text ?? section.title ?? ''}
                    className="w-full h-auto object-cover"
                    loading="lazy"
                    decoding="async"
                  />
                  {section.title && (
                    <figcaption className="mt-2 font-body text-xs text-taupe">{section.title}</figcaption>
                  )}
                </figure>
              );
            }
            return null;
          })}
        </div>
      </div>
    </>
  );
}
