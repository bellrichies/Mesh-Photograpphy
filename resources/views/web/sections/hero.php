<?php
$section = isset($section) && is_array($section) ? $section : [];
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];

// Prefer admin-managed slides, then fall back to section payload slides.
$slidesData = isset($homeContent['heroSlides']) && is_array($homeContent['heroSlides']) ? $homeContent['heroSlides'] : [];

if (empty($slidesData)) {
    $slidesData = isset($payload['slides']) && is_array($payload['slides']) ? $payload['slides'] : [];
}

// If no slides data, try to get from homeContent heroImages
if (empty($slidesData) && isset($homeContent['heroImages']) && is_array($homeContent['heroImages'])) {
    $slidesData = $homeContent['heroImages'];
}

// Build slides array
$slides = [];
if (!empty($slidesData)) {
    foreach ($slidesData as $slide) {
        $slides[] = [
            'image_url' => (string) ($slide['url'] ?? $slide['image_url'] ?? ''),
            'image_alt' => (string) ($slide['alt'] ?? $slide['image_alt'] ?? 'Hero slide image'),
            'title' => (string) ($slide['title'] ?? ''),
            'subtitle' => (string) ($slide['subtitle'] ?? ''),
            'description' => (string) ($slide['description'] ?? ''),
            'cta_label' => (string) ($slide['cta_label'] ?? ''),
            'cta_url' => (string) ($slide['cta_url'] ?? ''),
            'secondary_cta_label' => (string) ($slide['secondary_cta_label'] ?? ''),
            'secondary_cta_url' => (string) ($slide['secondary_cta_url'] ?? ''),
        ];
    }
}

// Fallback slide if no slides available
if (empty($slides)) {
    $slides[] = [
        'image_url' => (string) ($section['media_url'] ?? ''),
        'image_alt' => (string) ($section['media_alt'] ?? ($section['title'] ?? 'Homepage hero')),
        'title' => (string) ($section['title'] ?? ($homeContent['tagline'] ?? 'Crafted stories, timeless frames.')),
        'subtitle' => (string) ($homeContent['siteName'] ?? 'Mesh Photography'),
        'description' => (string) ($section['body'] ?? ''),
        'cta_label' => (string) ($section['cta_label'] ?? ''),
        'cta_url' => (string) ($section['cta_url'] ?? '/portfolio'),
        'secondary_cta_label' => 'View Portfolio',
        'secondary_cta_url' => '/portfolio',
    ];
}

// Slider settings
$autoplay = isset($payload['autoplay']) ? (bool) $payload['autoplay'] : true;
$autoplayDelay = isset($payload['autoplay_delay']) ? (int) $payload['autoplay_delay'] : 6000;
$transitionSpeed = isset($payload['transition_speed']) ? (int) $payload['transition_speed'] : 700;
$showDots = isset($payload['show_dots']) ? (bool) $payload['show_dots'] : true;
$showArrows = isset($payload['show_arrows']) ? (bool) $payload['show_arrows'] : true;

$sliderId = 'hero-slider-' . uniqid();
?>

<?php if (count($slides) > 0): ?>
<section class="hero-slider <?= $sliderId ?>" 
     data-autoplay="<?= $autoplay ? 'true' : 'false' ?>" 
     data-autoplay-delay="<?= $autoplayDelay ?>"
     data-transition-speed="<?= $transitionSpeed ?>"
     data-show-dots="<?= $showDots ? 'true' : 'false' ?>"
     data-show-arrows="<?= $showArrows ? 'true' : 'false' ?>"
     aria-roledescription="carousel"
     aria-label="Featured photography showcase"
     tabindex="0">
    
    <!-- Slider Track -->
    <div class="hero-slider-track">
        <?php foreach ($slides as $index => $slide): ?>
            <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" 
                 data-slide-index="<?= $index ?>"
                 role="group"
                 aria-roledescription="slide"
                 aria-label="Slide <?= $index + 1 ?> of <?= count($slides) ?>">
                <?php if (!empty($slide['image_url'])): ?>
                    <div class="hero-slide-image">
                        <img src="<?= htmlspecialchars($slide['image_url'], ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($slide['image_alt'], ENT_QUOTES, 'UTF-8') ?>" 
                             loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                             fetchpriority="<?= $index === 0 ? 'high' : 'auto' ?>">
                    </div>
                <?php else: ?>
                    <div class="hero-slide-image" style="background: linear-gradient(145deg, #0f0f0f 0%, #1e1a16 40%, #3d2e1f 80%, #5c4228 100%);">
                        <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 30% 30%,rgba(196,167,125,0.08),transparent 50%),radial-gradient(ellipse at 70% 70%,rgba(154,123,92,0.06),transparent 50%);"></div>
                    </div>
                <?php endif; ?>

                <div class="hero-slide-overlay"></div>

                <div class="hero-slide-shell md:max-w-7xl lg:max-w-[1400px] mx-auto">
                    <div class="hero-slide-content">
                        <div class="hero-slide-content-inner">
                            <?php if (!empty($slide['subtitle'])): ?>
                                <span class="hero-slide-label animate-fade-in-up"><?= htmlspecialchars($slide['subtitle'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>

                            <?php if (!empty($slide['title'])): ?>
                                <h1 class="text-gray-100 text-display-lg animate-fade-in-up delay-100"><?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                            <?php endif; ?>

                            <?php if (!empty($slide['description'])): ?>
                                <p class="hero-slide-description animate-fade-in-up delay-200"><?= htmlspecialchars($slide['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>

                            <?php if (!empty($slide['cta_label']) || !empty($slide['secondary_cta_label'])): ?>
                                <div class="hero-slide-cta animate-fade-in-up delay-300">
                                    <?php if (!empty($slide['cta_label']) && !empty($slide['cta_url'])): ?>
                                        <a href="<?= htmlspecialchars(app_href($slide['cta_url']), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">
                                            <span><?= htmlspecialchars($slide['cta_label'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($slide['secondary_cta_label']) && !empty($slide['secondary_cta_url'])): ?>
                                        <a href="<?= htmlspecialchars(app_href($slide['secondary_cta_url']), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary hero-slide-secondary-cta">
                                            <span><?= htmlspecialchars($slide['secondary_cta_label'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Slide Counter -->
    <?php if (count($slides) > 1): ?>
    <div class="absolute bottom-8 right-5 z-10 hidden items-center gap-3 font-display text-sm text-white/60 md:flex lg:right-8">
        <span class="slide-counter-current text-xl font-semibold text-white">01</span>
        <span class="h-px w-6 bg-white/30"></span>
        <span class="slide-counter-total"><?= str_pad((string) count($slides), 2, '0', STR_PAD_LEFT) ?></span>
    </div>
    <?php endif; ?>
    
    <!-- Progress Bar -->
    <div class="hero-slider-progress" style="width: 0%;"></div>
    
    <!-- Navigation Dots -->
    <?php if ($showDots && count($slides) > 1): ?>
        <div class="hero-slider-dots" role="tablist" aria-label="Slide navigation">
            <?php foreach ($slides as $index => $slide): ?>
                <button type="button" 
                        class="hero-slider-dot <?= $index === 0 ? 'active' : '' ?>" 
                        data-slide-dot="<?= $index ?>"
                        role="tab" 
                        aria-label="Go to slide <?= $index + 1 ?>"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 z-10 hidden -translate-x-1/2 flex-col items-center gap-2 md:flex" data-scroll-indicator>
        <span class="text-[9px] font-semibold uppercase tracking-[0.3em] text-white/40">Scroll</span>
        <div class="h-10 w-px bg-gradient-to-b from-white/40 to-transparent" style="animation: pulse-glow 2s ease-in-out infinite;"></div>
    </div>
</section>
<?php endif; ?>
