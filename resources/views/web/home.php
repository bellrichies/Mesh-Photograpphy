<?php
$page = isset($page) && is_array($page) ? $page : [];
$sections = isset($sections) && is_array($sections) ? $sections : [];
$sectionService = $sectionService ?? null;
$homeContent = isset($homeContent) && is_array($homeContent) ? $homeContent : [];
$view = new \App\Core\View(dirname(__DIR__, 3));
?>

<?php $announcementHtml = render_reusable_block('homepage-announcement'); ?>
<?php if ($announcementHtml !== ''): ?>
    <section class="relative z-10 pt-6">
        <div class="container">
            <?= $announcementHtml ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($sections === []): ?>
    <!-- Fallback when no sections configured -->
    <div class="container pb-16 pt-10 lg:px-8">
        <section class="overflow-hidden rounded-[2rem] border border-charcoal/10 bg-[linear-gradient(160deg,#f6efe8_0%,#fffdfa_48%,#f1e7dc_100%)] px-8 py-14 shadow-[0_20px_60px_-28px_rgba(20,20,20,0.22)] sm:px-12 lg:px-16">
            <p class="text-xs uppercase tracking-[0.22em] text-bronze">Homepage</p>
            <h1 class="mt-5 max-w-4xl font-display text-5xl leading-[1.02] text-charcoal sm:text-6xl"><?= htmlspecialchars((string) ($page['title'] ?? ($homeContent['siteName'] ?? config('app.name', 'Mesh Photography'))), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="mt-6 max-w-2xl text-base leading-8 text-charcoal/75"><?= htmlspecialchars((string) ($homeContent['tagline'] ?? ($page['excerpt'] ?? 'Publish homepage sections in the CMS to build the public landing experience.')), ENT_QUOTES, 'UTF-8') ?></p>
            
            <!-- Default CTA buttons -->
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars(app_href('/portfolio'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">View Portfolio</a>
                <a href="<?= htmlspecialchars(app_href('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">Get in Touch</a>
            </div>
        </section>
    </div>
<?php else: ?>
    <!-- Render Sections -->
    <?php 
    $hasHero = false;
    $firstSection = isset($sections[0]) ? $sections[0] : [];
    if (isset($firstSection['section_type']) && $firstSection['section_type'] === 'hero') {
        $hasHero = true;
    }
    ?>
    
    <?php if ($hasHero): ?>
        <!-- Full-width sections container -->
        <?php foreach ($sections as $section): ?>
            <?php
            $payload = isset($section['payload']) && is_array($section['payload']) ? $section['payload'] : [];
            $beforeBlock = isset($payload['before_block_key']) && is_string($payload['before_block_key']) ? trim($payload['before_block_key']) : '';
            $afterBlock = isset($payload['after_block_key']) && is_string($payload['after_block_key']) ? trim($payload['after_block_key']) : '';
            $partial = $sectionService instanceof \App\Services\PageSectionService
                ? $sectionService->renderPartialForType((string) ($section['section_type'] ?? ''))
                : 'web/sections/generic';
            ?>
            
            <!-- Before block -->
            <?php if ($beforeBlock !== ''): ?>
                <?php $beforeBlockHtml = render_reusable_block($beforeBlock); ?>
                <?php if ($beforeBlockHtml !== ''): ?>
                    <section class="section">
                        <div class="container">
                            <?= $beforeBlockHtml ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
            
            <?= $view->partial($partial, ['section' => $section, 'page' => $page, 'homeContent' => $homeContent]) ?>
            
            <!-- After block -->
            <?php if ($afterBlock !== ''): ?>
                <?php $afterBlockHtml = render_reusable_block($afterBlock); ?>
                <?php if ($afterBlockHtml !== ''): ?>
                    <section class="section">
                        <div class="container">
                            <?= $afterBlockHtml ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Contained sections with max-width -->
        <div class="container pb-16 pt-10 lg:px-8">
            <div class="space-y-8 sm:space-y-10">
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
                        <?php if ($beforeBlockHtml !== ''): ?><section class="section"><div><?= $beforeBlockHtml ?></div></section><?php endif; ?>
                    <?php endif; ?>
                    
                    <?= $view->partial($partial, ['section' => $section, 'page' => $page, 'homeContent' => $homeContent]) ?>
                    
                    <?php if ($afterBlock !== ''): ?>
                        <?php $afterBlockHtml = render_reusable_block($afterBlock); ?>
                        <?php if ($afterBlockHtml !== ''): ?><section class="section"><div><?= $afterBlockHtml ?></div></section><?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

