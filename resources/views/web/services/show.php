<?php
$service = isset($service) && is_array($service) ? $service : [];
$relatedServices = isset($relatedServices) && is_array($relatedServices) ? $relatedServices : [];
$relatedGalleries = isset($relatedGalleries) && is_array($relatedGalleries) ? $relatedGalleries : [];
$seo = isset($seo) && is_array($seo) ? $seo : [];

$coverDirectory = trim((string) ($service['cover_directory'] ?? ''), '/');
$coverStoredName = (string) ($service['cover_stored_name'] ?? '');
$coverUrl = app_media_url($coverDirectory, $coverStoredName);
?>

<!-- Hero with cover -->
<section class="page-hero" style="min-height: 38vh;">
    <?php if ($coverUrl !== ''): ?>
        <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>');"></div>
    <?php else: ?>
        <div class="page-hero-bg" style="background: linear-gradient(160deg, #141414 0%, #2e2a26 52%, #6d4f35 100%);"></div>
    <?php endif; ?>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Services', 'href' => '/services'],
            ['label' => (string) ($service['title'] ?? 'Service')],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <div class="reveal mt-4 max-w-3xl">
            <h1 class="page-hero-title"><?= htmlspecialchars((string) ($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ((string) ($service['price_display'] ?? '') !== ''): ?>
                <div class="mt-5"><span class="price-badge"><?= htmlspecialchars((string) ($service['price_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
            <?php endif; ?>
            <?php if ((string) ($service['short_description'] ?? '') !== ''): ?>
                <p class="page-hero-desc"><?= htmlspecialchars((string) ($service['short_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Inquire About This Service</a>
                <a href="<?= htmlspecialchars(base_url('booking?service=' . rawurlencode((string) ($service['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary" style="border-color:rgba(255,255,255,0.2);color:#fff;">Check Availability</a>
                <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost" style="color:rgba(255,255,255,0.7);">View Portfolio</a>
            </div>
        </div>
    </div>
</section>

<!-- Full Description -->
<?php if ((string) ($service['full_description'] ?? '') !== ''): ?>
    <section class="section-lg">
        <div class="container">
            <div class="reveal prose-content mx-auto max-w-3xl">
                <?= nl2br(htmlspecialchars((string) ($service['full_description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Related Services -->
<?php if ($relatedServices !== []): ?>
    <section class="section-lg" style="background: var(--color-ivory-warm);">
        <div class="container">
            <div class="reveal">
                <p class="section-label">Related Services</p>
                <h2 class="text-display-md mt-3">You may also be interested in</h2>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($relatedServices as $index => $item): ?>
                    <a href="<?= htmlspecialchars(base_url('services/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="reveal reveal-delay-<?= min($index + 1, 4) ?> info-card flex-col gap-0 p-6 hover:-translate-y-1 transition-transform">
                        <h3 class="font-display text-xl font-light text-charcoal"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if ((string) ($item['short_description'] ?? '') !== ''): ?><p class="mt-3 text-sm leading-relaxed text-charcoal/65"><?= htmlspecialchars((string) ($item['short_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Related Galleries -->
<?php if ($relatedGalleries !== []): ?>
    <section class="section-xl" style="background: var(--color-charcoal); color: #fff;">
        <div class="container">
            <div class="reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="section-label">Featured Galleries</p>
                    <h2 class="text-display-md mt-3 text-white">Recent work connected to the experience</h2>
                </div>
                <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-ghost" style="color:rgba(255,255,255,0.7);">Browse portfolio</a>
            </div>
            <div class="mt-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($relatedGalleries as $index => $item): ?>
                    <?php
                    $galleryDirectory = trim((string) ($item['cover_directory'] ?? ''), '/');
                    $galleryStoredName = (string) ($item['cover_stored_name'] ?? '');
                    $galleryCoverUrl = app_media_url($galleryDirectory, $galleryStoredName);
                    ?>
                    <a href="<?= htmlspecialchars(base_url('portfolio/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="reveal reveal-delay-<?= min($index + 1, 4) ?> group overflow-hidden rounded-xl transition hover:-translate-y-1">
                        <div class="aspect-[4/3] overflow-hidden rounded-xl bg-white/8">
                            <?php if ($galleryCoverUrl !== ''): ?><img src="<?= htmlspecialchars($galleryCoverUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($item['cover_alt_text'] ?? '') !== '' ? ($item['cover_alt_text'] ?? '') : ($item['title'] ?? 'Gallery cover')), ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy"><?php endif; ?>
                        </div>
                        <h3 class="mt-4 font-display text-xl font-light text-white"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
