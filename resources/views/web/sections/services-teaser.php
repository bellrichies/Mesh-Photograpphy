<?php
$section = isset($section) && is_array($section) ? $section : [];
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];
$limit = isset($payload['limit']) ? max(1, min(6, (int) $payload['limit'])) : 3;
$items = array_slice(isset($homeContent['services']) && is_array($homeContent['services']) ? $homeContent['services'] : [], 0, $limit);
?>

<section class="section-xl bg-cream relative overflow-hidden">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-bronze/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
    
    <div class="container relative">
        <!-- Section Header -->
        <div class="section-header section-header-center reveal">
            <?php if ((string) ($section['subtitle'] ?? '') !== ''): ?>
                <span class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <h2 class="section-title text-display-md"><?= htmlspecialchars((string) ($section['title'] ?? 'Our Services'), ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        
        <!-- Services Grid -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php if ($items === []): ?>
                <div class="col-span-full text-center py-16 reveal">
                    <p class="text-slate text-lg">Publish a few services to populate this section automatically.</p>
                </div>
            <?php endif; ?>
            
            <?php foreach ($items as $i => $item): ?>
                <article class="card group reveal reveal-delay-<?= min($i + 1, 4) ?>">
                    <a href="<?= htmlspecialchars(base_url('services/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="block h-full">
                        <!-- Image -->
                        <?php if ((string) ($item['cover_url'] ?? '') !== ''): ?>
                            <div class="card-image aspect-4-3">
                                <img src="<?= htmlspecialchars((string) ($item['cover_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="<?= htmlspecialchars((string) (($item['cover_alt_text'] ?? '') !== '' ? ($item['cover_alt_text'] ?? '') : ($item['title'] ?? 'Service cover')), ENT_QUOTES, 'UTF-8') ?>" 
                                     loading="lazy"
                                     decoding="async">
                            </div>
                        <?php else: ?>
                            <div class="card-image aspect-4-3 bg-charcoal flex items-center justify-center">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-white/30">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Content -->
                        <div class="card-content p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <?php if ((string) ($item['price_display'] ?? '') !== ''): ?>
                                        <span class="text-caption" style="color: var(--color-bronze);"><?= htmlspecialchars((string) ($item['price_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <h3 class="text-display-sm mt-2"><?= htmlspecialchars((string) ($item['title'] ?? 'Service'), ENT_QUOTES, 'UTF-8') ?></h3>
                                </div>
                                <span class="flex-shrink-0 w-10 h-10 rounded-full bg-charcoal text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:translate-x-0 -translate-x-1">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </span>
                            </div>
                            
                            <?php if ((string) ($item['short_description'] ?? '') !== ''): ?>
                                <p class="text-body-sm mt-4 text-slate line-clamp-3"><?= htmlspecialchars((string) ($item['short_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        
        <!-- View All Link -->
        <?php if ((string) ($section['cta_label'] ?? '') !== ''): ?>
            <div class="mt-12 text-center reveal">
                <a href="<?= htmlspecialchars(app_href((string) ($section['cta_url'] ?? '/services')), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">
                    <?= htmlspecialchars((string) ($section['cta_label'] ?? 'View All Services'), ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
