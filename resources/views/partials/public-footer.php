<?php
$socialLinks = app_setting('social', 'social_links', []);
$contactEmail = (string) app_setting('contact', 'contact_email', '');
$contactPhone = (string) app_setting('contact', 'phone', '');
$contactAddress = trim((string) app_setting('contact', 'address', ''));
$businessHours = app_setting('contact', 'business_hours', []);
$footerNoteBlock = published_reusable_block('footer-note');
$footerCtaHtml = render_reusable_block('footer-cta');

$brandingTitle = trim((string) app_setting('branding', 'brand_site_title', ''));
$siteName = $brandingTitle !== '' ? $brandingTitle : (string) app_setting('general', 'site_name', config('app.name', 'Mesh Photograph'));
$tagline = (string) app_setting('general', 'tagline', 'Cinematic imagery for modern stories.');
$footerText = trim((string) app_setting('general', 'footer_text', ''));
$logoMediaId = (int) app_setting('branding', 'logo_media_id', 0);
$logoAltText = trim((string) app_setting('branding', 'logo_alt_text', ''));
$logoUrl = '';

if ($logoMediaId > 0) {
    $logoMedia = (new \App\Models\Media(app_database()))->findById($logoMediaId);
    if (is_array($logoMedia)) {
        $logoUrl = app_media_url((string) ($logoMedia['directory'] ?? ''), (string) ($logoMedia['stored_name'] ?? ''));
    }
}

$businessHoursDays = [];
if (isset($businessHours['days']) && is_array($businessHours['days'])) {
    $businessHoursDays = $businessHours['days'];
} elseif (is_array($businessHours)) {
    foreach ($businessHours as $label => $hours) {
        if ($label === 'timezone') {
            continue;
        }

        $businessHoursDays[] = [
            'label' => ucwords(str_replace(['-', '_'], ' ', (string) $label)),
            'hours' => (string) $hours,
        ];
    }
}

$footerNoteTitle = trim((string) ($footerNoteBlock['title'] ?? ''));
$footerNoteBody = trim((string) ($footerNoteBlock['body'] ?? ''));

if (strcasecmp($footerNoteTitle, $siteName) === 0) {
    $footerNoteTitle = '';
}

if ($footerNoteBody !== '' && strcasecmp($footerNoteBody, $tagline) === 0) {
    $footerNoteBody = '';
}
?>

<footer class="footer">
    <!-- CTA Section -->
    <?php if ($footerCtaHtml !== ''): ?>
        <div class="footer-cta">
            <div class="container">
                <?= $footerCtaHtml ?>
            </div>
        </div>
    <?php else: ?>
        <div class="footer-cta">
            <div class="container">
                <h2 class="footer-cta-title">Let&rsquo;s Create Something Beautiful</h2>
                <p class="footer-cta-description">Ready to capture your story? Get in touch to discuss your vision and let&rsquo;s bring it to life.</p>
                <div class="flex flex-center flex-wrap gap-4 mt-8">
                    <a href="<?= htmlspecialchars(app_href('/booking'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">
                        <span>Start a Project</span>
                    </a>
                    <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary" style="border-color: rgba(255,255,255,0.25); color: #fff;">
                        <span>Get in Touch</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Footer -->
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="footer-logo-icon">
                            <?php if ($logoUrl !== ''): ?>
                                <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($logoAltText !== '' ? $logoAltText : $siteName, ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover">
                            <?php else: ?>
                                M
                            <?php endif; ?>
                        </div>
                        <span class="footer-logo-text"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <p class="footer-tagline"><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></p>
                    
                    <?php if ($footerNoteTitle !== '' || $footerNoteBody !== ''): ?>
                        <div class="footer-tagline">
                            <?php if ($footerNoteTitle !== ''): ?><p class="font-medium text-charcoal"><?= htmlspecialchars($footerNoteTitle, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                            <?php if ($footerNoteBody !== ''): ?><p class="<?= $footerNoteTitle !== '' ? 'mt-1 ' : '' ?>max-w-xl text-sm leading-7 text-charcoal/70"><?= htmlspecialchars($footerNoteBody, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter -->
                    <div class="footer-newsletter">
                        <p class="text-caption text-white/40 mb-3">Stay Inspired</p>
                        <form class="footer-newsletter-form" action="<?= htmlspecialchars(app_href('/api/newsletter'), ENT_QUOTES, 'UTF-8') ?>" method="post">
                            <input type="email" name="email" class="footer-newsletter-input" placeholder="Your email address" required autocomplete="email" aria-label="Email address for newsletter">
                            <button type="submit" class="footer-newsletter-btn">Subscribe</button>
                        </form>
                    </div>
                    
                    <?php if (is_array($socialLinks) && $socialLinks !== []): ?>
                        <div class="footer-social">
                            <?php foreach ($socialLinks as $label => $href): ?>
                                <a href="<?= htmlspecialchars((string) $href, ENT_QUOTES, 'UTF-8') ?>" 
                                   class="footer-social-link" 
                                   target="_blank" 
                                   rel="noreferrer"
                                   aria-label="<?= htmlspecialchars(ucfirst((string) $label), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (strtolower($label) === 'instagram'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                        </svg>
                                    <?php elseif (strtolower($label) === 'facebook'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                        </svg>
                                    <?php elseif (strtolower($label) === 'twitter' || strtolower($label) === 'x'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4l11.733 16h4.267l-11.733 -16z"></path>
                                            <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path>
                                        </svg>
                                    <?php elseif (strtolower($label) === 'pinterest'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M8 12c0-2.5 1.5-5 4-5s4 2.5 4 5c0 3-1.5 5-4 5"></path>
                                            <line x1="12" y1="17" x2="12" y2="22"></line>
                                        </svg>
                                    <?php elseif (strtolower($label) === 'linkedin'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                            <rect x="2" y="9" width="4" height="12"></rect>
                                            <circle cx="4" cy="4" r="2"></circle>
                                        </svg>
                                    <?php elseif (strtolower($label) === 'youtube'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                                        </svg>
                                    <?php else: ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                        </svg>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Contact Column -->
                <div>
                    <h3 class="footer-column-title">Contact</h3>
                    <ul class="footer-links">
                        <?php if ($contactEmail !== ''): ?>
                            <li>
                                <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>" class="footer-contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($contactPhone !== ''): ?>
                            <li>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone) ?? '', ENT_QUOTES, 'UTF-8') ?>" class="footer-contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    <?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($contactAddress !== ''): ?>
                            <li class="footer-contact-item items-start">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 21c4.97-4.97 7.5-8.73 7.5-11.25a7.5 7.5 0 10-15 0C4.5 12.27 7.03 16.03 12 21z"></path>
                                    <circle cx="12" cy="9.75" r="2.25"></circle>
                                </svg>
                                <span><?= nl2br(htmlspecialchars($contactAddress, ENT_QUOTES, 'UTF-8')) ?></span>
                            </li>
                        <?php endif; ?>
                        <?php if ($businessHoursDays !== []): ?>
                            <?php $firstHours = $businessHoursDays[0] ?? []; ?>
                            <li class="footer-contact-item items-start">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 6v6l4 2"></path>
                                    <circle cx="12" cy="12" r="9"></circle>
                                </svg>
                                <span><?= htmlspecialchars(trim((string) ($firstHours['label'] ?? 'Hours')) . ': ' . trim((string) ($firstHours['hours'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="footer-contact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                Start a conversation
                            </a>
                        </li>
                        <li>
                            <a href="<?= htmlspecialchars(app_href('/booking'), ENT_QUOTES, 'UTF-8') ?>" class="footer-contact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Request availability
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Navigation Column -->
                <div>
                    <h3 class="footer-column-title">Explore</h3>
                    <ul class="footer-links">
                        <li><a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>">Portfolio</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/services'), ENT_QUOTES, 'UTF-8') ?>">Services</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/about'), ENT_QUOTES, 'UTF-8') ?>">About</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/blog'), ENT_QUOTES, 'UTF-8') ?>">Journal</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/testimonials'), ENT_QUOTES, 'UTF-8') ?>">Testimonials</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a></li>
                    </ul>
                </div>

                <!-- Legal Column -->
                <div>
                    <h3 class="footer-column-title">Legal</h3>
                    <ul class="footer-links">
                        <li><a href="<?= htmlspecialchars(app_href('/privacy-policy'), ENT_QUOTES, 'UTF-8') ?>">Privacy Policy</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/terms'), ENT_QUOTES, 'UTF-8') ?>">Terms of Service</a></li>
                        <li><a href="<?= htmlspecialchars(app_href('/cookie-policy'), ENT_QUOTES, 'UTF-8') ?>">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p class="footer-copyright">
                    &copy; <?= date('Y') ?> <a href="<?= htmlspecialchars(app_href('/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></a>. All rights reserved.
                </p>
                <p><?= htmlspecialchars($footerText !== '' ? $footerText : 'Designed with passion for visual storytelling', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
</footer>
