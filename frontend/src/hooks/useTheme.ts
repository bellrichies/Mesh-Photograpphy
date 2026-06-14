import { useEffect } from 'react';
import type { ThemeSettings } from '@/types/models';

const GOOGLE_FONT_PAIRS: Record<string, string> = {
  'Cormorant Garamond': 'Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400',
  'Playfair Display':   'Playfair+Display:ital,wght@0,400;0,600;1,400',
  'Libre Baskerville':  'Libre+Baskerville:ital,wght@0,400;0,700;1,400',
  'DM Serif Display':   'DM+Serif+Display:ital@0;1',
  'EB Garamond':        'EB+Garamond:ital,wght@0,400;0,600;1,400',
  'Inter':              'Inter:wght@300;400;500;600',
  'Lato':               'Lato:ital,wght@0,300;0,400;0,700;1,400',
  'Open Sans':          'Open+Sans:ital,wght@0,300;0,400;0,600;1,400',
  'Montserrat':         'Montserrat:ital,wght@0,300;0,400;0,600;1,400',
  'Raleway':            'Raleway:ital,wght@0,300;0,400;0,600;1,400',
};

function loadGoogleFont(family: string): void {
  const key = family in GOOGLE_FONT_PAIRS ? family : null;
  if (!key) return;

  const linkId = `gf-${key.replace(/\s+/g, '-').toLowerCase()}`;
  if (document.getElementById(linkId)) return;

  const link = document.createElement('link');
  link.id   = linkId;
  link.rel  = 'stylesheet';
  link.href = `https://fonts.googleapis.com/css2?family=${GOOGLE_FONT_PAIRS[key]}&display=swap`;
  document.head.appendChild(link);
}

export function useTheme(theme: ThemeSettings | undefined): void {
  useEffect(() => {
    if (!theme) return;

    const root = document.documentElement;

    // Apply colour CSS custom properties
    root.style.setProperty('--theme-primary',   theme.primary_color   || '#C4923B');
    root.style.setProperty('--theme-secondary', theme.secondary_color || '#FAF9F7');
    root.style.setProperty('--theme-accent',    theme.accent_color    || '#B8860B');
    root.style.setProperty('--theme-text',      theme.text_color      || '#1A1A1A');
    root.style.setProperty('--theme-bg',        theme.bg_color        || '#FAF9F7');

    // Apply font family CSS custom properties
    if (theme.display_font) {
      root.style.setProperty('--theme-font-display', `'${theme.display_font}', Georgia, serif`);
      loadGoogleFont(theme.display_font);
    }
    if (theme.body_font) {
      root.style.setProperty('--theme-font-body', `'${theme.body_font}', system-ui, sans-serif`);
      loadGoogleFont(theme.body_font);
    }
  }, [theme]);
}
