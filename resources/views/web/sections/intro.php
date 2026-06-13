<?php $section = isset($section) && is_array($section) ? $section : []; ?>
<section class="reveal">
    <div class="mx-auto max-w-4xl rounded-3xl border border-charcoal/8 bg-white px-8 py-14 md:px-12 md:py-18 text-center relative overflow-hidden" style="box-shadow: 0 20px 60px -24px rgba(20,20,20,0.12);">
        <!-- Decorative gradient -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-1 rounded-full" style="background: linear-gradient(90deg, transparent, var(--color-bronze), transparent);" aria-hidden="true"></div>
        
        <h2 class="text-display-md text-charcoal"><?= htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if ((string) ($section['subtitle'] ?? '') !== ''): ?>
            <p class="mt-4 section-label"><?= htmlspecialchars((string) ($section['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ((string) ($section['body'] ?? '') !== ''): ?>
            <div class="mt-6 whitespace-pre-line text-body-md text-charcoal/70 max-w-2xl mx-auto"><?= htmlspecialchars((string) ($section['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>
</section>