<?php
$gallery = isset($gallery) && is_array($gallery) ? $gallery : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$media = isset($media) && is_array($media) ? $media : [];
$related = isset($related) && is_array($related) ? $related : [];
$coverUrl = (string) ($coverUrl ?? '');
$mediaPagination = isset($mediaPagination) && is_array($mediaPagination) ? $mediaPagination : ['page' => 1, 'total_pages' => 1, 'total' => count($media)];
?>

<!-- Hero with cover image -->
<section class="page-hero" style="min-height: 40vh;">
    <?php if ($coverUrl !== ''): ?>
        <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>');"></div>
    <?php else: ?>
        <div class="page-hero-bg" style="background: linear-gradient(160deg, #141414 0%, #2e2a26 52%, #6d4f35 100%);"></div>
    <?php endif; ?>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Portfolio', 'href' => '/portfolio'],
            ['label' => (string) ($gallery['title'] ?? 'Gallery')],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <div class="reveal mt-4 max-w-3xl">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($categories as $category): ?>
                    <a href="<?= htmlspecialchars(base_url('portfolio/category/' . rawurlencode((string) ($category['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="tag-pill" style="background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.2);color:rgba(255,255,255,0.8);"><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
            <h1 class="page-hero-title mt-5"><?= htmlspecialchars((string) ($gallery['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-xs font-medium uppercase tracking-[0.16em] text-white/50">
                <?php if ((string) ($gallery['location'] ?? '') !== ''): ?>
                    <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-bronze" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> <?= htmlspecialchars((string) ($gallery['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ((string) ($gallery['client_name'] ?? '') !== ''): ?>
                    <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-bronze" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> <?= htmlspecialchars((string) ($gallery['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ((string) ($gallery['event_date'] ?? '') !== ''): ?>
                    <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-bronze" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> <?= htmlspecialchars((string) ($gallery['event_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <?php if ((string) ($gallery['story_intro'] ?? '') !== ''): ?>
                <p class="page-hero-desc"><?= nl2br(htmlspecialchars((string) ($gallery['story_intro'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
            <?php elseif ((string) ($gallery['excerpt'] ?? '') !== ''): ?>
                <p class="page-hero-desc"><?= htmlspecialchars((string) ($gallery['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Start an Inquiry</a>
                <a href="#gallery-grid" class="btn-secondary" style="border-color:rgba(255,255,255,0.2);color:#fff;">View Frames</a>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Grid -->
<section id="gallery-grid" class="section-lg">
    <div class="container">
        <?php if ($media === []): ?>
            <?= render_component('components/empty-state', [
                'title' => 'No published gallery media is attached yet.',
                'description' => 'Media will appear here once images are uploaded to this gallery in the CMS.',
            ]) ?>
        <?php else: ?>
            <div class="masonry-grid">
                <?php foreach ($media as $index => $item): ?>
                    <figure class="reveal reveal-delay-<?= min(($index % 4) + 1, 4) ?> overflow-hidden rounded-xl border border-charcoal/6 bg-white shadow-sm transition-shadow hover:shadow-md">
                        <?php if ((string) ($item['preview_url'] ?? $item['url'] ?? '') !== ''): ?>
                            <a href="<?= htmlspecialchars((string) ($item['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="block" target="_blank" rel="noreferrer">
                                <img src="<?= htmlspecialchars((string) ($item['preview_url'] ?? $item['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($item['alt_text'] ?? '') !== '' ? ($item['alt_text'] ?? '') : ($gallery['title'] ?? 'Gallery image')), ENT_QUOTES, 'UTF-8') ?>" class="h-auto w-full object-cover" loading="lazy" decoding="async"<?= isset($item['thumb_width']) && (int) $item['thumb_width'] > 0 ? ' width="' . (int) $item['thumb_width'] . '"' : (isset($item['width']) && (int) $item['width'] > 0 ? ' width="' . (int) $item['width'] . '"' : '') ?><?= isset($item['thumb_height']) && (int) $item['thumb_height'] > 0 ? ' height="' . (int) $item['thumb_height'] . '"' : (isset($item['height']) && (int) $item['height'] > 0 ? ' height="' . (int) $item['height'] . '"' : '') ?>>
                            </a>
                        <?php endif; ?>
                        <?php if ((string) ($item['gallery_caption'] ?? '') !== ''): ?>
                            <figcaption class="px-4 py-3 text-sm leading-relaxed text-charcoal/65"><?= htmlspecialchars((string) ($item['gallery_caption'] ?? ''), ENT_QUOTES, 'UTF-8') ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>

            <?php if ((int) ($mediaPagination['total_pages'] ?? 1) > 1): ?>
                <nav class="pagination" aria-label="Gallery media pagination">
                    <?php $prevPage = max(1, (int) ($mediaPagination['page'] ?? 1) - 1); ?>
                    <?php $nextPage = min((int) ($mediaPagination['total_pages'] ?? 1), (int) ($mediaPagination['page'] ?? 1) + 1); ?>
                    <a href="<?= htmlspecialchars(app_href('/portfolio/' . rawurlencode((string) ($gallery['slug'] ?? '')) . '?page=' . $prevPage), ENT_QUOTES, 'UTF-8') ?>" class="<?= (int) ($mediaPagination['page'] ?? 1) <= 1 ? 'page-disabled' : '' ?>">&larr; Prev</a>
                    <span class="page-current"><?= (int) ($mediaPagination['page'] ?? 1) ?></span>
                    <span class="text-sm text-charcoal/40">of <?= (int) ($mediaPagination['total_pages'] ?? 1) ?></span>
                    <a href="<?= htmlspecialchars(app_href('/portfolio/' . rawurlencode((string) ($gallery['slug'] ?? '')) . '?page=' . $nextPage), ENT_QUOTES, 'UTF-8') ?>" class="<?= (int) ($mediaPagination['page'] ?? 1) >= (int) ($mediaPagination['total_pages'] ?? 1) ? 'page-disabled' : '' ?>">Next &rarr;</a>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Related Stories -->
<?php if ($related !== []): ?>
    <section class="section-lg" style="background: var(--color-ivory-warm);">
        <div class="container">
            <div class="reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="section-label">Related Stories</p>
                    <h2 class="text-display-md mt-3">Continue through the collection</h2>
                </div>
                <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost">Browse full portfolio</a>
            </div>
            <div class="mt-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($related as $index => $item): ?>
                    <div class="reveal reveal-delay-<?= min($index + 1, 4) ?>">
                        <?= render_component('components/gallery-card', ['item' => $item, 'variant' => 'compact']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php $ctaBlockHtml = render_reusable_block('global-cta'); ?>
<?php if ($ctaBlockHtml !== ''): ?><div class="mt-10"><?= $ctaBlockHtml ?></div><?php endif; ?>
