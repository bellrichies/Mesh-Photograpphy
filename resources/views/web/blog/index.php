<?php
$posts = isset($posts) && is_array($posts) ? $posts : [];
$featuredPost = isset($featuredPost) && is_array($featuredPost) ? $featuredPost : null;
$recentPosts = isset($recentPosts) && is_array($recentPosts) ? $recentPosts : [];
$categories = isset($categories) && is_array($categories) ? $categories : [];
$tags = isset($tags) && is_array($tags) ? $tags : [];
$archives = isset($archives) && is_array($archives) ? $archives : [];
$activeCategory = isset($activeCategory) && is_array($activeCategory) ? $activeCategory : null;
$activeTag = isset($activeTag) && is_array($activeTag) ? $activeTag : null;
$activeArchive = isset($activeArchive) && is_array($activeArchive) ? $activeArchive : null;
$pagination = isset($pagination) && is_array($pagination) ? $pagination : ['page' => 1, 'total_pages' => 1, 'total' => 0];
$query = (string) ($query ?? '');
$page = isset($page) && is_array($page) ? $page : [];
$headline = (string) ($headline ?? 'Blog');
$eyebrow = (string) ($eyebrow ?? 'Journal');
$description = (string) ($description ?? '');
$listingBasePath = (string) ($listingBasePath ?? '/blog');
$publishedTotal = (int) ($publishedTotal ?? 0);
$isAllPostsActive = $activeCategory === null && $activeTag === null && $activeArchive === null && $query === '' && (($mode ?? 'index') === 'index');
$isPrimaryIndex = $activeCategory === null && $activeTag === null && $activeArchive === null && $query === '' && (($mode ?? 'index') === 'index');
$pageHeadline = (string) (($page['title'] ?? '') !== '' ? ($page['title'] ?? '') : $headline);
$pageDescription = (string) (($page['excerpt'] ?? '') !== '' ? ($page['excerpt'] ?? '') : $description);
$pageIntro = trim((string) ($page['body'] ?? ''));

$currentParams = [];
if ($query !== '') {
    $currentParams['q'] = $query;
}
if ($activeArchive !== null && (string) ($activeArchive['archive_month'] ?? '') !== '') {
    $currentParams['archive'] = (string) ($activeArchive['archive_month'] ?? '');
}

$buildUrl = static function (string $path, array $params = []) use ($currentParams): string {
    $final = array_merge($currentParams, $params);
    $final = array_filter($final, static fn ($value): bool => $value !== null && $value !== '');

    if ($final === []) {
        return $path;
    }

    return $path . '?' . http_build_query($final);
};
$heroBlock = published_reusable_block('blog-hero');
$topBlockHtml = render_reusable_block('blog-intro');
$bottomBlockHtml = render_reusable_block('blog-after-listing');
?>

<section class="page-hero" style="background: var(--color-charcoal);">
    <div class="page-hero-bg" style="background-image: url('<?= htmlspecialchars(asset_url('assets/images/blog-hero.jpg'), ENT_QUOTES, 'UTF-8') ?>'); opacity: 0.2;"></div>
    <div class="container">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Blog'],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <div class="mt-4 grid items-end gap-8 lg:grid-cols-2">
            <div class="reveal">
                <p class="page-hero-eyebrow"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></p>
                <h1 class="page-hero-title"><?= htmlspecialchars(($isPrimaryIndex && ($heroBlock['title'] ?? '') !== '') ? (string) ($heroBlock['title'] ?? '') : ($isPrimaryIndex ? $pageHeadline : $headline), ENT_QUOTES, 'UTF-8') ?></h1>
                <?php $heroDescription = ($isPrimaryIndex && ($heroBlock['body'] ?? '') !== '') ? (string) ($heroBlock['body'] ?? '') : ($isPrimaryIndex ? $pageDescription : $description); ?>
                <?php if ($heroDescription !== ''): ?><p class="page-hero-desc"><?= htmlspecialchars($heroDescription, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <div class="mt-5 flex flex-wrap gap-3 text-xs uppercase tracking-[0.16em] text-white/45">
                    <span><?= (int) ($pagination['total'] ?? 0) ?> published posts</span>
                    <?php if ($activeCategory !== null): ?><span>Category: <?= htmlspecialchars((string) ($activeCategory['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                    <?php if ($activeTag !== null): ?><span>Tag: #<?= htmlspecialchars((string) ($activeTag['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                    <?php if ($activeArchive !== null): ?><span>Archive: <?= htmlspecialchars((string) ($activeArchive['archive_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                </div>
            </div>
            <div class="reveal reveal-delay-2">
                <form method="get" action="<?= htmlspecialchars(app_href('/blog/search'), ENT_QUOTES, 'UTF-8') ?>" class="search-bar">
                    <input type="text" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by title, theme, category, or tag&hellip;">
                    <button type="submit" aria-label="Search">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </button>
                </form>
                <div class="mt-4 flex items-center justify-between text-sm text-white/50">
                    <span>Refined editorial layout</span>
                    <a href="<?= htmlspecialchars(app_href('/blog'), ENT_QUOTES, 'UTF-8') ?>" class="text-bronze hover:text-white transition">Reset &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($isPrimaryIndex && $topBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $topBlockHtml ?>
    </div>
</section>
<?php elseif ($isPrimaryIndex && $pageIntro !== ''): ?>
<section class="section">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl text-center">
            <p class="text-body-md leading-8 text-charcoal/70"><?= nl2br(htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($featuredPost !== null): ?>
    <section class="section-lg" style="padding-bottom: 0;">
        <div class="container">
            <article class="featured-post-card reveal">
                <div class="featured-post-media">
                    <?php if ((string) ($featuredPost['featured_image_url'] ?? '') !== ''): ?>
                        <div class="featured-post-image">
                            <img src="<?= htmlspecialchars((string) ($featuredPost['featured_image_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($featuredPost['featured_image_alt_text'] ?? '') !== '' ? ($featuredPost['featured_image_alt_text'] ?? '') : ($featuredPost['title'] ?? 'Featured blog image')), ENT_QUOTES, 'UTF-8') ?>" loading="eager" fetchpriority="high" decoding="async">
                        </div>
                    <?php else: ?>
                        <div class="featured-post-image featured-post-image-fallback"></div>
                    <?php endif; ?>
                    <div class="featured-post-media-badge">
                        <span class="featured-post-kicker">Featured Post</span>
                    </div>
                </div>
                <div class="featured-post-body">
                    <div class="featured-post-meta-row">
                        <?php if ((string) ($featuredPost['published_label'] ?? '') !== ''): ?><span class="featured-post-meta-item"><?= htmlspecialchars((string) ($featuredPost['published_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        <?php if ((string) ($featuredPost['reading_time_label'] ?? '') !== ''): ?><span class="featured-post-meta-item"><?= htmlspecialchars((string) ($featuredPost['reading_time_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        <?php if ((string) ($featuredPost['author_name'] ?? '') !== ''): ?><span class="featured-post-meta-item"><?= htmlspecialchars((string) ($featuredPost['author_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                    </div>

                    <h2 class="featured-post-title"><?= htmlspecialchars((string) ($featuredPost['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>

                    <?php if ((string) ($featuredPost['summary'] ?? '') !== ''): ?>
                        <p class="featured-post-summary"><?= htmlspecialchars((string) ($featuredPost['summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <?php if (is_array($featuredPost['categories'] ?? null) && $featuredPost['categories'] !== []): ?>
                        <div class="featured-post-tags">
                            <?php foreach ($featuredPost['categories'] as $category): ?>
                                <a href="<?= htmlspecialchars(base_url('blog/category/' . rawurlencode((string) ($category['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="featured-post-tag"><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="featured-post-footer">
                        <div class="featured-post-footer-copy">
                            <span class="featured-post-footer-label">Editorial Highlight</span>
                            <span class="featured-post-footer-note">Designed for fast scanning, strong hierarchy, and an intentional first click.</span>
                        </div>
                        <a href="<?= htmlspecialchars(base_url('blog/' . rawurlencode((string) ($featuredPost['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="featured-post-cta">
                            <span>Read Article</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>
<?php endif; ?>

<section class="section-lg">
    <div class="container grid gap-12 lg:grid-cols-[1fr,280px]">
        <div>
            <?php if ($posts === []): ?>
                <?= render_component('components/empty-state', [
                    'title' => 'No published posts matched this view.',
                    'description' => 'Try a different keyword, remove the archive filter, or return to the full journal index.',
                    'actionHref' => '/blog',
                    'actionLabel' => 'View All Posts',
                ]) ?>
            <?php else: ?>
                <div class="grid gap-7 sm:grid-cols-2">
                    <?php foreach ($posts as $index => $post): ?>
                        <div class="reveal reveal-delay-<?= min(($index % 2) + 1, 4) ?>">
                            <?= render_component('components/blog-card', ['item' => $post]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ((int) ($pagination['total_pages'] ?? 1) > 1): ?>
                    <nav class="pagination" aria-label="Blog pagination">
                        <?php $prevPage = max(1, (int) ($pagination['page'] ?? 1) - 1); ?>
                        <?php $nextPage = min((int) ($pagination['total_pages'] ?? 1), (int) ($pagination['page'] ?? 1) + 1); ?>
                        <a href="<?= htmlspecialchars($buildUrl($listingBasePath, ['page' => $prevPage]), ENT_QUOTES, 'UTF-8') ?>" class="<?= (int) ($pagination['page'] ?? 1) <= 1 ? 'page-disabled' : '' ?>">&larr; Prev</a>
                        <span class="page-current"><?= (int) ($pagination['page'] ?? 1) ?></span>
                        <span class="text-sm text-charcoal/40">of <?= (int) ($pagination['total_pages'] ?? 1) ?></span>
                        <a href="<?= htmlspecialchars($buildUrl($listingBasePath, ['page' => $nextPage]), ENT_QUOTES, 'UTF-8') ?>" class="<?= (int) ($pagination['page'] ?? 1) >= (int) ($pagination['total_pages'] ?? 1) ? 'page-disabled' : '' ?>">Next &rarr;</a>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
            <!-- Recent Posts -->
            <div class="sidebar-widget">
                <h3 class="sidebar-widget-title">Recent Posts</h3>
                <div class="space-y-1">
                    <?php foreach ($recentPosts as $post): ?>
                        <a href="<?= htmlspecialchars(base_url('blog/' . rawurlencode((string) ($post['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link flex-col items-start gap-1">
                            <span class="font-display text-base text-charcoal"><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ((string) ($post['published_label'] ?? '') !== ''): ?><span class="text-xs text-charcoal/40"><?= htmlspecialchars((string) ($post['published_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Categories -->
            <div class="sidebar-widget">
                <h3 class="sidebar-widget-title">Categories</h3>
                <div class="space-y-0.5">
                    <a href="<?= htmlspecialchars(app_href('/blog'), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link <?= $isAllPostsActive ? 'active' : '' ?>">
                        <span>All Posts</span>
                        <span class="count"><?= $publishedTotal ?></span>
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <?php if ((int) ($category['post_count'] ?? 0) <= 0) { continue; } ?>
                        <?php $isActive = $activeCategory !== null && (int) ($activeCategory['id'] ?? 0) === (int) ($category['id'] ?? 0); ?>
                        <a href="<?= htmlspecialchars(base_url('blog/category/' . rawurlencode((string) ($category['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?>">
                            <span><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="count"><?= (int) ($category['post_count'] ?? 0) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tags -->
            <?php if ($tags !== []): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (array_slice($tags, 0, 14) as $tag): ?>
                            <?php $isActiveTag = $activeTag !== null && (int) ($activeTag['id'] ?? 0) === (int) ($tag['id'] ?? 0); ?>
                            <a href="<?= htmlspecialchars(base_url('blog/tag/' . rawurlencode((string) ($tag['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="tag-pill <?= $isActiveTag ? 'active' : '' ?>">#<?= htmlspecialchars((string) ($tag['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Archives -->
            <?php if ($archives !== []): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">Archives</h3>
                    <div class="space-y-0.5">
                        <?php foreach ($archives as $archive): ?>
                            <?php $isActiveArchive = $activeArchive !== null && (string) ($activeArchive['archive_month'] ?? '') === (string) ($archive['archive_month'] ?? ''); ?>
                            <a href="<?= htmlspecialchars(app_href('/blog?' . http_build_query(['archive' => (string) ($archive['archive_month'] ?? '')])), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link <?= $isActiveArchive ? 'active' : '' ?>">
                                <span><?= htmlspecialchars((string) ($archive['archive_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="count"><?= (int) ($archive['post_count'] ?? 0) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php if ($isPrimaryIndex && $bottomBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $bottomBlockHtml ?>
    </div>
</section>
<?php endif; ?>
