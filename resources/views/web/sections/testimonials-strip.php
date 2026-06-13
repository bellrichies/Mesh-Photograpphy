<?php
$section = isset($section) && is_array($section) ? $section : [];
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];
$limit = isset($payload['limit']) ? max(1, min(6, (int) $payload['limit'])) : 3;
$items = array_slice(isset($homeContent['testimonials']) && is_array($homeContent['testimonials']) ? $homeContent['testimonials'] : [], 0, $limit);
?>

<section class="section-xl bg-charcoal text-ivory relative overflow-hidden">
    <!-- Decorative radial gradient -->
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true" style="background: radial-gradient(ellipse at 30% 0%, rgba(154,123,92,0.1), transparent 50%), radial-gradient(ellipse at 70% 100%, rgba(196,167,125,0.06), transparent 50%);"></div>
    
    <div class="container relative">
        <!-- Section Header -->
        <div class="section-header flex flex-wrap items-end justify-between gap-6 reveal">
            <div>
                <span class="section-label" style="color: var(--color-gold);"><?= htmlspecialchars((string) ($section['subtitle'] ?? 'Testimonials'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2 class="text-display-md mt-3 text-ivory"><?= htmlspecialchars((string) ($section['title'] ?? 'Client Stories'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <a href="<?= htmlspecialchars(app_href('/testimonials'), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost btn-ghost-light">
                Read All Reviews
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
        
        <!-- Testimonials Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php if ($items === []): ?>
                <div class="col-span-full text-center py-16 reveal">
                    <p class="text-white/70 text-lg">Publish a few testimonials to populate this section automatically.</p>
                </div>
            <?php endif; ?>
            
            <?php foreach ($items as $i => $item): ?>
                <div class="reveal reveal-delay-<?= min($i + 1, 4) ?>">
                    <?= render_component('components/testimonial-card', ['item' => $item]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
