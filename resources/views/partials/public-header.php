<?php
$brandingTitle = trim((string) app_setting('branding', 'brand_site_title', ''));
$appName = $brandingTitle !== '' ? $brandingTitle : (string) app_setting('general', 'site_name', config('app.name', 'Mesh Photograph'));
$contactEmail = (string) app_setting('contact', 'contact_email', '');
$contactPhone = (string) app_setting('contact', 'phone', '');
$socialLinks = app_setting('social', 'social_links', []);
$logoMediaId = (int) app_setting('branding', 'logo_media_id', 0);
$logoAltText = trim((string) app_setting('branding', 'logo_alt_text', ''));
$logoUrl = '';

if ($logoMediaId > 0) {
    $logoMedia = (new \App\Models\Media(app_database()))->findById($logoMediaId);
    if (is_array($logoMedia)) {
        $logoUrl = app_media_url((string) ($logoMedia['directory'] ?? ''), (string) ($logoMedia['stored_name'] ?? ''));
    }
}

$navItems = [
    ['label' => 'Home', 'href' => '/', 'exact' => true],
    ['label' => 'Portfolio', 'href' => '/portfolio'],
    ['label' => 'Services', 'href' => '/services'],
    ['label' => 'Blog', 'href' => '/blog'],
    ['label' => 'Testimonials', 'href' => '/testimonials'],
    ['label' => 'About', 'href' => '/about'],
    ['label' => 'Contact', 'href' => '/contact'],
];

$isHomepage = is_current_path('/', true);
?>
<header id="site-header" class="fixed top-0 left-0 right-0 z-40 transition-all duration-500 <?= $isHomepage ? 'header-transparent' : 'header-inner' ?>" data-header data-header-mode="<?= $isHomepage ? 'home' : 'inner' ?>">
    <!-- Top Banner -->
    <div class="header-banner border-b border-white/8 bg-charcoal-dark text-ivory/70">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-5 py-2 text-[10px] uppercase tracking-[0.2em] lg:px-8">
            <div class="flex flex-wrap items-center gap-5">
                <?php if ($contactEmail !== ''): ?>
                    <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-2 transition hover:text-gold">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
                <?php if ($contactPhone !== ''): ?>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone) ?? '', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-2 transition hover:text-gold">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php if (is_array($socialLinks) && $socialLinks !== []): ?>
                <div class="hidden items-center gap-4 md:flex">
                    <?php foreach ($socialLinks as $label => $href): ?>
                        <a href="<?= htmlspecialchars((string) $href, ENT_QUOTES, 'UTF-8') ?>" class="transition hover:text-gold" target="_blank" rel="noreferrer"><?= htmlspecialchars(ucfirst((string) $label), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 lg:px-8 lg:py-5">
        <!-- Logo -->
        <a href="<?= htmlspecialchars(app_href('/'), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-3 text-charcoal transition group focus-visible:outline-bronze">
            <span class="logo-icon inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-full border border-charcoal/10 bg-white font-display text-lg font-semibold text-bronze transition-all duration-300 group-hover:bg-charcoal group-hover:text-gold group-hover:border-charcoal">
                <?php if ($logoUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($logoAltText !== '' ? $logoAltText : $appName, ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    M
                <?php endif; ?>
            </span>
            <span>
                <span class="logo-text block font-display text-[1.65rem] leading-none tracking-wide transition-colors duration-300"><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="logo-subtitle mt-0.5 block text-[10px] uppercase tracking-[0.24em] text-charcoal/50 transition-colors duration-300">Editorial Photograph Studio</span>
            </span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = is_current_path((string) $item['href'], (bool) ($item['exact'] ?? false)); ?>
                <a href="<?= htmlspecialchars(app_href((string) $item['href']), ENT_QUOTES, 'UTF-8') ?>" <?= $isActive ? 'aria-current="page"' : '' ?> class="nav-link rounded-full px-4 py-2 text-[11px] font-medium uppercase tracking-[0.16em] transition-all duration-300 focus-visible:outline-bronze <?= $isActive ? 'bg-charcoal text-ivory' : 'text-charcoal/80 hover:bg-charcoal/5 hover:text-charcoal' ?>"><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="<?= htmlspecialchars(app_href('/booking'), ENT_QUOTES, 'UTF-8') ?>" class="booking-btn hidden items-center gap-2 rounded-full border border-charcoal/15 bg-transparent px-5 py-2.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-charcoal transition-all duration-300 hover:bg-charcoal hover:text-ivory hover:border-charcoal hover:shadow-lg focus-visible:outline-bronze lg:inline-flex">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Book a Session
            </a>
            <button type="button" class="mobile-menu-btn inline-flex h-11 w-11 items-center justify-center rounded-full border border-charcoal/15 bg-white/80 text-charcoal backdrop-blur-sm transition-all duration-300 hover:bg-charcoal hover:text-ivory hover:border-charcoal lg:hidden" aria-expanded="false" aria-controls="mobile-nav-panel" aria-haspopup="dialog" data-mobile-nav-toggle>
                <span class="sr-only">Toggle navigation</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="15" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Nav Overlay -->
    <button type="button" class="pointer-events-none fixed inset-0 z-50 hidden bg-charcoal/50 opacity-0 backdrop-blur-sm transition-opacity duration-300 lg:hidden" aria-label="Close navigation overlay" tabindex="-1" data-mobile-nav-overlay></button>

    <!-- Mobile Navigation Drawer -->
    <nav id="mobile-nav-panel" class="fixed right-0 top-0 z-[60] hidden h-full w-full max-w-sm translate-x-full border-l border-charcoal/8 bg-ivory px-6 pb-8 pt-6 shadow-[-8px_0_40px_-12px_rgba(20,20,20,0.25)] transition-transform duration-300 lg:hidden" data-mobile-nav aria-hidden="true" aria-labelledby="mobile-nav-title" tabindex="-1">
        <div class="flex items-center justify-between">
            <p id="mobile-nav-title" class="text-[10px] font-semibold uppercase tracking-[0.24em] text-bronze">Navigation</p>
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-charcoal/10 bg-white transition-colors hover:bg-charcoal hover:text-ivory" data-mobile-nav-close>
                <span class="sr-only">Close navigation</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="mt-8 space-y-1.5">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = is_current_path((string) $item['href'], (bool) ($item['exact'] ?? false)); ?>
                <a href="<?= htmlspecialchars(app_href((string) $item['href']), ENT_QUOTES, 'UTF-8') ?>" <?= $isActive ? 'aria-current="page"' : '' ?> class="flex items-center gap-3 rounded-2xl px-5 py-3.5 text-sm font-medium uppercase tracking-[0.14em] transition-colors <?= $isActive ? 'bg-charcoal text-ivory' : 'bg-white text-charcoal/80 hover:bg-charcoal/5' ?>" data-mobile-nav-link>
                    <span class="h-1 w-1 rounded-full <?= $isActive ? 'bg-gold' : 'bg-charcoal/20' ?>"></span>
                    <?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-8 overflow-hidden rounded-3xl bg-charcoal px-6 py-7 text-ivory">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gold">Start Your Journey</p>
            <p class="mt-3 text-sm leading-7 text-ivory/65">Ready to create something extraordinary? Let's bring your vision to life.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="rounded-full bg-ivory px-5 py-2.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-charcoal transition-colors hover:bg-gold hover:text-white" data-mobile-nav-link>Contact</a>
                <a href="<?= htmlspecialchars(app_href('/booking'), ENT_QUOTES, 'UTF-8') ?>" class="rounded-full border border-white/20 px-5 py-2.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-ivory transition-colors hover:bg-white/10" data-mobile-nav-link>Book Now</a>
            </div>
        </div>
    </nav>
</header>
<?php if (!$isHomepage): ?>
<div class="h-[calc(theme(spacing.16)+2.75rem)]"></div>
<?php endif; ?>
