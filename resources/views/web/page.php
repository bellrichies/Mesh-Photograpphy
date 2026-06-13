<?php
$page = isset($page) && is_array($page) ? $page : [];
$sections = isset($sections) && is_array($sections) ? $sections : [];
$sectionService = $sectionService ?? null;
$view = new \App\Core\View(dirname(__DIR__, 3));
$heroBlock = published_reusable_block('page-' . (string) ($page['slug'] ?? '') . '-hero');
$pageBody = trim((string) ($page['body'] ?? ''));
?>

<!-- Hero -->
<section class="page-hero" style="min-height:28vh">
    <div class="container relative z-10 flex flex-col items-start justify-end">
        <?php
        $breadcrumbs = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => (string) ($page['title'] ?? 'Page')],
        ];
        $variant = 'dark';
        include dirname(__DIR__) . '/partials/breadcrumbs.php';
        ?>
        <p class="page-hero-eyebrow mt-4">Page</p>
        <h1 class="page-hero-title"><?= htmlspecialchars((string) (($heroBlock['title'] ?? '') !== '' ? ($heroBlock['title'] ?? '') : ($page['title'] ?? config('app.name', 'Mesh Photography'))), ENT_QUOTES, 'UTF-8') ?></h1>
        <?php $pageHeroDescription = (string) (($heroBlock['body'] ?? '') !== '' ? ($heroBlock['body'] ?? '') : ($page['excerpt'] ?? '')); ?>
        <?php if ($pageHeroDescription !== ''): ?>
            <p class="page-hero-desc"><?= htmlspecialchars($pageHeroDescription, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Sections -->
<?php if ($sections === []): ?>
    <section class="section-lg">
        <div class="container">
            <?php if ($pageBody !== ''): ?>
                <div class="reveal mx-auto max-w-3xl text-center">
                    <div class="whitespace-pre-line text-body-md leading-8 text-charcoal/72"><?= htmlspecialchars($pageBody, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php else: ?>
                <?= render_component('components/empty-state', [
                    'title' => 'No published sections are configured for this page yet.',
                    'description' => 'Use the CMS page section editor to build out this page with structured content.',
                ]) ?>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <?php foreach ($sections as $section): ?>
        <?php
        $payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];
        $beforeBlock = isset($payload['before_block_key']) && is_string($payload['before_block_key']) ? trim($payload['before_block_key']) : '';
        $afterBlock = isset($payload['after_block_key']) && is_string($payload['after_block_key']) ? trim($payload['after_block_key']) : '';
        $partial = $sectionService instanceof \App\Services\PageSectionService
            ? $sectionService->renderPartialForType((string) ($section['section_type'] ?? ''))
            : 'web/sections/generic';
        ?>
        <?php if ($beforeBlock !== ''): ?>
            <?php $beforeBlockHtml = render_reusable_block($beforeBlock); ?>
            <?php if ($beforeBlockHtml !== ''): ?><section class="section"><div class="container"><?= $beforeBlockHtml ?></div></section><?php endif; ?>
        <?php endif; ?>

        <?= $view->partial($partial, ['section' => $section, 'page' => $page]) ?>

        <?php if ($afterBlock !== ''): ?>
            <?php $afterBlockHtml = render_reusable_block($afterBlock); ?>
            <?php if ($afterBlockHtml !== ''): ?><section class="section"><div class="container"><?= $afterBlockHtml ?></div></section><?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php $ctaBlockHtml = render_reusable_block('global-cta'); ?>
<?php if ($ctaBlockHtml !== ''): ?><div class="mt-10"><?= $ctaBlockHtml ?></div><?php endif; ?>
