<?php
$item = isset($item) && is_array($item) ? $item : [];
$variant = (string) ($variant ?? 'default');
$coverUrl = (string) ($item['cover_card_url'] ?? $item['cover_url'] ?? '');
$coverWidth = isset($item['cover_thumb_width']) ? (int) $item['cover_thumb_width'] : (isset($item['cover_width']) ? (int) $item['cover_width'] : 0);
$coverHeight = isset($item['cover_thumb_height']) ? (int) $item['cover_thumb_height'] : (isset($item['cover_height']) ? (int) $item['cover_height'] : 0);
$imageHeightClass = $variant === 'compact' ? 'aspect-4-3' : ($variant === 'archive' ? '' : 'aspect-3-2');
$cardClasses = 'card group';
$linkClasses = 'block h-full';
$imageClasses = 'card-image relative overflow-hidden bg-charcoal';
$contentClasses = 'card-content p-5';
$excerptClasses = 'text-body-sm text-slate line-clamp-2';

if ($variant === 'archive') {
    $cardClasses .= ' portfolio-card';
    $linkClasses .= ' portfolio-card-link';
    $imageClasses .= ' portfolio-card-image';
    $contentClasses .= ' portfolio-card-content';
    $excerptClasses .= ' portfolio-card-description';
}
?>

<article class="<?= htmlspecialchars($cardClasses, ENT_QUOTES, 'UTF-8') ?>">
    <a href="<?= htmlspecialchars(base_url('portfolio/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="<?= htmlspecialchars($linkClasses, ENT_QUOTES, 'UTF-8') ?>">
        <!-- Image Container -->
        <div class="<?= htmlspecialchars(trim($imageClasses . ' ' . $imageHeightClass), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($coverUrl !== ''): ?>
                <img src="<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>" 
                     alt="<?= htmlspecialchars((string) (($item['cover_alt_text'] ?? '') !== '' ? ($item['cover_alt_text'] ?? '') : ($item['title'] ?? 'Gallery cover')), ENT_QUOTES, 'UTF-8') ?>" 
                     class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.06]" 
                     loading="lazy" 
                     decoding="async"
                     <?= $coverWidth > 0 ? ' width="' . $coverWidth . '"' : '' ?>
                     <?= $coverHeight > 0 ? ' height="' . $coverHeight . '"' : '' ?>>
            <?php else: ?>
                <div class="absolute inset-0" style="background: linear-gradient(155deg, #1a1a1a 0%, #40372f 55%, #8a6a4a 100%);"></div>
            <?php endif; ?>
            
            <!-- Overlay with text -->
            <div class="absolute inset-x-0 bottom-0 px-5 py-5 pt-20" style="background: linear-gradient(to top, rgba(15,15,15,0.85) 0%, rgba(15,15,15,0.4) 40%, transparent 100%);">
                <?php if ((string) ($item['primary_category_name'] ?? '') !== ''): ?>
                    <span class="inline-block text-caption" style="color: var(--color-gold);"><?= htmlspecialchars((string) ($item['primary_category_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <h3 class="text-display-sm mt-2 text-ivory"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                
                <!-- Arrow icon on hover -->
                <span class="absolute right-4 bottom-4 w-10 h-10 rounded-full bg-white/15 backdrop-blur-sm text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="7" y1="17" x2="17" y2="7"></line>
                        <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                </span>
            </div>
        </div>
        
        <!-- Card Body -->
        <div class="<?= htmlspecialchars($contentClasses, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ((string) ($item['excerpt'] ?? '') !== ''): ?>
                <p class="<?= htmlspecialchars($excerptClasses, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($item['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-caption text-taupe">
                <?php if ((string) ($item['location'] ?? '') !== ''): ?>
                    <span class="flex items-center gap-1.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?= htmlspecialchars((string) ($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
                <?php if ((string) ($item['client_name'] ?? '') !== ''): ?>
                    <span class="flex items-center gap-1.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <?= htmlspecialchars((string) ($item['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
                <?php if (isset($item['media_count'])): ?>
                    <span class="flex items-center gap-1.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <?= (int) ($item['media_count'] ?? 0) ?> frames
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</article>
