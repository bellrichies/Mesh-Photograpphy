import { Outlet } from 'react-router-dom';

export default function PublicLayout() {
  return (
    <>
      {/* Navbar placeholder — Phase 2 */}
      <header className="sticky top-0 z-50 bg-ivory border-b border-cream">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          <a href="/" className="font-display text-xl text-charcoal tracking-wide">
            Mesh Photography
          </a>
          <nav aria-label="Main navigation">
            <ul className="flex gap-6 text-sm font-body text-taupe">
              <li><a href="/portfolio" className="hover:text-bronze transition-colors">Portfolio</a></li>
              <li><a href="/services" className="hover:text-bronze transition-colors">Services</a></li>
              <li><a href="/blog" className="hover:text-bronze transition-colors">Blog</a></li>
              <li><a href="/contact" className="hover:text-bronze transition-colors">Contact</a></li>
            </ul>
          </nav>
        </div>
      </header>

      <main id="main-content">
        <Outlet />
      </main>

      {/* Footer placeholder — Phase 2 */}
      <footer className="bg-espresso text-ivory py-12 mt-24">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <p className="font-display text-lg tracking-wide">Mesh Photography</p>
          <p className="text-taupe text-sm mt-2 font-body">Capturing timeless moments</p>
        </div>
      </footer>
    </>
  );
}
