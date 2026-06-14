import { Helmet } from 'react-helmet-async';
import type { SeoMeta } from '@/types/models';

interface PageMetaProps {
  title?: string | null;
  description?: string | null;
  seo?: SeoMeta | null;
  siteName?: string;
}

export default function PageMeta({ title, description, seo, siteName = 'Mesh Photography' }: PageMetaProps) {
  const resolvedTitle       = seo?.meta_title ?? (title ? `${title} | ${siteName}` : siteName);
  const resolvedDescription = seo?.meta_description ?? description ?? null;
  const ogTitle             = seo?.og_title ?? title ?? siteName;
  const ogDesc              = seo?.og_description ?? resolvedDescription;
  const ogImage             = seo?.og_image_url ?? null;
  const canonical           = seo?.canonical_url ?? null;
  const robots              = seo?.robots ?? 'index, follow';

  return (
    <Helmet>
      <title>{resolvedTitle}</title>
      {resolvedDescription && <meta name="description" content={resolvedDescription} />}
      <meta name="robots" content={robots} />
      {canonical && <link rel="canonical" href={canonical} />}

      <meta property="og:type" content="website" />
      <meta property="og:title" content={ogTitle} />
      {ogDesc && <meta property="og:description" content={ogDesc} />}
      {ogImage && <meta property="og:image" content={ogImage} />}
      <meta property="og:site_name" content={siteName} />

      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={ogTitle} />
      {ogDesc && <meta name="twitter:description" content={ogDesc} />}
      {ogImage && <meta name="twitter:image" content={ogImage} />}
    </Helmet>
  );
}
