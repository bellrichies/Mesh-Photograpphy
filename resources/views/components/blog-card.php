<?php
$item = isset($item) && is_array($item) ? $item : [];
$variant = (string) ($variant ?? 'default');
$imageUrl = (string) ($item['featured_image_card_url'] ?? $item['featured_image_url'] ?? '');
$imageWidth = isset($item['featured_image_thumb_width']) ? (int) $item['featured_image_thumb_width'] : (isset($item['featured_image_width']) ? (int) $item['featured_image_width'] : 0);
$imageHeight = isset($item['featured_image_thumb_height']) ? (int) $item['featured_image_thumb_height'] : (isset($item['featured_image_height']) ? (int) $item['featured_image_height'] : 0);
$imageHeightClass = $variant === 'compact' ? 'aspect-4-3' : 'aspect-3-2';
$categories = isset($item['categories']) && is_array($item['categories']) ? $item['categories'] : [];
?>
<article class="card group h-full">
    <a href="<?= htmlspecialchars(base_url('blog/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="block h-full">
        <div class="card-image <?= $imageHeightClass ?> overflow-hidden" style="background: linear-gradient(160deg, #171717 0%, #3a332d 56%, #8a6a4a 100%);">
            <?php if ($imageUrl !== ''): ?>
                <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" 
                     alt="<?= htmlspecialchars((string) (($item['featured_image_alt_text'] ?? '') !== '' ? ($item['featured_image_alt_text'] ?? '') : ($item['title'] ?? 'Blog image')), ENT_QUOTES, 'UTF-8') ?>" 
                     class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.06]" 
                     loading="lazy" 
                     decoding="async"
                     <?= $imageWidth > 0 ? ' width="' . $imageWidth . '"' : '' ?>
                     <?= $imageHeight > 0 ? ' height="' . $imageHeight . '"' : '' ?>>
            <?php endif; ?>
        </div>
        <div class="card-content p-6 flex flex-col gap-4">
            <?php if ($categories !== []): ?>
                <div class="flex flex-wrap gap-2 text-caption" style="color: var(--color-bronze);">
                    <?php foreach (array_slice($categories, 0, 2) as $category): ?>
                        <span><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <h3 class="text-display-sm text-charcoal"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if ((string) ($item['summary'] ?? $item['excerpt'] ?? '') !== ''): ?>
                <p class="line-clamp-3 text-body-sm text-slate"><?= htmlspecialchars((string) ($item['summary'] ?? $item['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-caption text-taupe mt-auto pt-2">
                <?php if ((string) ($item['published_label'] ?? '') !== ''): ?>
                    <span><?= htmlspecialchars((string) ($item['published_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ((string) ($item['reading_time_label'] ?? '') !== ''): ?>
                    <span><?= htmlspecialchars((string) ($item['reading_time_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ((string) ($item['author_name'] ?? '') !== ''): ?>
                    <span><?= htmlspecialchars((string) ($item['author_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</article>