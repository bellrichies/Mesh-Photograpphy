import { useState, useEffect } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { Menu, X } from 'lucide-react';
import { cn } from '@/utils/cn';
import type { PublicSettings } from '@/types/models';

interface NavbarProps {
  settings?: PublicSettings | null;
}

const NAV_LINKS = [
  { to: '/portfolio', label: 'Portfolio' },
  { to: '/services',  label: 'Services' },
  { to: '/blog',      label: 'Blog' },
  { to: '/about',     label: 'About' },
  { to: '/contact',   label: 'Contact' },
];

export default function Navbar({ settings }: NavbarProps) {
  const [menuOpen, setMenuOpen]   = useState(false);
  const [scrolled, setScrolled]   = useState(false);
  const siteName = settings?.site.name ?? 'Mesh Photography';

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    if (menuOpen) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
    return () => { document.body.style.overflow = ''; };
  }, [menuOpen]);

  return (
    <header
      className={cn(
        'fixed top-0 inset-x-0 z-40 transition-all duration-300',
        scrolled ? 'bg-ivory/95 backdrop-blur-sm shadow-soft' : 'bg-transparent'
      )}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        {/* Logo */}
        <Link
          to="/"
          className={cn(
            'font-display text-xl tracking-wide transition-colors',
            scrolled ? 'text-charcoal' : 'text-ivory'
          )}
        >
          {siteName}
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
                      'font-body text-sm tracking-wide transition-colors',
                      isActive ? 'text-bronze' : scrolled ? 'text-taupe hover:text-bronze' : 'text-ivory/80 hover:text-ivory'
                    )
                  }
                >
                  {label}
                </NavLink>
              </li>
            ))}
          </ul>
        </nav>

        {/* Booking CTA */}
        <Link
          to="/booking"
          className={cn(
            'hidden md:inline-block px-5 py-2 text-xs tracking-widest uppercase font-body border transition-colors',
            scrolled
              ? 'border-bronze text-bronze hover:bg-bronze hover:text-ivory'
              : 'border-ivory text-ivory hover:bg-ivory hover:text-charcoal'
          )}
        >
          Book Now
        </Link>

        {/* Mobile toggle */}
        <button
          className={cn('md:hidden transition-colors', scrolled ? 'text-charcoal' : 'text-ivory')}
          onClick={() => setMenuOpen((v) => !v)}
          aria-label={menuOpen ? 'Close menu' : 'Open menu'}
          aria-expanded={menuOpen}
        >
          {menuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden fixed inset-0 top-16 bg-ivory z-30 flex flex-col">
          <nav className="flex-1 px-6 pt-8 pb-6">
            <ul className="flex flex-col gap-6">
              {NAV_LINKS.map(({ to, label }) => (
                <li key={to}>
                  <NavLink
                    to={to}
                    onClick={() => setMenuOpen(false)}
                    className={({ isActive }) =>
                      cn('font-display text-3xl font-light transition-colors', isActive ? 'text-bronze' : 'text-charcoal hover:text-bronze')
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
              className="mt-10 inline-block px-8 py-3 border border-bronze text-bronze font-body text-sm tracking-widest uppercase hover:bg-bronze hover:text-ivory transition-colors"
            >
              Book Now
            </Link>
          </nav>
        </div>
      )}
    </header>
  );
}
