<?php
$items = isset($items) && is_array($items) ? $items : [];
$page = isset($page) && is_array($page) ? $page : [];
$heroBlock = published_reusable_block('services-hero');
$introBlockHtml = render_reusable_block('services-intro');
$afterGridBlockHtml = render_reusable_block('services-after-grid');
$heroTitle = (string) (($heroBlock['title'] ?? '') !== '' ? ($heroBlock['title'] ?? '') : (($page['title'] ?? '') !== '' ? ($page['title'] ?? '') : 'Offerings designed around story, care & calm rhythm'));
$heroDescription = (string) (($heroBlock['body'] ?? '') !== '' ? ($heroBlock['body'] ?? '') : (($page['excerpt'] ?? '') !== '' ? ($page['excerpt'] ?? '') : 'Explore the signature photography services managed from the CMS, each with its own positioning, editorial description, and optional detail page.'));
$pageIntro = trim((string) ($page['body'] ?? ''));
?>

<!-- Page Hero -->
<section class="page-hero" style="background: var(--color-charcoal);">
    <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars(asset_url('assets/images/services-hero.jpg'), ENT_QUOTES, 'UTF-8') ?>'); opacity: 0.2;"></div>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Services'],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <div class="reveal mt-4 max-w-3xl">
            <p class="page-hero-eyebrow">Services</p>
            <h1 class="page-hero-title"><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="page-hero-desc"><?= htmlspecialchars($heroDescription, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>

<?php if ($introBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $introBlockHtml ?>
    </div>
</section>
<?php elseif ($pageIntro !== ''): ?>
<section class="section">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl text-center">
            <p class="text-body-md leading-8 text-charcoal/70"><?= nl2br(htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Services Grid -->
<section class="section-lg">
    <div class="container">
        <?php if ($items === []): ?>
            <?= render_component('components/empty-state', [
                'title' => 'No published services are available yet.',
                'description' => 'Services will appear here once published in the CMS.',
            ]) ?>
        <?php endif; ?>
        <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($items as $index => $item): ?>
                <?php
                $coverUrl = app_media_url(
                    (string) ($item['cover_directory'] ?? ''),
                    (string) ($item['cover_stored_name'] ?? '')
                );
                ?>
                <article class="card service-card reveal reveal-delay-<?= min(($index % 3) + 1, 4) ?>">
                    <a href="<?= htmlspecialchars(base_url('services/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="block h-full service-card-link">
                        <div class="card-image service-card-image">
                            <?php if ($coverUrl !== ''): ?>
                                <img src="<?= htmlspecialchars($coverUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($item['cover_alt_text'] ?? '') !== '' ? ($item['cover_alt_text'] ?? '') : ($item['title'] ?? 'Service cover')), ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php else: ?>
                                <div class="absolute inset-0" style="background: linear-gradient(160deg, #141414 0%, #2e2a26 52%, #6d4f35 100%);"></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-content service-card-content">
                            <div class="flex items-start justify-between gap-3">
                                <h2 class="font-display text-2xl font-light text-charcoal"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                                <?php if ((string) ($item['price_display'] ?? '') !== ''): ?><span class="price-badge"><?= htmlspecialchars((string) ($item['price_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                            </div>
                            <?php if ((string) ($item['short_description'] ?? '') !== ''): ?><p class="mt-3 text-sm leading-relaxed text-charcoal/65 service-card-description"><?= htmlspecialchars((string) ($item['short_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($afterGridBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $afterGridBlockHtml ?>
    </div>
</section>
<?php endif; ?>
