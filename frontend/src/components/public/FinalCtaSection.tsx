import { Link } from 'react-router-dom';
import { ArrowRight, CheckCircle, MessageCircle } from 'lucide-react';
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
      className="relative overflow-hidden bg-espresso py-12 sm:py-14 lg:py-16"
    >
      <div className="absolute inset-0 bg-[linear-gradient(135deg,rgba(47,76,69,0.35)_0%,rgba(27,23,20,0)_45%,rgba(196,167,125,0.16)_100%)]" />
      <div className="absolute inset-x-0 top-0 h-px bg-gold/20" />
      <div className="absolute inset-x-0 bottom-0 h-px bg-black/30" />

      <div
        className={cn(
          'site-container relative z-10 transition-all duration-700 ease-out',
          isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
        )}
      >
        <div className="grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_auto]">
          <div className="max-w-2xl">
            <div className="mb-4 flex items-center gap-3">
              <span className="h-px w-10 bg-gold" />
              <span className="font-body text-[11px] uppercase tracking-[0.2em] text-gold/80">
                Ready when you are
              </span>
            </div>

            <h2 className="font-display text-3xl font-light leading-tight text-ivory sm:text-4xl lg:text-5xl">
              Let's create something
              <span className="text-gold italic"> beautiful together</span>
            </h2>

            <p className="mt-4 max-w-xl font-body text-sm leading-relaxed text-ivory/65 sm:text-base">
              Every session starts with a focused conversation about your story,
              timeline, and the images you want to hold onto.
            </p>
          </div>

          <div className="flex w-full flex-col gap-3 sm:w-auto sm:min-w-[280px] sm:flex-row lg:flex-col">
            <Link
              to="/booking"
              className="inline-flex items-center justify-center gap-2.5 bg-bronze px-7 py-3.5 font-body text-[11px] font-semibold uppercase tracking-[0.16em] text-ivory transition-colors duration-200 hover:bg-bronze-light focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 focus:ring-offset-espresso"
            >
              Book a Session
              <ArrowRight className="h-4 w-4" aria-hidden="true" />
            </Link>
            <Link
              to="/contact"
              className="inline-flex items-center justify-center gap-2.5 border border-ivory/30 px-7 py-3.5 font-body text-[11px] font-semibold uppercase tracking-[0.16em] text-ivory/85 transition-colors duration-200 hover:border-gold hover:text-ivory focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 focus:ring-offset-espresso"
            >
              Get in Touch
              <MessageCircle className="h-4 w-4" aria-hidden="true" />
            </Link>
          </div>
        </div>

        <div className="mt-7 grid gap-3 border-t border-ivory/10 pt-5 sm:grid-cols-3">
          {TRUST_SIGNALS.map((text) => (
            <span
              key={text}
              className="flex items-center justify-center gap-2 font-body text-xs text-ivory/45 sm:justify-start"
            >
              <CheckCircle className="h-3.5 w-3.5 shrink-0 text-gold/70" aria-hidden="true" />
              {text}
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}
