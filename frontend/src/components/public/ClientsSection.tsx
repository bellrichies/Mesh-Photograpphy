import DOMPurify from 'dompurify';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import { cn } from '@/utils/cn';
import type { Client } from '@/types/models';

interface ClientsSectionProps {
  clients: Client[];
  heading: string;
  /** Optional rich-text intro (sanitized HTML) shown beneath the heading. */
  intro?: string | null;
}

function ClientTile({ client }: { client: Client }) {
  const className =
    'group relative block aspect-[4/3] overflow-hidden rounded-xl bg-charcoal shadow-soft ring-1 ring-charcoal/5 transition-all duration-300 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-bronze';

  const inner = client.logo ? (
    <>
      {/* Image fills the entire card */}
      <img
        src={client.logo.url}
        alt={client.logo.alt_text ?? client.name}
        className="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.05]"
        loading="lazy"
        width={client.logo.width ?? 600}
        height={client.logo.height ?? 450}
        decoding="async"
      />
      {/* Gradient overlay so the name stays legible */}
      <div className="absolute inset-0 bg-gradient-to-t from-charcoal/75 via-charcoal/10 to-transparent transition-colors duration-300 group-hover:from-charcoal/85" />
      <span className="absolute inset-x-0 bottom-0 p-4 font-display text-base font-light leading-snug text-ivory">
        {client.name}
      </span>
    </>
  ) : (
    /* No-logo fallback — clean branded tile with the client name centered */
    <div className="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-espresso to-charcoal px-5 text-center transition-colors duration-300 group-hover:from-charcoal group-hover:to-espresso">
      <span className="font-display text-xl font-light text-ivory/90 transition-colors duration-300 group-hover:text-bronze-light">
        {client.name}
      </span>
    </div>
  );

  if (client.website_url) {
    return (
      <a
        href={client.website_url}
        target="_blank"
        rel="noopener noreferrer"
        aria-label={`Visit ${client.name}`}
        title={client.name}
        className={className}
      >
        {inner}
      </a>
    );
  }

  return (
    <div className={className} title={client.name}>
      {inner}
    </div>
  );
}

export default function ClientsSection({ clients, heading, intro }: ClientsSectionProps) {
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  if (!clients.length) return null;

  return (
    <section
      ref={ref}
      className={cn(
        'bg-sand py-20 lg:py-28 transition-all duration-700 ease-out',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
      )}
    >
      <div className="site-container">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-14">
          <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-3">
            Clients &amp; Partners
          </span>
          <h2 className="font-display text-4xl lg:text-5xl text-charcoal font-light leading-tight">
            {heading}
          </h2>
          {intro && (
            <div
              className="prose max-w-none font-body text-taupe text-base leading-relaxed mt-5 mx-auto
                prose-a:text-bronze prose-a:no-underline hover:prose-a:underline"
              dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(intro) }}
            />
          )}
        </div>

        {/* Client cards */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5 sm:gap-6">
          {clients.map((client) => (
            <ClientTile key={client.id} client={client} />
          ))}
        </div>
      </div>
    </section>
  );
}
