<?php
$section = isset($section) && is_array($section) ? $section : [];
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];
$limit = isset($payload['limit']) ? max(1, min(4, (int) $payload['limit'])) : 3;
$items = array_slice(isset($homeContent['featuredPosts']) && is_array($homeContent['featuredPosts']) ? $homeContent['featuredPosts'] : [], 0, $limit);
?>

<section class="section-xl bg-cream relative overflow-hidden">
    <!-- Subtle decorative pattern -->
    <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-bronze/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
    
    <div class="container relative">
        <!-- Section Header -->
        <div class="section-header flex flex-wrap items-end justify-between gap-6 reveal">
            <div>
                <span class="section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? 'Journal'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2 class="text-display-md mt-3"><?= htmlspecialchars((string) ($section['title'] ?? 'From the Journal'), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <a href="<?= htmlspecialchars(app_href('/blog'), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost">
                Read the Journal
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
        
        <!-- Blog Grid -->
        <div class="grid gap-8 lg:grid-cols-3">
            <?php if ($items === []): ?>
                <div class="col-span-full text-center py-16 reveal">
                    <p class="text-slate text-lg">Publish a few blog posts to populate this section automatically.</p>
                </div>
            <?php endif; ?>
            
            <?php foreach ($items as $i => $item): ?>
                <div class="reveal reveal-delay-<?= min($i + 1, 4) ?>">
                    <?= render_component('components/blog-card', ['item' => $item, 'variant' => 'compact']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
