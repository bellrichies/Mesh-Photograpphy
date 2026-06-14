import { Link } from 'react-router-dom';
import { Instagram, Facebook } from 'lucide-react';
import type { PublicSettings } from '@/types/models';

interface FooterProps {
  settings?: PublicSettings | null;
}

const EXPLORE_LINKS = [
  ['/portfolio', 'Portfolio'],
  ['/services',  'Services'],
  ['/blog',      'Blog'],
  ['/about',     'About'],
] as const;

const STUDIO_LINKS = [
  ['/contact', 'Contact'],
  ['/booking', 'Booking'],
  ['/privacy-policy', 'Privacy'],
  ['/terms',   'Terms'],
] as const;

export default function Footer({ settings }: FooterProps) {
  const siteName = settings?.site.name ?? 'Mesh Photography';
  const address  = settings?.contact.address;
  const email    = settings?.contact.email;
  const phone    = settings?.contact.phone;
  const ig       = settings?.social.instagram ?? null;
  const fb       = settings?.social.facebook ?? null;
  const pinterest = (settings?.social as Record<string, string | null>)?.pinterest ?? null;

  return (
    <footer className="bg-ink border-t border-charcoal-light">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
          {/* Brand column */}
          <div>
            <span className="font-display text-2xl text-ivory block mb-3">{siteName}</span>

            {address && (
              <p className="font-body text-xs text-taupe/60 leading-relaxed mb-4">{address}</p>
            )}
            {email && (
              <a
                href={`mailto:${email}`}
                className="font-body text-xs text-taupe/60 hover:text-ivory transition-colors duration-150 block mb-1"
              >
                {email}
              </a>
            )}
            {phone && (
              <a
                href={`tel:${phone}`}
                className="font-body text-xs text-taupe/60 hover:text-ivory transition-colors duration-150 block"
              >
                {phone}
              </a>
            )}
          </div>

          {/* Navigation columns */}
          <div className="grid grid-cols-2 gap-8">
            <div>
              <h4 className="font-body text-xs uppercase tracking-[0.12em] text-taupe mb-4">Explore</h4>
              <nav aria-label="Footer navigation — explore">
                {EXPLORE_LINKS.map(([to, label]) => (
                  <Link
                    key={to}
                    to={to}
                    className="block font-body text-sm text-ivory/50 hover:text-ivory transition-colors duration-150 mb-2"
                  >
                    {label}
                  </Link>
                ))}
              </nav>
            </div>
            <div>
              <h4 className="font-body text-xs uppercase tracking-[0.12em] text-taupe mb-4">Studio</h4>
              <nav aria-label="Footer navigation — studio">
                {STUDIO_LINKS.map(([to, label]) => (
                  <Link
                    key={to}
                    to={to}
                    className="block font-body text-sm text-ivory/50 hover:text-ivory transition-colors duration-150 mb-2"
                  >
                    {label}
                  </Link>
                ))}
              </nav>
            </div>
          </div>

          {/* Social column */}
          <div>
            <h4 className="font-body text-xs uppercase tracking-[0.12em] text-taupe mb-4">Follow Along</h4>
            <div className="flex gap-4">
              {ig && (
                <a
                  href={ig}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label="Instagram"
                  className="text-ivory/40 hover:text-bronze transition-colors duration-150"
                >
                  <Instagram className="h-5 w-5" />
                </a>
              )}
              {fb && (
                <a
                  href={fb}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label="Facebook"
                  className="text-ivory/40 hover:text-bronze transition-colors duration-150"
                >
                  <Facebook className="h-5 w-5" />
                </a>
              )}
              {pinterest && (
                <a
                  href={pinterest}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label="Pinterest"
                  className="text-ivory/40 hover:text-bronze transition-colors duration-150 font-body text-xs"
                >
                  Pinterest
                </a>
              )}
            </div>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="border-t border-charcoal-light pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p className="font-body text-xs text-taupe/40">
            &copy; {new Date().getFullYear()} {siteName}. All rights reserved.
          </p>
          <div className="flex gap-6">
            <Link
              to="/privacy-policy"
              className="font-body text-xs text-taupe/40 hover:text-ivory transition-colors duration-150"
            >
              Privacy Policy
            </Link>
            <Link
              to="/terms"
              className="font-body text-xs text-taupe/40 hover:text-ivory transition-colors duration-150"
            >
              Terms of Service
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
