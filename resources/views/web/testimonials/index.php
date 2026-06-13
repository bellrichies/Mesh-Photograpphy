<?php
$items = isset($items) && is_array($items) ? $items : [];
$page = isset($page) && is_array($page) ? $page : [];
$heroBlock = published_reusable_block('testimonials-hero');
$bottomBlockHtml = render_reusable_block('testimonials-after-list');
$heroTitle = (string) (($heroBlock['title'] ?? '') !== '' ? ($heroBlock['title'] ?? '') : (($page['title'] ?? '') !== '' ? ($page['title'] ?? '') : 'Words that anchor the brand in lived experience.'));
$heroDescription = (string) (($heroBlock['body'] ?? '') !== '' ? ($heroBlock['body'] ?? '') : (($page['excerpt'] ?? '') !== '' ? ($page['excerpt'] ?? '') : 'Every quote below is managed from the CMS, with optional editorial story depth and links back to the relevant service or gallery.'));
$pageIntro = trim((string) ($page['body'] ?? ''));
?>

<!-- Hero -->
<section class="page-hero" style="min-height:30vh">
    <div class="container relative z-10 flex flex-col items-start justify-end">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Testimonials'],
        ];
        $variant = 'dark';
        include dirname(__DIR__, 2) . '/partials/breadcrumbs.php';
        ?>
        <p class="page-hero-eyebrow mt-4">Client Stories</p>
        <h1 class="page-hero-title"><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="page-hero-desc"><?= htmlspecialchars($heroDescription, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>

<?php if ($pageIntro !== ''): ?>
<section class="section">
    <div class="container">
        <div class="reveal mx-auto max-w-3xl text-center">
            <p class="text-body-md leading-8 text-charcoal/70"><?= nl2br(htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials Grid -->
<section class="section-lg">
    <div class="container">
        <?php if ($items === []): ?>
            <?= render_component('components/empty-state', [
                'title' => 'No published testimonials are available yet.',
                'description' => 'Featured client stories will appear here once they are published from the CMS.',
            ]) ?>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($items as $index => $item): ?>
                    <div class="reveal reveal-delay-<?= min(($index % 3) + 1, 4) ?>">
                        <?= render_component('components/testimonial-card', ['item' => $item, 'variant' => 'detailed']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($bottomBlockHtml !== ''): ?>
<section class="section">
    <div class="container">
        <?= $bottomBlockHtml ?>
    </div>
</section>
<?php endif; ?>
