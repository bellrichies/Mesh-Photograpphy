import { useState, useEffect } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import { Menu, X } from 'lucide-react';
import { cn } from '@/utils/cn';
import type { PublicSettings } from '@/types/models';

interface NavbarProps {
  settings?: PublicSettings | null;
}

const NAV_LINKS = [
  { to: '/about',     label: 'About' },
  { to: '/services',  label: 'Services' },
  { to: '/portfolio', label: 'Portfolio' },
  { to: '/blog',      label: 'Blog' },
  { to: '/contact',   label: 'Contact' },
];

export default function Navbar({ settings }: NavbarProps) {
  const [menuOpen, setMenuOpen] = useState(false);
  const [scrolled, setScrolled]  = useState(false);
  const { pathname } = useLocation();
  const siteName = settings?.site.name ?? 'Mesh Photography';
  const logoUrl = settings?.site.logo_url ?? null;
  const ig = settings?.social.instagram ?? null;
  const fb = settings?.social.facebook ?? null;

  // Only show the transparent/ivory-text style on the homepage before the user
  // scrolls. All other pages have light backgrounds so we need dark text from
  // the very first pixel. Collapse to solid when the mobile menu is open so the
  // header bar is always readable over the full-screen mobile nav overlay.
  const transparent = pathname === '/' && !scrolled && !menuOpen;

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 80);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = menuOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [menuOpen]);

  return (
    <header
      className={cn(
        'fixed top-0 inset-x-0 z-50 transition-all duration-300',
        transparent
          ? 'bg-transparent border-b border-transparent'
          : 'bg-ivory/95 backdrop-blur-sm border-b border-cream shadow-soft'
      )}
    >
      {/* Skip to content */}
      <a
        href="#main-content"
        className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-bronze focus:text-ivory focus:text-sm focus:font-body focus:rounded"
      >
        Skip to content
      </a>

      <div className="site-container h-[72px] flex items-center justify-between">
        {/* Logo */}
        <Link
          to="/"
          className={cn(
            'inline-flex items-center gap-2.5 transition-colors duration-300',
            transparent ? 'text-ivory' : 'text-charcoal'
          )}
          aria-label={`${siteName} home`}
        >
          {logoUrl && (
            <img
              src={logoUrl}
              alt=""
              className="h-9 w-auto max-w-[140px] object-contain"
              loading="eager"
              decoding="async"
            />
          )}
          <span className="font-display text-xl lg:text-[22px] tracking-wide">{siteName}</span>
        </Link>

        {/* Desktop nav */}
        <nav aria-label="Main navigation" className="hidden md:block">
          <ul className="flex gap-7">
            {NAV_LINKS.map(({ to, label }) => (
              <li key={to}>
                <NavLink
                  to={to}
                  className={({ isActive }) =>
                    cn(
                      'font-body text-sm font-medium tracking-wide transition-colors duration-150',
                      isActive
                        ? 'text-bronze font-semibold'
                        : transparent
                        ? 'text-ivory/80 hover:text-ivory'
                        : 'text-charcoal hover:text-bronze'
                    )
                  }
                >
                  {label}
                </NavLink>
              </li>
            ))}
          </ul>
        </nav>

        {/* Book CTA */}
        <Link
          to="/booking"
          className={cn(
            'hidden md:inline-block px-5 py-2 text-xs tracking-widest uppercase font-body border transition-colors duration-150',
            transparent
              ? 'border-ivory/70 text-ivory hover:bg-ivory hover:text-charcoal'
              : 'border-bronze text-bronze hover:bg-bronze hover:text-ivory'
          )}
        >
          Book a Session
        </Link>

        {/* Mobile toggle */}
        <button
          className={cn('md:hidden p-1 transition-colors', transparent ? 'text-ivory' : 'text-charcoal')}
          onClick={() => setMenuOpen((v) => !v)}
          aria-label={menuOpen ? 'Close navigation menu' : 'Open navigation menu'}
          aria-expanded={menuOpen}
          aria-controls="mobile-nav"
        >
          {menuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile overlay */}
      <div
        id="mobile-nav"
        role="dialog"
        aria-label="Navigation menu"
        aria-hidden={!menuOpen}
        className={cn(
          'md:hidden fixed inset-0 top-[72px] bg-ivory z-40 flex flex-col transition-transform duration-300 ease-out shadow-lg',
          menuOpen ? 'translate-x-0' : 'translate-x-full'
        )}
      >
        <nav className="flex-1 px-6 pt-10 pb-8">
          <ul className="flex flex-col gap-6 mb-10">
            {NAV_LINKS.map(({ to, label }) => (
              <li key={to}>
                <NavLink
                  to={to}
                  onClick={() => setMenuOpen(false)}
                  className={({ isActive }) =>
                    cn(
                      'font-display text-3xl font-light transition-colors duration-150',
                      isActive ? 'text-bronze' : 'text-charcoal hover:text-bronze'
                    )
                  }
                >
                  {label}
                </NavLink>
              </li>
            ))}
          </ul>

          <Link
            to="/booking"
            onClick={() => setMenuOpen(false)}
            className="inline-block px-8 py-3 bg-bronze text-ivory font-body text-sm tracking-widest uppercase hover:bg-bronze-dark transition-colors duration-150"
          >
            Book a Session
          </Link>

          {(ig || fb) && (
            <div className="flex gap-5 mt-10">
              {ig && (
                <a
                  href={ig}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label="Instagram"
                  className="font-body text-sm text-taupe hover:text-bronze transition-colors"
                >
                  Instagram
                </a>
              )}
              {fb && (
                <a
                  href={fb}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label="Facebook"
                  className="font-body text-sm text-taupe hover:text-bronze transition-colors"
                >
                  Facebook
                </a>
              )}
            </div>
          )}
        </nav>
      </div>
    </header>
  );
}
