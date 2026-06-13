<?php
$post = isset($post) && is_array($post) ? $post : [];
$supportingMedia = isset($supportingMedia) && is_array($supportingMedia) ? $supportingMedia : [];
$relatedPosts = isset($relatedPosts) && is_array($relatedPosts) ? $relatedPosts : [];
$recentPosts = isset($recentPosts) && is_array($recentPosts) ? $recentPosts : [];
$seo = isset($seo) && is_array($seo) ? $seo : [];
$categories = isset($post['categories']) && is_array($post['categories']) ? $post['categories'] : [];
$tags = isset($post['tags']) && is_array($post['tags']) ? $post['tags'] : [];
$featuredImageUrl = (string) ($post['featured_image_url'] ?? '');
$ctaBlockHtml = render_reusable_block('global-cta');
?>

<!-- Hero -->
<section class="page-hero" style="min-height:38vh">
    <?php if ($featuredImageUrl !== ''): ?>
        <img src="<?= htmlspecialchars($featuredImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($post['featured_image_alt_text'] ?? '') !== '' ? ($post['featured_image_alt_text'] ?? '') : ($post['title'] ?? 'Blog hero image')), ENT_QUOTES, 'UTF-8') ?>" class="page-hero-bg" loading="eager">
    <?php endif; ?>
    <div class="container relative z-10 flex flex-col items-start justify-end">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Journal', 'href' => '/blog'],
            ['label' => (string) ($post['title'] ?? 'Article')],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>

        <?php if ($categories !== []): ?>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ($categories as $category): ?>
                    <a href="<?= htmlspecialchars(base_url('blog/category/' . rawurlencode((string) ($category['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="tag-pill border-white/20 bg-white/10 text-ivory backdrop-blur-sm hover:bg-white/20"><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h1 class="page-hero-title mt-3"><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs uppercase tracking-widest text-ivory/70">
            <?php if ((string) ($post['published_label'] ?? '') !== ''): ?>
                <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><?= htmlspecialchars((string) ($post['published_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if ((string) ($post['reading_time_label'] ?? '') !== ''): ?>
                <span><?= htmlspecialchars((string) ($post['reading_time_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if ((string) ($post['author_name'] ?? '') !== ''): ?>
                <span><?= htmlspecialchars((string) ($post['author_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span><?= (int) ($post['view_count'] ?? 0) ?> views</span>
        </div>

        <?php if ((string) ($post['summary'] ?? '') !== ''): ?>
            <p class="page-hero-desc"><?= htmlspecialchars((string) ($post['summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Article Body + Sidebar -->
<article class="section-lg">
    <div class="container grid gap-12 lg:grid-cols-[1fr,280px]">

        <!-- Main Content -->
        <div class="space-y-10">
            <div class="prose-content">
                <?= nl2br(htmlspecialchars((string) ($post['body_long'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
            </div>

            <?php if ($tags !== []): ?>
                <div class="border-t border-charcoal/10 pt-6">
                    <p class="section-label">Filed Under</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?= htmlspecialchars(base_url('blog/tag/' . rawurlencode((string) ($tag['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="tag-pill">#<?= htmlspecialchars((string) ($tag['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($supportingMedia !== []): ?>
                <div class="grid gap-5 sm:grid-cols-2">
                    <?php foreach ($supportingMedia as $index => $media): ?>
                        <?php if ((string) ($media['url'] ?? '') === '') { continue; } ?>
                        <figure class="card reveal reveal-delay-<?= min(($index % 2) + 1, 4) ?>">
                            <div class="card-image" style="aspect-ratio:4/3">
                                <img src="<?= htmlspecialchars((string) ($media['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) (($media['alt_text'] ?? '') !== '' ? ($media['alt_text'] ?? '') : ($media['title'] ?? 'Supporting blog image')), ENT_QUOTES, 'UTF-8') ?>" class="h-full w-full object-cover" loading="lazy">
                            </div>
                            <?php if ((string) ($media['caption'] ?? '') !== ''): ?>
                                <div class="card-content"><p class="text-sm text-charcoal/65"><?= htmlspecialchars((string) ($media['caption'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></div>
                            <?php endif; ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- CTA -->
            <?php if ($ctaBlockHtml !== ''): ?>
                <div><?= $ctaBlockHtml ?></div>
            <?php else: ?>
                <section class="rounded-2xl bg-charcoal px-8 py-10 text-ivory">
                    <p class="section-label text-bronze/90">Next Step</p>
                    <h2 class="mt-3 text-display-md text-ivory">Bring the story into your own inquiry.</h2>
                    <p class="mt-3 max-w-2xl text-body-md text-ivory/75">If the tone, pacing, and visual direction feel aligned, the next conversation can start with availability, scope, and intent.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary bg-ivory text-charcoal hover:bg-white">Start an Inquiry</a>
                        <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary border-white/15 text-ivory hover:border-white/30">View Portfolio</a>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
            <?php if ($recentPosts !== []): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">Recent Posts</h3>
                    <div class="space-y-1">
                        <?php foreach ($recentPosts as $item): ?>
                            <a href="<?= htmlspecialchars(base_url('blog/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link flex-col items-start gap-1">
                                <span class="font-display text-base text-charcoal"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ((string) ($item['published_label'] ?? '') !== ''): ?><span class="text-xs text-charcoal/40"><?= htmlspecialchars((string) ($item['published_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($relatedPosts !== []): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">Related Reading</h3>
                    <div class="space-y-1">
                        <?php foreach ($relatedPosts as $item): ?>
                            <a href="<?= htmlspecialchars(base_url('blog/' . rawurlencode((string) ($item['slug'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>" class="sidebar-link flex-col items-start gap-1">
                                <span class="font-display text-base text-charcoal"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ((string) ($item['reading_time_label'] ?? '') !== ''): ?><span class="text-xs text-charcoal/40"><?= htmlspecialchars((string) ($item['reading_time_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>

    </div>
</article>
