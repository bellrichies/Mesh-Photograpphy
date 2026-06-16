import { useState } from 'react';
import type { SVGProps } from 'react';
import { Link } from 'react-router-dom';
import {
  Instagram,
  Youtube,
  Mail,
  Phone,
  MapPin,
  Clock,
  MessageSquare,
  Calendar,
  Facebook,
  Twitter,
  Linkedin,
  Music2,
  Loader2,
  Send,
} from 'lucide-react';
import type { PublicSettings } from '@/types/models';
import { useSubscribeNewsletter } from '@/api/newsletter';
import WhatsAppIcon from '@/components/ui/WhatsAppIcon';
import { buildWhatsAppHref, normalizeExternalUrl } from '@/utils/social-links';
import { getErrorMessage } from '@/utils/api-errors';

interface FooterProps {
  settings?: PublicSettings | null;
}

const EXPLORE_LINKS = [
  ['/about',     'About'],
  ['/services',  'Services'],
  ['/portfolio', 'Portfolio'],
  ['/blog',      'Journal'],
  ['/contact',   'Contact'],
] as const;

const LEGAL_LINKS = [
  ['/privacy-policy', 'Privacy Policy'],
  ['/terms',          'Terms of Service'],
  ['/cookie-policy',  'Cookie Policy'],
] as const;

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function PinterestIcon(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" {...props}>
      <path
        d="M11.7 3.8c-4.1 0-7 2.8-7 6.5 0 2.3 1.3 4.1 3 4.1.5 0 .8-.5.7-.9l-.2-.8c-.1-.3 0-.5.2-.8.3-.4.5-.9.5-1.6 0-1.9 1.1-3.6 3.2-3.6 1.7 0 2.9 1.1 2.9 2.8 0 2.1-1 3.7-2.4 3.7-.8 0-1.4-.7-1.2-1.5l.4-1.7c.2-.7.1-1.4-.7-1.4-.9 0-1.6.9-1.6 2.2 0 .8.3 1.3.3 1.3l-1.2 5.1c-.3 1.3-.2 2.8-.1 3.6.1.3.5.4.7.1.5-.7 1.2-2 1.5-3.3l.5-2.1c.5.9 1.5 1.3 2.6 1.3 3.4 0 5.7-3.1 5.7-7.2 0-3.2-2.7-5.8-6.8-5.8Z"
        fill="currentColor"
      />
    </svg>
  );
}

export default function Footer({ settings }: FooterProps) {
  const [email, setEmail] = useState('');
  const [feedback, setFeedback] = useState<{ type: 'success' | 'error'; message: string } | null>(null);
  const subscribeMutation = useSubscribeNewsletter();

  const siteName  = settings?.site.name ?? 'Mesh Photography';
  const tagline   = settings?.site.tagline ?? 'Editorial imagery for modern celebrations, portraits, and brands.';
  const address   = settings?.contact.address;
  const emailAddr = settings?.contact.email;
  const phone     = settings?.contact.phone;
  const logoUrl   = settings?.site.logo_url     ?? null;
  const socials = [
    { key: 'instagram', label: 'Instagram', href: normalizeExternalUrl(settings?.social.instagram), Icon: Instagram },
    { key: 'facebook', label: 'Facebook', href: normalizeExternalUrl(settings?.social.facebook), Icon: Facebook },
    { key: 'x', label: 'X / Twitter', href: normalizeExternalUrl(settings?.social.x ?? settings?.social.twitter), Icon: Twitter },
    { key: 'youtube', label: 'YouTube', href: normalizeExternalUrl(settings?.social.youtube), Icon: Youtube },
    { key: 'pinterest', label: 'Pinterest', href: normalizeExternalUrl(settings?.social.pinterest), Icon: PinterestIcon },
    { key: 'linkedin', label: 'LinkedIn', href: normalizeExternalUrl(settings?.social.linkedin), Icon: Linkedin },
    { key: 'tiktok', label: 'TikTok', href: normalizeExternalUrl(settings?.social.tiktok), Icon: Music2 },
    { key: 'whatsapp', label: 'WhatsApp', href: buildWhatsAppHref(settings?.social.whatsapp), Icon: WhatsAppIcon },
  ].filter((item) => item.href);

  const handleSubscribe = async (e: React.FormEvent) => {
    e.preventDefault();
    const trimmedEmail = email.trim();

    if (!trimmedEmail) {
      setFeedback({ type: 'error', message: 'Enter your email address.' });
      return;
    }

    if (!EMAIL_PATTERN.test(trimmedEmail)) {
      setFeedback({ type: 'error', message: 'Enter a valid email address.' });
      return;
    }

    setFeedback(null);

    try {
      const response = await subscribeMutation.mutateAsync({ email: trimmedEmail });
      setEmail('');
      setFeedback({ type: 'success', message: response.message });
    } catch (error) {
      setFeedback({ type: 'error', message: getErrorMessage(error) });
    }
  };

  const socialIconClass =
    'w-9 h-9 rounded-full border border-ivory/15 bg-white/0 flex items-center justify-center text-ivory/55 transition-all duration-200 hover:-translate-y-0.5 hover:text-ivory hover:border-bronze hover:bg-bronze/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-bronze';
  const feedbackId = 'footer-newsletter-feedback';
  const isSubscribing = subscribeMutation.isPending;

  return (
    <footer className="bg-ink border-t border-charcoal-light">
      <div className="site-container pt-14 pb-10">

        {/* ── Main grid ─────────────────────────────────────────────────── */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8 mb-12">

          {/* Col 1 - Brand + Newsletter + Social */}
          <div className="sm:col-span-2 lg:col-span-1">
            <Link to="/" className="inline-flex items-center gap-2.5 mb-4 group">
              {logoUrl ? (
                <img src={logoUrl} alt={siteName} className="h-8 w-auto" />
              ) : (
                <div className="w-8 h-8 border border-ivory/20 flex items-center justify-center shrink-0">
                  <span className="font-display text-ivory text-xs">M</span>
                </div>
              )}
              <span className="font-display text-xl text-ivory group-hover:text-bronze transition-colors duration-150">
                {siteName}
              </span>
            </Link>

            {tagline && (
              <p className="font-body text-xs text-taupe/60 leading-relaxed mb-7 max-w-xs">{tagline}</p>
            )}

            {/* Newsletter */}
            <p className="font-body text-[10px] tracking-[0.15em] uppercase text-taupe mb-3">Stay Inspired</p>
            <form onSubmit={handleSubscribe} className="mb-6" noValidate>
              <div className="flex gap-0">
              <input
                type="email"
                id="footer-newsletter-email"
                name="newsletter_email"
                value={email}
                onChange={(e) => {
                  setEmail(e.target.value);
                  if (feedback?.type === 'error') setFeedback(null);
                }}
                placeholder="Your email address"
                aria-label="Email for newsletter"
                aria-invalid={feedback?.type === 'error' ? true : undefined}
                aria-describedby={feedback ? feedbackId : undefined}
                autoComplete="email"
                disabled={isSubscribing}
                className="flex-1 min-w-0 bg-charcoal-light border border-charcoal-light text-ivory text-xs font-body px-3 py-2.5 placeholder:text-taupe/40 focus:outline-none focus:border-bronze transition-colors disabled:cursor-not-allowed disabled:opacity-70"
              />
              <button
                type="submit"
                disabled={isSubscribing}
                aria-label={isSubscribing ? 'Subscribing to newsletter' : 'Subscribe to newsletter'}
                className="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-bronze text-ivory font-body text-[10px] tracking-[0.1em] uppercase hover:bg-bronze-light transition-colors duration-150 shrink-0 disabled:cursor-not-allowed disabled:opacity-70"
              >
                {isSubscribing ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Send className="h-3.5 w-3.5" />}
                <span>{isSubscribing ? 'Joining' : 'Subscribe'}</span>
              </button>
              </div>
              {feedback && (
                <p
                  id={feedbackId}
                  role={feedback.type === 'error' ? 'alert' : 'status'}
                  className={`mt-2 font-body text-xs ${feedback.type === 'error' ? 'text-red-300' : 'text-bronze-light'}`}
                >
                  {feedback.message}
                </p>
              )}
            </form>

            {/* Social icons */}
            <div className="flex items-center gap-3 flex-wrap">
              {socials.map(({ key, label, href, Icon }) => (
                <a key={key} href={href ?? '#'} target="_blank" rel="noopener noreferrer" aria-label={label} className={socialIconClass}>
                  <Icon className="h-4 w-4" />
                </a>
              ))}
            </div>
          </div>

          {/* Col 2 - Explore */}
          <div>
            <h4 className="font-body text-[10px] tracking-[0.15em] uppercase text-taupe mb-5">Explore</h4>
            <nav aria-label="Footer navigation - explore">
              {EXPLORE_LINKS.map(([to, label]) => (
                <Link
                  key={to}
                  to={to}
                  className="block font-body text-sm text-ivory/50 hover:text-ivory transition-colors duration-150 mb-2.5"
                >
                  {label}
                </Link>
              ))}
            </nav>
          </div>

          {/* Col 3 - Legal */}
          <div>
            <h4 className="font-body text-[10px] tracking-[0.15em] uppercase text-taupe mb-5">Legal</h4>
            <nav aria-label="Footer navigation - legal">
              {LEGAL_LINKS.map(([to, label]) => (
                <Link
                  key={to}
                  to={to}
                  className="block font-body text-sm text-ivory/50 hover:text-ivory transition-colors duration-150 mb-2.5"
                >
                  {label}
                </Link>
              ))}
            </nav>
          </div>

          {/* Col 4 - Contact (moved to last) */}
          <div>
            <h4 className="font-body text-[10px] tracking-[0.15em] uppercase text-taupe mb-5">Contact</h4>
            <div className="space-y-4">
              {emailAddr && (
                <div className="flex items-start gap-3">
                  <Mail size={13} className="text-bronze mt-0.5 shrink-0" />
                  <a
                    href={`mailto:${emailAddr}`}
                    className="font-body text-xs text-ivory/60 hover:text-ivory transition-colors duration-150 leading-relaxed break-all"
                  >
                    {emailAddr}
                  </a>
                </div>
              )}

              {phone && (
                <div className="flex items-start gap-3">
                  <Phone size={13} className="text-bronze mt-0.5 shrink-0" />
                  <a
                    href={`tel:${phone}`}
                    className="font-body text-xs text-ivory/60 hover:text-ivory transition-colors duration-150"
                  >
                    {phone}
                  </a>
                </div>
              )}

              {address && (
                <div className="flex items-start gap-3">
                  <MapPin size={13} className="text-bronze mt-0.5 shrink-0" />
                  <p className="font-body text-xs text-ivory/60 leading-relaxed whitespace-pre-line">{address}</p>
                </div>
              )}

              <div className="flex items-start gap-3">
                <Clock size={13} className="text-bronze mt-0.5 shrink-0" />
                <p className="font-body text-xs text-ivory/60 leading-relaxed">
                  Monday - Thursday: 10:00 AM - 5:00 PM
                </p>
              </div>

              <div className="flex items-start gap-3">
                <MessageSquare size={13} className="text-bronze mt-0.5 shrink-0" />
                <Link
                  to="/contact"
                  className="font-body text-xs text-ivory/60 hover:text-bronze transition-colors duration-150"
                >
                  Start a conversation
                </Link>
              </div>

              <div className="flex items-start gap-3">
                <Calendar size={13} className="text-bronze mt-0.5 shrink-0" />
                <Link
                  to="/booking"
                  className="font-body text-xs text-ivory/60 hover:text-bronze transition-colors duration-150"
                >
                  Request availability
                </Link>
              </div>
            </div>
          </div>
        </div>

        {/* ── Bottom bar ────────────────────────────────────────────────── */}
        <div className="border-t border-charcoal-light pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p className="font-body text-xs text-taupe/40">
            &copy; {new Date().getFullYear()} {siteName}. All rights reserved.
          </p>
          <p className="font-body text-xs text-taupe/30 text-center sm:text-right max-w-sm">
            Serving couples, founders, and families with calm direction, thoughtful pacing, and imagery designed to feel timeless.
          </p>
        </div>
      </div>
    </footer>
  );
}
