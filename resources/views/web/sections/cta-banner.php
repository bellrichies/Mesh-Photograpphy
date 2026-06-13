<?php $section = isset($section) && is_array($section) ? $section : []; ?>
<section class="reveal">
    <div class="rounded-3xl border border-bronze/20 px-8 py-12 md:px-12 md:py-14 text-charcoal relative overflow-hidden" style="background: linear-gradient(135deg, #f5ede3 0%, #ebe1d4 50%, #e8ddd0 100%);">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-48 h-48 rounded-full bg-bronze/8 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-gold/6 blur-2xl pointer-events-none" aria-hidden="true"></div>
        
        <div class="relative flex flex-wrap items-center justify-between gap-6">
            <div class="max-w-2xl">
                <h2 class="text-display-md"><?= htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ((string) ($section['body'] ?? '') !== ''): ?>
                    <p class="mt-4 text-body-md text-charcoal/70 max-w-xl"><?= htmlspecialchars((string) ($section['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
            <?php if ((string) ($section['cta_label'] ?? '') !== '' && (string) ($section['cta_url'] ?? '') !== ''): ?>
                <a href="<?= htmlspecialchars(app_href((string) ($section['cta_url'] ?? '#')), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">
                    <span><?= htmlspecialchars((string) ($section['cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
