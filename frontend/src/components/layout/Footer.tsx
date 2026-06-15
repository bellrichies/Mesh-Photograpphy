import { useState } from 'react';
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
} from 'lucide-react';
import type { PublicSettings } from '@/types/models';

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

function normalizeExternalUrl(url?: string | null): string | null {
  const value = (url ?? '').trim();
  if (!value) return null;
  if (/^https?:\/\//i.test(value)) return value;
  if (/^(www\.|[a-z0-9-]+\.[a-z]{2,})/i.test(value)) return `https://${value}`;
  return null;
}

export default function Footer({ settings }: FooterProps) {
  const [email, setEmail] = useState('');

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
    { key: 'pinterest', label: 'Pinterest', href: normalizeExternalUrl(settings?.social.pinterest), text: 'P' },
    { key: 'linkedin', label: 'LinkedIn', href: normalizeExternalUrl(settings?.social.linkedin), Icon: Linkedin },
    { key: 'tiktok', label: 'TikTok', href: normalizeExternalUrl(settings?.social.tiktok), Icon: Music2 },
  ].filter((item) => item.href);

  const handleSubscribe = (e: React.FormEvent) => {
    e.preventDefault();
    setEmail('');
  };

  const socialIconClass =
    'w-8 h-8 border border-charcoal-light flex items-center justify-center text-ivory/40 hover:text-bronze hover:border-bronze transition-colors duration-150';

  return (
    <footer className="bg-ink border-t border-charcoal-light">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-10">

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
            <form onSubmit={handleSubscribe} className="flex gap-0 mb-6">
              <input
                type="email"
                id="footer-newsletter-email"
                name="newsletter_email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="Your email address"
                aria-label="Email for newsletter"
                autoComplete="email"
                className="flex-1 min-w-0 bg-charcoal-light border border-charcoal-light text-ivory text-xs font-body px-3 py-2.5 placeholder:text-taupe/40 focus:outline-none focus:border-bronze transition-colors"
              />
              <button
                type="submit"
                className="px-4 py-2.5 bg-bronze text-ivory font-body text-[10px] tracking-[0.1em] uppercase hover:bg-bronze-light transition-colors duration-150 shrink-0"
              >
                Subscribe
              </button>
            </form>

            {/* Social icons */}
            <div className="flex items-center gap-3 flex-wrap">
              {socials.map(({ key, label, href, Icon, text }) => (
                <a key={key} href={href ?? '#'} target="_blank" rel="noopener noreferrer" aria-label={label} className={socialIconClass}>
                  {Icon ? <Icon className="h-3.5 w-3.5" /> : <span className="font-body text-[11px] font-semibold">{text}</span>}
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
