import { Link } from 'react-router-dom';
import { Instagram, Facebook, Mail, Phone, MapPin } from 'lucide-react';
import type { PublicSettings } from '@/types/models';

interface FooterProps {
  settings?: PublicSettings | null;
}

export default function Footer({ settings }: FooterProps) {
  const siteName = settings?.site.name ?? 'Mesh Photography';
  const tagline  = settings?.site.tagline ?? 'Capturing timeless moments';
  const phone    = settings?.contact.phone;
  const email    = settings?.contact.email;
  const address  = settings?.contact.address;
  const ig       = settings?.social.instagram;
  const fb       = settings?.social.facebook;

  return (
    <footer className="bg-espresso text-ivory">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
          {/* Brand */}
          <div>
            <p className="font-display text-2xl tracking-wide text-gold">{siteName}</p>
            <p className="font-body text-sm text-ivory/60 mt-2">{tagline}</p>

            {/* Social */}
            <div className="flex gap-4 mt-6">
              {ig && (
                <a href={ig} target="_blank" rel="noopener noreferrer" aria-label="Instagram" className="text-ivory/50 hover:text-gold transition-colors">
                  <Instagram size={20} />
                </a>
              )}
              {fb && (
                <a href={fb} target="_blank" rel="noopener noreferrer" aria-label="Facebook" className="text-ivory/50 hover:text-gold transition-colors">
                  <Facebook size={20} />
                </a>
              )}
              {email && (
                <a href={`mailto:${email}`} aria-label="Email" className="text-ivory/50 hover:text-gold transition-colors">
                  <Mail size={20} />
                </a>
              )}
            </div>
          </div>

          {/* Quick links */}
          <div>
            <p className="font-body text-xs tracking-widest uppercase text-taupe mb-5">Explore</p>
            <ul className="space-y-3 font-body text-sm text-ivory/70">
              {[
                ['/', 'Home'],
                ['/portfolio', 'Portfolio'],
                ['/services', 'Services'],
                ['/blog', 'Blog'],
                ['/about', 'About'],
              ].map(([to, label]) => (
                <li key={to}>
                  <Link to={to} className="hover:text-gold transition-colors">{label}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <p className="font-body text-xs tracking-widest uppercase text-taupe mb-5">Get in Touch</p>
            <ul className="space-y-3 font-body text-sm text-ivory/70">
              {phone && (
                <li className="flex items-center gap-2">
                  <Phone size={14} className="text-gold shrink-0" />
                  <a href={`tel:${phone}`} className="hover:text-gold transition-colors">{phone}</a>
                </li>
              )}
              {email && (
                <li className="flex items-center gap-2">
                  <Mail size={14} className="text-gold shrink-0" />
                  <a href={`mailto:${email}`} className="hover:text-gold transition-colors">{email}</a>
                </li>
              )}
              {address && (
                <li className="flex items-start gap-2">
                  <MapPin size={14} className="text-gold shrink-0 mt-0.5" />
                  <span>{address}</span>
                </li>
              )}
            </ul>
            <Link
              to="/booking"
              className="mt-6 inline-block px-6 py-2.5 border border-gold text-gold text-xs tracking-widest uppercase font-body hover:bg-gold hover:text-espresso transition-colors"
            >
              Book a Session
            </Link>
          </div>
        </div>

        <div className="border-t border-ivory/10 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p className="font-body text-xs text-ivory/40">
            &copy; {new Date().getFullYear()} {siteName}. All rights reserved.
          </p>
          <div className="flex gap-6 font-body text-xs text-ivory/40">
            <Link to="/privacy-policy" className="hover:text-ivory/70 transition-colors">Privacy</Link>
            <Link to="/terms"          className="hover:text-ivory/70 transition-colors">Terms</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
