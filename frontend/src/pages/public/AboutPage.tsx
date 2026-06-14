import { Link } from 'react-router-dom';
import PageMeta from '@/components/ui/PageMeta';
import { useCmsPage } from '@/api/pages';
import DOMPurify from 'dompurify';

export default function AboutPage() {
  const { data: page, isLoading } = useCmsPage('about');

  if (isLoading) {
    return (
      <div className="pt-24 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="space-y-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="h-4 bg-cream rounded animate-pulse" style={{ width: `${50 + (i % 4) * 12}%` }} />
          ))}
        </div>
      </div>
    );
  }

  if (page) {
    const body = page.sections.find((s) => s.section_type === 'rich_text')?.content ?? '';
    return (
      <>
        <PageMeta title={page.title} seo={page.seo} />
        <div className="pt-24 pb-20">
          <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">About</p>
            <h1 className="font-display text-5xl text-charcoal font-light mb-10">{page.title}</h1>
            {body && (
              <div
                className="prose prose-lg max-w-none font-body text-charcoal
                  prose-headings:font-display prose-headings:font-light
                  prose-a:text-bronze prose-a:no-underline hover:prose-a:underline
                  prose-img:rounded-xl"
                dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(body) }}
              />
            )}
          </div>
        </div>
      </>
    );
  }

  // Fallback static about page when CMS page doesn't exist yet
  return (
    <>
      <PageMeta title="About" description="Learn more about Mesh Photography and our story." />

      <div className="pt-24 pb-20">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <p className="font-body text-xs tracking-widest uppercase text-bronze mb-3">About</p>
          <h1 className="font-display text-5xl text-charcoal font-light mb-10">Our Story</h1>

          <div className="prose prose-lg max-w-none font-body text-charcoal prose-headings:font-display prose-headings:font-light prose-a:text-bronze">
            <p>
              Mesh Photography was born from a deep love of storytelling through images. We believe that
              every moment — whether grand or quiet — carries meaning, and our job is to preserve it.
            </p>
            <p>
              Based in the heart of the city, we travel wherever love, life, and light take us. From
              intimate elopements in the mountains to corporate headshots downtown, we bring the same
              care and craft to every shoot.
            </p>
            <h2>Our Approach</h2>
            <p>
              We're not interested in stiff poses or forced smiles. We create space for genuine moments
              to emerge — then we're there to catch them. Documentary instincts, editorial sensibility.
            </p>
          </div>

          <div className="flex gap-4 mt-10">
            <Link to="/portfolio" className="px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors">
              See Our Work
            </Link>
            <Link to="/contact" className="px-8 py-3 border border-charcoal text-charcoal font-body text-sm tracking-widest uppercase hover:bg-charcoal hover:text-ivory transition-colors">
              Get in Touch
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}
