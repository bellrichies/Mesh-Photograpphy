import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import type { PublicSettings } from '@/types/models';

interface BrandIntroSectionProps {
  settings?: PublicSettings | null;
}

export default function BrandIntroSection({ settings }: BrandIntroSectionProps) {
  const { ref, isVisible } = useScrollReveal<HTMLElement>();
  const tagline = settings?.site.tagline ?? 'Capturing the quiet moments between the moments.';

  return (
    <section
      ref={ref}
      className={cn(
        'py-20 lg:py-28 bg-ivory transition-all duration-700 ease-out',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
      )}
    >
      <div className="max-w-3xl mx-auto px-6 text-center">
        <div className="flex justify-center mb-10">
          <div className="h-px w-10 bg-gold" />
        </div>

        <p className="font-display text-3xl lg:text-4xl text-charcoal font-light leading-snug mb-6">
          {tagline}
        </p>

        <p className="font-body text-base lg:text-lg text-taupe leading-relaxed max-w-xl mx-auto mb-8">
          A photography studio that approaches every session with patience, intention, and an eye for
          the light that makes everything feel true.
        </p>

        <Link
          to="/about"
          className="inline-flex items-center gap-2 text-bronze font-body text-sm font-medium hover:text-bronze-dark transition-colors duration-150"
        >
          About the Studio
          <ArrowRight className="h-4 w-4" />
        </Link>

        <div className="flex justify-center mt-10">
          <div className="h-px w-10 bg-gold" />
        </div>
      </div>
    </section>
  );
}
