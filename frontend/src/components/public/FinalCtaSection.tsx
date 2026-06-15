import { Link } from 'react-router-dom';
import { CheckCircle } from 'lucide-react';
import { cn } from '@/utils/cn';
import { useScrollReveal } from '@/hooks/useScrollReveal';

const TRUST_SIGNALS = [
  'Free consultation',
  'Response within 24 hours',
  'No commitment required',
];

export default function FinalCtaSection() {
  const { ref, isVisible } = useScrollReveal<HTMLElement>();

  return (
    <section
      ref={ref}
      className="py-24 lg:py-32 bg-espresso relative overflow-hidden"
    >
      <div
        className={cn(
          'relative z-10 max-w-3xl mx-auto px-6 text-center transition-all duration-700 ease-out',
          isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
        )}
      >
        <div className="flex justify-center mb-8">
          <div className="h-px w-10 bg-gold" />
        </div>

        <h2 className="font-display text-4xl lg:text-5xl text-ivory font-light leading-tight mb-5">
          Let's Create Something
          <span className="block text-gold italic">Beautiful Together</span>
        </h2>

        <p className="font-body text-base text-ivory/60 leading-relaxed mb-10 max-w-lg mx-auto">
          Every session begins with a conversation. Tell us about your vision
          and we'll tell you how we can make it extraordinary.
        </p>

        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            to="/booking"
            className="px-8 py-3.5 bg-bronze text-ivory font-body text-sm tracking-wide hover:bg-bronze-light transition-colors duration-150 text-center min-w-[160px]"
          >
            Book a Session
          </Link>
          <Link
            to="/contact"
            className="px-8 py-3.5 border border-ivory/40 text-ivory/80 font-body text-sm tracking-wide hover:border-ivory hover:text-ivory transition-colors duration-150 text-center min-w-[160px]"
          >
            Get in Touch
          </Link>
        </div>

        <div className="flex flex-wrap justify-center gap-6 mt-10">
          {TRUST_SIGNALS.map((text) => (
            <span
              key={text}
              className="font-body text-xs text-ivory/30 flex items-center gap-1.5"
            >
              <CheckCircle className="h-3.5 w-3.5 shrink-0" />
              {text}
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}
