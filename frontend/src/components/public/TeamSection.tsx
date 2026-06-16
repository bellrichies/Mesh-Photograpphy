import { Instagram, Mail, User } from 'lucide-react';
import DOMPurify from 'dompurify';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import { cn } from '@/utils/cn';
import type { TeamMember } from '@/types/models';

interface TeamSectionProps {
  members: TeamMember[];
  heading: string;
  /** Optional rich-text intro (sanitized HTML) shown beneath the heading. */
  intro?: string | null;
}

function getInitials(name: string): string {
  const parts = name.match(/[A-Za-z0-9]+/g) ?? [];
  return parts.slice(0, 2).map((p) => p[0]).join('').toUpperCase() || 'MP';
}

function MemberCard({ member }: { member: TeamMember }) {
  return (
    <article className="group flex flex-col">
      {/* Portrait */}
      <div className="relative overflow-hidden bg-charcoal" style={{ aspectRatio: '4/5' }}>
        {member.photo ? (
          <img
            src={member.photo.url}
            alt={member.photo.alt_text ?? member.name}
            className="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.04]"
            loading="lazy"
            width={member.photo.width ?? 600}
            height={member.photo.height ?? 750}
            decoding="async"
          />
        ) : (
          <div className="absolute inset-0 flex items-center justify-center bg-sand">
            <span className="font-display text-5xl font-light text-bronze/60">
              {getInitials(member.name)}
            </span>
            <User className="absolute bottom-4 right-4 text-charcoal/10" size={28} aria-hidden="true" />
          </div>
        )}

        {/* Social links — slide up on hover */}
        {(member.instagram_url || member.email) && (
          <div className="absolute inset-x-0 bottom-0 flex items-center gap-2 bg-gradient-to-t from-charcoal/80 to-transparent p-4 opacity-0 translate-y-3 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0">
            {member.instagram_url && (
              <a
                href={member.instagram_url}
                target="_blank"
                rel="noopener noreferrer"
                aria-label={`${member.name} on Instagram`}
                className="flex h-9 w-9 items-center justify-center rounded-full bg-ivory/90 text-charcoal transition-colors hover:bg-bronze hover:text-ivory"
              >
                <Instagram size={16} />
              </a>
            )}
            {member.email && (
              <a
                href={`mailto:${member.email}`}
                aria-label={`Email ${member.name}`}
                className="flex h-9 w-9 items-center justify-center rounded-full bg-ivory/90 text-charcoal transition-colors hover:bg-bronze hover:text-ivory"
              >
                <Mail size={16} />
              </a>
            )}
          </div>
        )}
      </div>

      {/* Details */}
      <div className="pt-5">
        <h3 className="font-display text-xl text-charcoal font-light leading-snug">{member.name}</h3>
        {member.role && (
          <p className="font-body text-xs tracking-[0.14em] uppercase text-bronze mt-1.5">{member.role}</p>
        )}
        {member.bio && (
          <p className="font-body text-sm text-taupe leading-relaxed mt-3">{member.bio}</p>
        )}
      </div>
    </article>
  );
}

export default function TeamSection({ members, heading, intro }: TeamSectionProps) {
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  if (!members.length) return null;

  return (
    <section
      ref={ref}
      className={cn(
        'bg-ivory py-20 lg:py-28 transition-all duration-700 ease-out',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
      )}
    >
      <div className="site-container">
        {/* Header */}
        <div className="max-w-2xl mb-14 lg:mb-16">
          <span className="font-body text-xs tracking-[0.18em] uppercase text-taupe block mb-3">
            Our Team
          </span>
          <h2 className="font-display text-4xl lg:text-5xl text-charcoal font-light leading-tight">
            {heading}
          </h2>
          {intro && (
            <div
              className="prose max-w-none font-body text-taupe text-base leading-relaxed mt-5
                prose-a:text-bronze prose-a:no-underline hover:prose-a:underline"
              dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(intro) }}
            />
          )}
        </div>

        {/* Member grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
          {members.map((member) => (
            <MemberCard key={member.id} member={member} />
          ))}
        </div>
      </div>
    </section>
  );
}
