<?php
$items = isset($items) && is_array($items) ? $items : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$activeCategory = isset($activeCategory) && is_array($activeCategory) ? $activeCategory : null;
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$page = isset($page) && is_array($page) ? $page : [];
$query = (string) ($query ?? '');

$basePath = $activeCategory !== null
    ? '/portfolio/category/' . rawurlencode((string) ($activeCategory['slug'] ?? ''))
    : '/portfolio';
$heroBlock = published_reusable_block('portfolio-hero');
$introBlockHtml = render_reusable_block('portfolio-intro');
$afterGridBlockHtml = render_reusable_block('portfolio-after-grid');
$defaultHeroTitle = (string) (($page['title'] ?? '') !== '' ? ($page['title'] ?? '') : 'Stories shaped by light, place & atmosphere');
$defaultHeroDescription = (string) (($page['excerpt'] ?? '') !== '' ? ($page['excerpt'] ?? '') : 'Browse editorial wedding stories, portrait collections, and refined commercial work curated from the CMS-managed gallery archive.');
$pageIntro = trim((string) ($page['body'] ?? ''));
?>

<!-- Page Hero -->
<section class="page-hero" style="background: var(--color-charcoal);">
    <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars(asset_url('assets/images/portfolio-hero.jpg'), ENT_QUOTES, 'UTF-8') ?>'); opacity: 0.25;"></div>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => $activeCategory !== null ? (string) ($activeCategory['name'] ?? 'Portfolio') : 'Portfolio'],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <div class="mt-4 grid items-end gap-8 lg:grid-cols-2">
            <div class="reveal">
                <p class="page-hero-eyebrow">Portfolio</p>
                <h1 class="page-hero-title">
                    <?= $activeCategory !== null
                        ? htmlspecialchars((string) ($activeCategory['name'] ?? 'Portfolio'), ENT_QUOTES, 'UTF-8')
                        : htmlspecialchars((string) (($heroBlock['title'] ?? '') !== '' ? ($heroBlock['title'] ?? '') : $defaultHeroTitle), ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <p class="page-hero-desc">
                    <?= $activeCategory !== null && (string) ($activeCategory['description'] ?? '') !== ''
                        ? htmlspecialchars((string) ($activeCategory['description'] ?? ''), ENT_QUOTES, 'UTF-8')
                        : htmlspecialchars((string) (($heroBlock['body'] ?? '') !== '' ? ($heroBlock['body'] ?? '') : $defaultHeroDescription), ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
            <div class="reveal reveal-delay-2">
                <form method="get" action="<?= htmlspecialchars(app_href($basePath), ENT_QUOTES, 'UTF-8') ?>" class="search-bar">
                    <input type="text" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by title, client, or location&hellip;">
                    <button type="submit" aria-label="Search">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </button>
                </form>
                <div class="mt-4 flex items-center justify-between text-sm text-white/55">
                    <span><?= (int) ($pagination['total'] ?? 0) ?> published galleries</span>
                    <?php if ($activeCategory !== null): ?><a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="text-bronze hover:text-white transition">View all &rarr;</a><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($activeCategory === null && $introBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $introBlockHtml ?>
    </div>
</section>
<?php elseif ($activeCategory === null && $pageIntro !== ''): ?>
<section class="section">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl text-center">
            <p class="text-body-md leading-8 text-charcoal/70"><?= nl2br(htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Filter + Grid -->
<section class="section-lg">
    <div class="container">
        <!-- Category filter pills -->
        <div class="reveal mb-10 flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="filter-pill <?= $activeCategory === null ? 'active' : '' ?>">
                All Work <span class="text-xs opacity-60">(<?= (int) ($pagination['total'] ?? 0) ?>)</span>
            </a>
            <?php foreach ($categories as $category): ?>
                <?php $isActive = $activeCategory !== null && (int) ($activeCategory['id'] ?? 0) === (int) ($category['id'] ?? 0); ?>
                <a href="<?= htmlspecialchars(base_url('portfolio/category/' . rawurlencode((string) ($category['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="filter-pill <?= $isActive ? 'active' : '' ?>">
                    <?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <span class="text-xs opacity-60">(<?= (int) ($category['gallery_count'] ?? 0) ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($items === []): ?>
            <?= render_component('components/empty-state', [
                'title' => 'No galleries matched this view.',
                'description' => 'Adjust the search terms or switch back to the full portfolio index.',
                'actionHref' => '/portfolio',
                'actionLabel' => 'Browse All Work',
            ]) ?>
        <?php else: ?>
            <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($items as $index => $item): ?>
                    <div class="reveal reveal-delay-<?= min(($index % 3) + 1, 4) ?>">
                        <?= render_component('components/gallery-card', ['item' => $item, 'variant' => 'archive']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ((int) ($pagination['total_pages'] ?? 1) > 1): ?>
                <nav class="pagination" aria-label="Portfolio pagination">
                    <?php $prevPage = max(1, (int) ($pagination['page'] ?? 1) - 1); ?>
                    <?php $nextPage = min((int) ($pagination['total_pages'] ?? 1), (int) ($pagination['page'] ?? 1) + 1); ?>
                    <a href="<?= htmlspecialchars(app_href($basePath . '?q=' . rawurlencode($query) . '&page=' . $prevPage), ENT_QUOTES, 'UTF-8') ?>" class="<?= (int) ($pagination['page'] ?? 1) <= 1 ? 'page-disabled' : '' ?>" aria-label="Previous page">&larr; Prev</a>
                    <span class="page-current"><?= (int) ($pagination['page'] ?? 1) ?></span>
                    <span class="text-sm text-charcoal/40">of <?= (int) ($pagination['total_pages'] ?? 1) ?></span>
                    <a href="<?= htmlspecialchars(app_href($basePath . '?q=' . rawurlencode($query) . '&page=' . $nextPage), ENT_QUOTES, 'UTF-8') ?>" class="<?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'page-disabled' : '' ?>" aria-label="Next page">Next &rarr;</a>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php if ($activeCategory === null && $afterGridBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $afterGridBlockHtml ?>
    </div>
</section>
<?php endif; ?>
