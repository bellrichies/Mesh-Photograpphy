<?php
$section = isset($section) && is_array($section) ? $section : [];
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$aboutPage = isset($homeContent['aboutPage']) && is_array($homeContent['aboutPage']) ? $homeContent['aboutPage'] : [];

$body = trim((string) ($section['body'] ?? ''));
if ($body === '') {
    $body = trim((string) ($aboutPage['excerpt'] ?? ''));
}

if ($body === '') {
    $body = trim((string) ($homeContent['tagline'] ?? ''));
}
?>

<section class="section-xl relative overflow-hidden">
    <!-- Subtle background pattern -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, #1a1a1a 1px, transparent 0); background-size: 40px 40px;"></div>
    
    <div class="container relative">
        <div class="grid gap-10 lg:grid-cols-[1.15fr,0.85fr] lg:gap-16 items-center">
            <!-- Main Content -->
            <div class="reveal">
                <span class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? 'About Our Studio'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2 class="text-display-lg mt-4"><?= htmlspecialchars((string) ($section['title'] ?? ($aboutPage['title'] ?? 'A studio built around honest, elegant storytelling.')), ENT_QUOTES, 'UTF-8') ?></h2>
                
                <?php if ($body !== ''): ?>
                    <p class="mt-6 text-lg leading-8 text-charcoal/65"><?= nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>
                
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="<?= htmlspecialchars(app_href((string) (($section['cta_url'] ?? '') !== '' ? ($section['cta_url'] ?? '') : (($aboutPage['slug'] ?? '') !== '' ? '/' . ltrim((string) ($aboutPage['slug'] ?? ''), '/') : '/about'))), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">
                        <span>Discover the Studio</span>
                    </a>
                    <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">
                        <span>View Signature Work</span>
                    </a>
                </div>
            </div>
            
            <!-- Feature Card -->
            <div class="reveal reveal-delay-2">
                <div class="rounded-3xl bg-charcoal p-8 text-ivory lg:p-10 relative overflow-hidden">
                    <!-- Decorative gradient -->
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-bronze/10 blur-3xl"></div>
                    
                    <span class="text-caption-lg text-gold relative">What Shapes The Work</span>
                    <ul class="mt-8 space-y-6 relative">
                        <li class="flex gap-5 group">
                            <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-bronze/15 flex items-center justify-center text-bronze-light transition-all duration-300 group-hover:bg-bronze/25 group-hover:scale-110">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </span>
                            <div>
                                <p class="font-display text-lg font-semibold text-ivory">Guided Direction</p>
                                <p class="mt-1 text-sm leading-relaxed text-white/55">Calm, guided direction that keeps the experience natural in front of the camera.</p>
                            </div>
                        </li>
                        <li class="flex gap-5 group">
                            <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-bronze/15 flex items-center justify-center text-bronze-light transition-all duration-300 group-hover:bg-bronze/25 group-hover:scale-110">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </span>
                            <div>
                                <p class="font-display text-lg font-semibold text-ivory">Editorial Composition</p>
                                <p class="mt-1 text-sm leading-relaxed text-white/55">Restraint to preserve real emotion and movement in every frame.</p>
                            </div>
                        </li>
                        <li class="flex gap-5 group">
                            <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-bronze/15 flex items-center justify-center text-bronze-light transition-all duration-300 group-hover:bg-bronze/25 group-hover:scale-110">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                            </span>
                            <div>
                                <p class="font-display text-lg font-semibold text-ivory">Premium Planning</p>
                                <p class="mt-1 text-sm leading-relaxed text-white/55">Full support across timelines, locations, and visual priorities.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
