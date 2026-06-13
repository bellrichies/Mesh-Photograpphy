<?php
$section = isset($section) && is_array($section) ? $section : [];
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];
$limit = isset($payload['limit']) ? max(1, min(6, (int) $payload['limit'])) : 3;
$items = array_slice(isset($homeContent['featuredGalleries']) && is_array($homeContent['featuredGalleries']) ? $homeContent['featuredGalleries'] : [], 0, $limit);
?>

<section class="section-xl">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header flex flex-wrap items-end justify-between gap-6 reveal">
            <div>
                <span class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? 'Featured Work'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2 class="text-display-md mt-3"><?= htmlspecialchars((string) ($section['title'] ?? 'Selected Projects'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <?php if ((string) ($section['cta_label'] ?? '') !== '' && (string) ($section['cta_url'] ?? '') !== ''): ?>
                <a href="<?= htmlspecialchars(app_href((string) ($section['cta_url'] ?? '#')), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost">
                    <?= htmlspecialchars((string) ($section['cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            <?php else: ?>
                <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost">
                    Browse Portfolio
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Galleries Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php if ($items === []): ?>
                <div class="col-span-full text-center py-16 reveal">
                    <p class="text-slate text-lg">Publish a few galleries to populate this section automatically.</p>
                </div>
            <?php endif; ?>
            
            <?php foreach ($items as $i => $item): ?>
                <div class="reveal reveal-delay-<?= min($i + 1, 4) ?>">
                    <?= render_component('components/gallery-card', ['item' => $item, 'variant' => 'compact']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
